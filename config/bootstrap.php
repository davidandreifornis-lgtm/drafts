<?php
/**
 * Shared bootstrap: PDO, CORS, JSON helpers.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$config = require __DIR__ . '/database.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    global $config;
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['dbname'],
        $config['charset']
    );
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST ?: [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $message, int $code = 400, array $extra = []): void {
    respond(array_merge(['ok' => false, 'error' => $message], $extra), $code);
}

function ok($data = [], int $code = 200): void {
    respond(array_merge(['ok' => true], is_array($data) ? $data : ['data' => $data]), $code);
}

function normalize_ref(string $ref): string {
    return strtoupper(trim($ref));
}

function new_txn_code(PDO $pdo): string {
    $n = (int)$pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn() + 1;
    return 'TXN-' . str_pad((string)$n, 5, '0', STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(2)));
}