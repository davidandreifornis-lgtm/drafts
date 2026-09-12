<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/mailer.php';

try {
    // Optional: ?force=1 clears cooldown so you can retest immediately
    if (!empty($_GET['force'])) {
        $cfg = mail_config();
        @file_put_contents($cfg['cooldown_file'], '{}');
    }
    $result = notify_low_stock(db());
    ok(['message' => 'Low-stock check complete', 'result' => $result]);
} catch (Throwable $e) {
    fail('Low-stock check failed: ' . $e->getMessage(), 500);
}