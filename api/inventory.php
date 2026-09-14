<?php
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_api();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo = db();

try {
    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT id, ink_code, brand, printer_model, quantity, reorder_level, supplier, created_at, updated_at
             FROM dbo.toner_inventory ORDER BY ink_code ASC'
        );
        $items = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $items[] = map_inventory_row($r);
        }
        ok([
            'items' => $items,
            'database' => 'toner_inventory',
            'count' => count($items),
        ]);
    }

    if ($method === 'POST') {
        $in = json_input();
        $code = strtoupper(trim($in['inkCode'] ?? ''));
        $printer = trim($in['printerModel'] ?? '');
        $supplier = trim($in['supplier'] ?? '');
        $qty = max(0, (int)($in['quantity'] ?? 0));
        $reorder = max(0, (int)($in['reorderLevel'] ?? 3));
        $brand = trim($in['brand'] ?? '');

        if ($code === '') fail('Toner code is required.');
        if ($printer === '') fail('Compatible printer(s) are required.');
        if ($supplier === '') fail('Supplier is required.');

        $check = $pdo->prepare('SELECT id FROM dbo.toner_inventory WHERE ink_code = ?');
        $check->execute([$code]);
        if ($check->fetch()) fail("Toner {$code} already exists.", 409);

        $stmt = $pdo->prepare(
            'INSERT INTO dbo.toner_inventory
                (ink_code, brand, printer_model, quantity, reorder_level, supplier, created_at, updated_at)
             OUTPUT INSERTED.id, INSERTED.ink_code, INSERTED.brand, INSERTED.printer_model,
                    INSERTED.quantity, INSERTED.reorder_level, INSERTED.supplier,
                    INSERTED.created_at, INSERTED.updated_at
             VALUES (?, ?, ?, ?, ?, ?, SYSUTCDATETIME(), SYSUTCDATETIME())'
        );
        $stmt->execute([$code, $brand, $printer, $qty, $reorder, $supplier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $rowStmt = $pdo->prepare('SELECT * FROM dbo.toner_inventory WHERE ink_code = ?');
            $rowStmt->execute([$code]);
            $row = $rowStmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$row) {
            fail('Insert ran but row not found in dbo.toner_inventory on database toner_inventory.', 500);
        }

        ok([
            'item' => map_inventory_row($row),
            'database' => 'toner_inventory',
            'message' => 'Saved to dbo.toner_inventory',
        ], 201);
    }

    
    if ($method === 'PUT') {
        $in = json_input();
        $code = strtoupper(trim($in['inkCode'] ?? ''));
        if ($code === '') fail('Toner code is required.');

        $stmt = $pdo->prepare('SELECT * FROM dbo.toner_inventory WHERE ink_code = ?');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) fail('Toner not found.', 404);
        $row = array_change_key_case($row, CASE_LOWER);

        $printer = array_key_exists('printerModel', $in) ? trim((string)$in['printerModel']) : (string)$row['printer_model'];
        $supplier = array_key_exists('supplier', $in) ? trim((string)$in['supplier']) : (string)$row['supplier'];
        $qty = array_key_exists('quantity', $in) ? max(0, (int)$in['quantity']) : (int)$row['quantity'];
        $reorder = array_key_exists('reorderLevel', $in) ? max(0, (int)$in['reorderLevel']) : (int)$row['reorder_level'];

        if ($printer === '') fail('Compatible printer(s) are required.');
        if ($supplier === '') fail('Supplier is required.');

        $oldQty = (int)$row['quantity'];
        $upd = $pdo->prepare(
            'UPDATE dbo.toner_inventory
             SET printer_model = ?, supplier = ?, quantity = ?, reorder_level = ?, updated_at = SYSUTCDATETIME()
             WHERE ink_code = ?'
        );
        $upd->execute([$printer, $supplier, $qty, $reorder, $code]);

        // Optional stock adjustment note in transactions when qty changes
        if ($qty !== $oldQty) {
            $diff = $qty - $oldQty;
            $txnCode = new_txn_code($pdo);
            $type = $diff > 0 ? 'RECEIVED' : 'RELEASED';
            $abs = abs($diff);
            $ref = 'ADJ-' . date('Ymd-His');
            $ins = $pdo->prepare(
                "INSERT INTO dbo.toner_transactions
                 (txn_code, type, reference_number, ink_code, quantity, txn_date, supplier, purpose, status, created_at)
                 VALUES (?, ?, ?, ?, ?, CAST(GETDATE() AS DATE), ?, ?, 'RECORDED', SYSUTCDATETIME())"
            );
            $purpose = $diff > 0
                ? 'Stock card adjustment (+' . $abs . ')'
                : 'Stock card adjustment (-' . $abs . ')';
            $ins->execute([
                $txnCode,
                $type,
                $ref,
                $code,
                $abs,
                $diff > 0 ? $supplier : null,
                $purpose,
            ]);
        }

        $rowStmt = $pdo->prepare('SELECT * FROM dbo.toner_inventory WHERE ink_code = ?');
        $rowStmt->execute([$code]);
        $fresh = $rowStmt->fetch(PDO::FETCH_ASSOC);
        ok(['item' => map_inventory_row($fresh), 'message' => 'Stock card updated']);
    }

    if ($method === 'DELETE') {
        $in = json_input();
        $code = strtoupper(trim($in['inkCode'] ?? ($_GET['inkCode'] ?? '')));
        if ($code === '') fail('Toner code is required.');
        $stmt = $pdo->prepare('DELETE FROM dbo.toner_inventory WHERE ink_code = ?');
        $stmt->execute([$code]);
        if ($stmt->rowCount() === 0) fail('Toner not found in dbo.toner_inventory.', 404);
        ok(['deleted' => $code, 'database' => 'toner_inventory']);
    }

    fail('Method not allowed', 405);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}

function map_inventory_row(array $r): array {
    $r = array_change_key_case($r, CASE_LOWER);
    return [
        'id' => 'TNR-' . ($r['id'] ?? ''),
        'inkCode' => (string)($r['ink_code'] ?? ''),
        'brand' => (string)($r['brand'] ?? ''),
        'printerModel' => (string)($r['printer_model'] ?? ''),
        'quantity' => (int)($r['quantity'] ?? 0),
        'reorderLevel' => (int)($r['reorder_level'] ?? 0),
        'supplier' => (string)($r['supplier'] ?? ''),
        'department' => '',
        'location' => '',
        'serialNumbers' => [],
        'createdAt' => (string)($r['created_at'] ?? ''),
        'updatedAt' => (string)($r['updated_at'] ?? ''),
    ];
}