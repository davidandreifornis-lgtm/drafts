<?php
require_once __DIR__ . '/../config/bootstrap.php';

$pdo = db();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        fail('Method not allowed', 405);
    }

    $type = strtoupper(trim($_GET['type'] ?? ''));
    $from = trim($_GET['from'] ?? '');
    $to = trim($_GET['to'] ?? '');

    $sql = 'SELECT * FROM transactions WHERE 1=1';
    $params = [];

    if (in_array($type, ['RECEIVED', 'RELEASED', 'DEFECTIVE'], true)) {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    if ($from !== '') {
        $sql .= ' AND txn_date >= ?';
        $params[] = $from;
    }
    if ($to !== '') {
        $sql .= ' AND txn_date <= ?';
        $params[] = $to;
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