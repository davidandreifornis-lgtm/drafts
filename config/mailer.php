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

function notify_low_stock(?PDO $pdo = null): array {
    if (!$pdo) $pdo = db();
    $cfg = mail_config();
    $admin = $cfg['admin_email'];
    $cooldownHours = (int)($cfg['cooldown_hours'] ?? 12);
    $cooldown = load_alert_cooldown();
    $now = time();

    $stmt = $pdo->query(
        'SELECT ink_code, printer_model, quantity, reorder_level, supplier
         FROM inventory ORDER BY quantity ASC, ink_code ASC'
    );
    $rows = $stmt->fetchAll();

    $low = [];
    $out = [];
    $toAlert = [];

    foreach ($rows as $r) {
        $qty = (int)$r['quantity'];
        $reorder = (int)$r['reorder_level'];
        $code = $r['ink_code'];
        if ($qty <= 0) {
            $out[] = $r;
            $status = 'OUT';
        } elseif ($qty <= $reorder) {
            $low[] = $r;
            $status = 'LOW';
        } else {
            continue;
        }
        $last = isset($cooldown[$code]) ? (int)$cooldown[$code] : 0;
        if ($last > 0 && ($now - $last) < ($cooldownHours * 3600)) {
            continue;
        }
        $toAlert[] = ['row' => $r, 'status' => $status];
    }

    if (count($toAlert) === 0) {
        return [
            'sent' => false,
            'reason' => empty($low) && empty($out) ? 'all_ok' : 'cooldown',
            'low_count' => count($low),
            'out_count' => count($out),
            'to' => $admin,
        ];
    }

    $lines = ['Toner Inventory — Low Stock Alert', 'Generated: ' . date('Y-m-d H:i:s'), str_repeat('-', 48)];
    $rowsHtml = '';
    foreach ($toAlert as $item) {
        $r = $item['row'];
        $label = $item['status'] === 'OUT' ? 'OUT OF STOCK' : 'LOW STOCK';
        $lines[] = sprintf(
            '[%s] %s | On hand: %d | Reorder at: %d | Supplier: %s',
            $label, $r['ink_code'], (int)$r['quantity'], (int)$r['reorder_level'], $r['supplier'] ?: '—'
        );
        $badge = $item['status'] === 'OUT'
            ? '<span style="color:#b91c1c;font-weight:700;">OUT OF STOCK</span>'
            : '<span style="color:#d97706;font-weight:700;">LOW STOCK</span>';
        $rowsHtml .= '<tr>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($r['ink_code']) . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;">' . $badge . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . (int)$r['quantity'] . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . (int)$r['reorder_level'] . '</td>'
            . '<td style="padding:8px;border:1px solid #e2e8f0;">' . htmlspecialchars($r['supplier'] ?: '—') . '</td>'
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
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:left">Toner</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:left">Status</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:right">On hand</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:right">Reorder</th>'
        . '<th style="padding:8px;border:1px solid #e2e8f0;text-align:left">Supplier</th>'
        . '</tr></thead><tbody>' . $rowsHtml . '</tbody></table>'
        . '<p style="margin-top:16px;">Please reorder the items above.</p></div>';

    $subject = ($cfg['subject_prefix'] ?? '[Toner Alert]') . ' ' . count($toAlert) . ' item(s) need attention';
    $send = send_system_mail($admin, $subject, $bodyText, $bodyHtml);

    if (!empty($send['ok'])) {
        foreach ($toAlert as $item) {
            $cooldown[$item['row']['ink_code']] = $now;
        }
        save_alert_cooldown($cooldown);
    }

    return [
        'sent' => !empty($send['ok']),
        'to' => $admin,
        'alerted' => count($toAlert),
        'low_count' => count($low),
        'out_count' => count($out),
        'driver' => $send['driver'] ?? $cfg['driver'],
        'error' => $send['error'] ?? null,
    ];
}