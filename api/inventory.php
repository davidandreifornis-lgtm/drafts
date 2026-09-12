<?php
require_once __DIR__ . '/../config/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = db();

try {
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT * FROM inventory ORDER BY ink_code ASC');
        $rows = $stmt->fetchAll();
        $items = array_map('map_inventory_row', $rows);
        ok(['items' => $items]);
    }

    if ($method === 'POST') {
        $in = json_input();
        $code = strtoupper(trim($in['inkCode'] ?? ''));
        $printer = trim($in['printerModel'] ?? '');
        $supplier = trim($in['supplier'] ?? '');
        $qty = max(0, (int)($in['quantity'] ?? 0));
        $reorder = max(0, (int)($in['reorderLevel'] ?? 3));
        $brand = trim($in['brand'] ?? '');
        $color = trim($in['color'] ?? 'Black');

        if ($code === '') fail('Toner code is required.');
        if ($printer === '') fail('Compatible printer(s) are required.');
        if ($supplier === '') fail('Supplier is required.');

        $check = $pdo->prepare('SELECT id FROM inventory WHERE ink_code = ?');
        $check->execute([$code]);
        if ($check->fetch()) fail("Toner {$code} already exists.", 409);

        $stmt = $pdo->prepare(
            'INSERT INTO inventory (ink_code, brand, printer_model, color, quantity, reorder_level, supplier)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$code, $brand, $printer, $color, $qty, $reorder, $supplier]);
        $id = (int)$pdo->lastInsertId();
        $row = $pdo->query("SELECT * FROM inventory WHERE id = {$id}")->fetch();
        ok(['item' => map_inventory_row($row)], 201);
    }

    if ($method === 'DELETE') {
        $in = json_input();
        $code = strtoupper(trim($in['inkCode'] ?? ($_GET['inkCode'] ?? '')));
        if ($code === '') fail('Toner code is required.');
        $stmt = $pdo->prepare('DELETE FROM inventory WHERE ink_code = ?');
        $stmt->execute([$code]);
        if ($stmt->rowCount() === 0) fail('Toner not found.', 404);
        ok(['deleted' => $code]);
    }

    fail('Method not allowed', 405);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}

function map_inventory_row(array $r): array {
    return [
        'id' => 'TNR-' . $r['id'],
        'inkCode' => $r['ink_code'],
        'brand' => $r['brand'],
        'printerModel' => $r['printer_model'],
        'color' => $r['color'],
        'quantity' => (int)$r['quantity'],
        'reorderLevel' => (int)$r['reorder_level'],
        'supplier' => $r['supplier'],
        'department' => '',
        'location' => '',
        'serialNumbers' => [],
        'createdAt' => $r['created_at'],
        'updatedAt' => $r['updated_at'],
    ];
}