<?php
require_once __DIR__ . '/../config/bootstrap.php';
try {
    $pdo = db();
    $pdo->query('SELECT 1');
    ok(['status' => 'up', 'database' => true]);
} catch (Throwable $e) {
    fail('Database connection failed: ' . $e->getMessage(), 500, ['database' => false]);
}