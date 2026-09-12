<?php
require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$in = json_input();
$ref = normalize_ref($in['referenceNumber'] ?? '');
$inkCode = strtoupper(trim($in['inkCode'] ?? ''));
$qty = (int)($in['quantity'] ?? 0);
$date = trim($in['date'] ?? date('Y-m-d'));
$supplier = trim($in['supplier'] ?? '');

if ($ref === '') fail('Delivery reference is required.');
if ($inkCode === '') fail('Toner code is required.');
if ($qty < 1) fail('Quantity must be at least 1.');
if ($supplier === '') fail('Supplier is required.');
if ($date === '') $date = date('Y-m-d');

$pdo = db();

try {
    $pdo->beginTransaction();

    $dup = $pdo->prepare('SELECT id FROM transactions WHERE reference_number = ? LIMIT 1');
    $dup->execute([$ref]);
    if ($dup->fetch()) {
        $pdo->rollBack();
        fail('This reference was already recorded.', 409, ['duplicate' => true, 'referenceNumber' => $ref]);
    }

    $inv = $pdo->prepare('SELECT * FROM inventory WHERE ink_code = ? FOR UPDATE');
    $inv->execute([$inkCode]);
    $row = $inv->fetch();
    if (!$row) {
        $pdo->rollBack();
        fail("Toner {$inkCode} not found in inventory. Add it first.");
    }

    $newQty = (int)$row['quantity'] + $qty;
    $upd = $pdo->prepare('UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE id = ?');
    $upd->execute([$newQty, $row['id']]);

    $txnCode = new_txn_code($pdo);
    $ins = $pdo->prepare(
        'INSERT INTO transactions
         (txn_code, type, reference_number, ink_code, quantity, txn_date, supplier, purpose, status)
         VALUES (?, \'RECEIVED\', ?, ?, ?, ?, ?, ?, \'RECORDED\')'
    );
    $ins->execute([$txnCode, $ref, $inkCode, $qty, $date, $supplier, 'Stock delivery']);

    $pdo->commit();
    ok([
        'message' => 'Delivery recorded',
        'referenceNumber' => $ref,
        'inkCode' => $inkCode,
        'quantity' => $qty,
        'newStock' => $newQty,
        'txnCode' => $txnCode,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fail('Server error: ' . $e->getMessage(), 500);
}