<?php
/**
 * Email settings — use SMTP for real delivery (XAMPP mail() almost never works).
 *
 * Gmail setup:
 * 1. Google Account → Security → 2-Step Verification (on)
 * 2. App passwords → generate one for "Mail"
 * 3. Put that 16-char password in smtp_pass below
 */
return [
    'admin_email'    => getenv('MAIL_ADMIN') ?: 'davidandreifornis@gmail.com',
    'from_email'     => getenv('MAIL_FROM') ?: 'davidandreifornis@gmail.com',
    'from_name'      => 'Toner Inventory System',
    'subject_prefix' => '[Toner Alert]',
    'cooldown_hours' => 12,
    'cooldown_file'  => __DIR__ . '/../storage/low_stock_alerts.json',

    /**
     * driver:
     *   smtp  = real email via Gmail/other SMTP (recommended)
     *   log   = only write storage/mail.log (testing)
     *   mail  = PHP mail() (needs server MTA; fails on most XAMPP)
     */
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp',

    // SMTP (Gmail example)
    'smtp_host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtp_port'       => (int)(getenv('SMTP_PORT') ?: 587),
    'smtp_encryption' => getenv('SMTP_ENC') ?: 'tls', // tls or ssl
    'smtp_user'       => getenv('SMTP_USER') ?: 'davidandreifornis@gmail.com',
    'smtp_pass'       => getenv('SMTP_PASS') ?: 'hvts qfok yecm qvbu',
];