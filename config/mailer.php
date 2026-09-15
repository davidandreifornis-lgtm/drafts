<?php
/**
 * Mail helper: log / PHP mail / SMTP + low-stock alerts.
 */

function mail_config(): array {
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/mail.php';
    }
    return $cfg;
}

function mail_log(string $line): void {
    $file = __DIR__ . '/../storage/mail.log';
    $dir = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($file, '[' . date('Y-m-d H:i:s') . '] ' . $line . "\n", FILE_APPEND);
}

/**
 * Minimal SMTP client (LOGIN + STARTTLS/SSL).
 * @return array{ok:bool,error?:string}
 */
function smtp_send(string $to, string $subject, string $bodyText, string $bodyHtml = ''): array {
    $cfg = mail_config();
    $host = $cfg['smtp_host'];
    $port = (int)$cfg['smtp_port'];
    $user = $cfg['smtp_user'];
    $pass = $cfg['smtp_pass'];
    $enc  = strtolower($cfg['smtp_encryption'] ?? 'tls');
    $from = $cfg['from_email'];
    $fromName = $cfg['from_name'];

    if ($pass === '' || $pass === 'YOUR_GMAIL_APP_PASSWORD_HERE') {
        return ['ok' => false, 'error' => 'SMTP password not set. Put a Gmail App Password in config/mail.php (smtp_pass).'];
    }

    // Local XAMPP often lacks CA certs → certificate verify failed.
    // Context disables peer verify for SMTP only (dev/local). Prefer real CA in production.
    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
            'crypto_method'     => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
        ],
    ]);

    $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client(
        $remote,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );
    if (!$fp) {
        return ['ok' => false, 'error' => "Cannot connect to {$remote}: {$errstr} ({$errno})"];
    }
    stream_set_timeout($fp, 30);

    $read = function () use ($fp) {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $write = function (string $cmd) use ($fp) {
        fwrite($fp, $cmd . "\r\n");
    };
    $expect = function (string $prefix, string $ctx) use ($read) {
        $resp = $read();
        if (strpos($resp, $prefix) !== 0) {
            throw new RuntimeException("SMTP {$ctx} failed: " . trim($resp));
        }
        return $resp;
    };

    try {
        $expect('220', 'banner');
        $write('EHLO localhost');
        $expect('250', 'EHLO');

        if ($enc === 'tls') {
            $write('STARTTLS');
            $expect('220', 'STARTTLS');
            $cryptoOk = @stream_socket_enable_crypto(
                $fp,
                true,
                STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
            );
            if (!$cryptoOk) {
                // Fallback older constant
                $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }
            if (!$cryptoOk) {
                throw new RuntimeException('STARTTLS crypto failed (OpenSSL). Check App Password and network.');
            }
            $write('EHLO localhost');
            $expect('250', 'EHLO after TLS');
        }

        $write('AUTH LOGIN');
        $expect('334', 'AUTH');
        $write(base64_encode($user));
        $expect('334', 'USER');
        $write(base64_encode($pass));
        $expect('235', 'PASS');

        $write('MAIL FROM:<' . $from . '>');
        $expect('250', 'MAIL FROM');
        $write('RCPT TO:<' . $to . '>');
        $expect('250', 'RCPT TO');
        $write('DATA');
        $expect('354', 'DATA');

        $headers = [];
        $headers[] = 'From: ' . sprintf('%s <%s>', $fromName, $from);
        $headers[] = 'To: <' . $to . '>';
        $headers[] = 'Subject: ' . $subject;
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Date: ' . date('r');

        if ($bodyHtml !== '') {
            $boundary = 'bnd_' . md5(uniqid('', true));
            $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
            $message = implode("\r\n", $headers) . "\r\n\r\n";
            $message .= "--{$boundary}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$bodyText}\r\n\r\n";
            $message .= "--{$boundary}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$bodyHtml}\r\n\r\n";
            $message .= "--{$boundary}--\r\n";
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $bodyText . "\r\n";
        }

        // Dot-stuff lines starting with .
        $message = preg_replace('/^\./m', '..', $message);
        $write($message . "\r\n.");
        $expect('250', 'message body');
        $write('QUIT');
        fclose($fp);
        return ['ok' => true];
    } catch (Throwable $e) {
        fclose($fp);
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * @return array{ok:bool,error?:string,driver?:string}
 */
function send_system_mail(string $to, string $subject, string $bodyText, string $bodyHtml = ''): array {
    $cfg = mail_config();
    $driver = $cfg['driver'] ?? 'smtp';

    mail_log("driver={$driver} to={$to} subject={$subject}\n{$bodyText}\n---");

    if ($driver === 'log') {
        return ['ok' => true, 'driver' => 'log'];
    }

    if ($driver === 'smtp') {
        $result = smtp_send($to, $subject, $bodyText, $bodyHtml);
        $result['driver'] = 'smtp';
        if (!$result['ok']) {
            mail_log('SMTP ERROR: ' . ($result['error'] ?? 'unknown'));
        }
        return $result;
    }

    // PHP mail()
    $fromEmail = $cfg['from_email'];
    $fromName  = $cfg['from_name'];
    $headers = [
        'From: "' . addslashes($fromName) . '" <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $ok = @mail($to, $subject, $bodyText, implode("\r\n", $headers));
    if (!$ok) {
        mail_log('mail() returned false — XAMPP has no mail server. Use driver=smtp with App Password.');
        return ['ok' => false, 'driver' => 'mail', 'error' => 'PHP mail() failed. Use SMTP (Gmail App Password) in config/mail.php'];
    }
    return ['ok' => true, 'driver' => 'mail'];
}

function load_alert_cooldown(): array {
    $cfg = mail_config();
    $file = $cfg['cooldown_file'];
    if (!is_file($file)) return [];
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function save_alert_cooldown(array $data): void {
    $cfg = mail_config();
    $file = $cfg['cooldown_file'];
    $dir = dirname($file);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}


/**
 * Active admin emails from dbo.toner_users (+ fallback config admin_email).
 * @return string[]
 */
function get_admin_notification_emails(?PDO $pdo = null): array {
    $cfg = mail_config();
    $emails = [];

    try {
        if (!$pdo) {
            if (function_exists('db')) {
                $pdo = db();
            } elseif (function_exists('db_only')) {
                $pdo = db_only();
            }
        }
        if ($pdo) {
            // Username IS the notification email. No separate email column required.
            try {
                $stmt = $pdo->query(
                    "SELECT username FROM dbo.toner_users WHERE is_active = 1 OR is_active = '1'"
                );
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $row = array_change_key_case($row, CASE_LOWER);
                    $em = strtolower(trim((string)($row['username'] ?? '')));
                    if ($em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL)) {
                        $emails[$em] = true;
                    }
                }
            } catch (Throwable $e) {
                // Retry without is_active filter if column type differs
                try {
                    $stmt = $pdo->query("SELECT username FROM dbo.toner_users");
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $row = array_change_key_case($row, CASE_LOWER);
                        $em = strtolower(trim((string)($row['username'] ?? '')));
                        if ($em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL)) {
                            $emails[$em] = true;
                        }
                    }
                } catch (Throwable $e2) {
                    mail_log('get_admin_notification_emails: ' . $e2->getMessage());
                }
            }
        }
    } catch (Throwable $e) {
        mail_log('get_admin_notification_emails db: ' . $e->getMessage());
    }

    $fallback = strtolower(trim((string)($cfg['admin_email'] ?? '')));
    if ($fallback !== '' && filter_var($fallback, FILTER_VALIDATE_EMAIL)) {
        $emails[$fallback] = true;
    }

    return array_keys($emails);
}

