<?php
require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$in = json_input();
$ref = normalize_ref($in['referenceNumber'] ?? '');
$inkCode = strtoupper(trim($in['inkCode'] ?? ''));
$dept = strtoupper(trim($in['department'] ?? ''));
$location = trim($in['location'] ?? '');
$qty = 1;
$date = date('Y-m-d');

if ($ref === '') fail('Issuance reference is required.');
if ($inkCode === '') fail('Toner code is required.');
if ($dept === '') fail('Department is required.');
if ($location === '') fail('Location is required.');

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
        fail("Toner {$inkCode} not found in inventory.");
    }
    if ((int)$row['quantity'] < 1) {
        $pdo->rollBack();
        fail("Insufficient stock for {$inkCode} (0 on hand).");
    }

    $newQty = (int)$row['quantity'] - 1;
    $upd = $pdo->prepare('UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE id = ?');
    $upd->execute([$newQty, $row['id']]);

    $txnCode = new_txn_code($pdo);
    $ins = $pdo->prepare(
        'INSERT INTO transactions
         (txn_code, type, reference_number, ink_code, quantity, txn_date, department, location, purpose, status)
         VALUES (?, \'RELEASED\', ?, ?, 1, ?, ?, ?, ?, \'RECORDED\')'
    );
    $ins->execute([$txnCode, $ref, $inkCode, $date, $dept, $location, 'Stock issuance']);

    $pdo->commit();
    ok([
        'message' => 'Issuance recorded',
        'referenceNumber' => $ref,
        'inkCode' => $inkCode,
        'quantity' => 1,
        'newStock' => $newQty,
        'department' => $dept,
        'location' => $location,
        'txnCode' => $txnCode,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fail('Server error: ' . $e->getMessage(), 500);
}