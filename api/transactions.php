<?php
require_once __DIR__ . '/../config/bootstrap.php';

$pdo = db();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        fail('Method not allowed', 405);
    }

    $type = strtoupper(trim($_GET['type'] ?? ''));
    $sql = 'SELECT * FROM transactions';
    $params = [];
    if (in_array($type, ['RECEIVED', 'RELEASED', 'DEFECTIVE'], true)) {
        $sql .= ' WHERE type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY created_at ASC, id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    ok(['transactions' => array_map('map_txn_row', $rows)]);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}

function map_txn_row(array $r): array {
    return [
        'id' => $r['txn_code'],
        'type' => $r['type'],
        'referenceNumber' => $r['reference_number'],
        'inkId' => '',
        'inkCode' => $r['ink_code'],
        'serialNumber' => 'N/A',
        'brand' => '',
        'color' => '',
        'quantity' => (int)$r['quantity'],
        'date' => $r['txn_date'],
        'supplier' => $r['supplier'] ?? '',
        'givenTo' => $r['given_to'] ?? '',
        'department' => $r['department'] ?? '',
        'location' => $r['location'] ?? '',
        'purpose' => $r['purpose'] ?? '',
        'status' => $r['status'],
        'defective' => (bool)$r['defective'],
        'createdAt' => $r['created_at'],
    ];
}