function notify_low_stock(?PDO $pdo = null): array {
    if (!$pdo) {
        $pdo = db();
    }
    $cfg = mail_config();
    $recipients = get_admin_notification_emails($pdo);
    if (count($recipients) === 0) {
        return [
            'sent' => false,
            'reason' => 'no_recipients',
            'error' => 'No admin emails found. User Management username must be a valid email, or set admin_email in config/mail.php.',
            'low_count' => 0,
            'out_count' => 0,
        ];
    }

    $cooldownHours = (int)($cfg['cooldown_hours'] ?? 12);
    $cooldown = load_alert_cooldown();
    $now = time();

    // Schema uses item_code (not ink_code)
    try {
        $stmt = $pdo->query(
            'SELECT item_code, description, printer_model, quantity, reorder_level, supplier
             FROM dbo.toner_inventory
             ORDER BY quantity ASC, item_code ASC'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        // Legacy fallback if someone still has ink_code
        try {
            $stmt = $pdo->query(
                'SELECT ink_code AS item_code, description, printer_model, quantity, reorder_level, supplier
                 FROM dbo.toner_inventory
                 ORDER BY quantity ASC'
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e2) {
            return [
                'sent' => false,
                'error' => 'Cannot read inventory for low-stock check: ' . $e2->getMessage(),
                'low_count' => 0,
                'out_count' => 0,
            ];
        }
    }

    $low = [];
    $out = [];
    $toAlert = [];

    foreach ($rows as $r) {
        $r = array_change_key_case($r, CASE_LOWER);
        $code = strtoupper(trim((string)($r['item_code'] ?? $r['ink_code'] ?? '')));
        if ($code === '') continue;
        $qty = (int)($r['quantity'] ?? 0);
        $reorder = (int)($r['reorder_level'] ?? 3);
        if ($reorder < 0) $reorder = 3;

        $r['item_code'] = $code;
        $r['quantity'] = $qty;
        $r['reorder_level'] = $reorder;
        $r['description'] = (string)($r['description'] ?? '');
        $r['supplier'] = (string)($r['supplier'] ?? '');

        if ($qty <= 0) {
            $out[] = $r;
            $status = 'OUT';
        } elseif ($qty <= $reorder) {
            $low[] = $r;
            $status = 'LOW';
        } else {
            continue;
        }

        $last = (int)($cooldown[$code] ?? 0);
        if ($last > 0 && ($now - $last) < ($cooldownHours * 3600)) {
            continue; // still in cooldown
        }
        $toAlert[] = ['row' => $r, 'status' => $status];
    }

    if (count($toAlert) === 0) {
        return [
            'sent' => false,
            'reason' => 'none_to_alert',
            'to' => [],
            'recipients' => $recipients,
            'alerted' => 0,
            'low_count' => count($low),
            'out_count' => count($out),
            'message' => (count($low) + count($out)) === 0
                ? 'No low or out-of-stock items.'
                : 'Low items exist but all are within email cooldown (' . $cooldownHours . 'h). Use check_low_stock.php?force=1 to retest.',
            'driver' => $cfg['driver'] ?? 'smtp',
            'error' => null,
        ];
    }

    $lines = [];
    $lines[] = 'Toner Inventory — Low Stock Alert';
    $lines[] = date('Y-m-d H:i:s');
    $lines[] = str_repeat('-', 48);
    $rowsHtml = '';
    foreach ($toAlert as $item) {
        $r = $item['row'];
        $status = $item['status'];
        $label = $status === 'OUT' ? 'OUT OF STOCK' : 'LOW STOCK';
        $name = $r['description'] !== '' ? $r['description'] : $r['item_code'];
        $lines[] = sprintf(
            "%s | %s (%s) | qty=%d | reorder=%d | supplier=%s",
            $label,
            $name,
            $r['item_code'],
            (int)$r['quantity'],
            (int)$r['reorder_level'],
            $r['supplier'] !== '' ? $r['supplier'] : '—'
        );
        $rowsHtml .= '<tr>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($name)
            . '<br><span style="color:#64748b;font-size:12px;">' . htmlspecialchars($r['item_code']) . '</span></td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;font-weight:bold;color:'
            . ($status === 'OUT' ? '#be123c' : '#b45309') . ';">' . htmlspecialchars($label) . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . (int)$r['quantity'] . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . (int)$r['reorder_level'] . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($r['supplier'] !== '' ? $r['supplier'] : '—') . '</td>'
            . '</tr>';
    }
    $lines[] = str_repeat('-', 48);
    $lines[] = 'Please reorder the items above.';
    $bodyText = implode("\n", $lines);
    $bodyHtml = '<div style="font-family:Segoe UI,Arial,sans-serif;color:#0f172a">'
        . '<h2>Toner Inventory — Low Stock Alert</h2>'
        . '<p style="color:#64748b">' . date('Y-m-d H:i:s') . '</p>'
        . '<table style="border-collapse:collapse;width:100%;max-width:640px;font-size:14px">'
        . '<thead><tr style="background:#f8fafc">'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:left">Item</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:left">Status</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:right">On hand</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:right">Reorder</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:left">Supplier</th>'
        . '</tr></thead><tbody>' . $rowsHtml . '</tbody></table>'
        . '<p style="margin-top:16px;">Please reorder the items above.</p></div>';

    $subject = ($cfg['subject_prefix'] ?? '[Toner Alert]') . ' ' . count($toAlert) . ' item(s) need attention';
    $sentTo = [];
    $errors = [];
    $lastSend = ['ok' => false, 'driver' => $cfg['driver'] ?? 'smtp'];

    foreach ($recipients as $toEmail) {
        $send = send_system_mail($toEmail, $subject, $bodyText, $bodyHtml);
        $lastSend = $send;
        if (!empty($send['ok'])) {
            $sentTo[] = $toEmail;
            mail_log("Low-stock alert sent to {$toEmail}");
        } else {
            $err = $send['error'] ?? 'failed';
            $errors[] = $toEmail . ': ' . $err;
            mail_log("Low-stock alert FAILED to {$toEmail}: {$err}");
        }
    }

    $anyOk = count($sentTo) > 0;
    if ($anyOk) {
        foreach ($toAlert as $item) {
            $cooldown[$item['row']['item_code']] = $now;
        }
        save_alert_cooldown($cooldown);
    }

    return [
        'sent' => $anyOk,
        'to' => $sentTo,
        'recipients' => $recipients,
        'alerted' => count($toAlert),
        'low_count' => count($low),
        'out_count' => count($out),
        'driver' => $lastSend['driver'] ?? $cfg['driver'],
        'error' => $anyOk ? null : implode('; ', $errors),
    ];
}