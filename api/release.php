<?php
/**
 * Stock Issuance — deduct 1 unit from dbo.toner_inventory (item_code)
 * and log RELEASED in dbo.toner_transactions (ink_code column stores the item code).
 */
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/mailer.php';
auth_require_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$in = json_input();
$ref = normalize_ref($in['referenceNumber'] ?? $in['ref'] ?? '');
// Frontend may send inkCode or itemCode (both = item_code in inventory)
$inkCode = strtoupper(trim((string)($in['inkCode'] ?? $in['itemCode'] ?? $in['item_code'] ?? '')));
$dept = strtoupper(trim((string)($in['department'] ?? '')));
$location = trim((string)($in['location'] ?? ''));
$date = date('Y-m-d'); // always current day

if ($ref === '') fail('Issuance reference is required.');
if ($inkCode === '') fail('Item code is required. Select an item from the list.');
if ($dept === '') fail('Department is required.');
if ($location === '') fail('Location is required.');

$pdo = db();

try {
    $pdo->beginTransaction();

    $dup = $pdo->prepare('SELECT id FROM dbo.toner_transactions WHERE reference_number = ?');
    $dup->execute([$ref]);
    if ($dup->fetch()) {
        $pdo->rollBack();
        fail('This reference was already recorded.', 409, [
            'duplicate' => true,
            'referenceNumber' => $ref,
        ]);
    }

    // Inventory uses item_code (not ink_code)
    $inv = $pdo->prepare(
        'SELECT * FROM dbo.toner_inventory WITH (UPDLOCK, ROWLOCK) WHERE item_code = ?'
    );
    $inv->execute([$inkCode]);
    $row = $inv->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $pdo->rollBack();
        fail("Item {$inkCode} not found in toner_inventory. Receive it via MRR or Add Toner first.");
    }
    $row = array_change_key_case($row, CASE_LOWER);

    $onHand = (int)($row['quantity'] ?? 0);
    if ($onHand < 1) {
        $pdo->rollBack();
        fail("Insufficient stock for {$inkCode} (0 on hand).");
    }

    $newQty = $onHand - 1;
    $upd = $pdo->prepare(
        'UPDATE dbo.toner_inventory
         SET quantity = ?, updated_at = SYSUTCDATETIME()
         WHERE id = ?'
    );
    $upd->execute([$newQty, (int)$row['id']]);

    // Transactions table still stores the code in column ink_code
    $txnCode = new_txn_code($pdo);
    $ins = $pdo->prepare(
        "INSERT INTO dbo.toner_transactions
         (txn_code, type, reference_number, ink_code, quantity, txn_date, department, location, purpose, status, created_at)
         VALUES (?, 'RELEASED', ?, ?, 1, ?, ?, ?, ?, 'RECORDED', SYSUTCDATETIME())"
    );
    $ins->execute([
        $txnCode,
        $ref,
        $inkCode,
        $date,
        $dept,
        $location,
        'Stock issuance',
    ]);

    $pdo->commit();

    try {
        notify_low_stock($pdo);
    } catch (Throwable $e) {
        /* ignore mail errors */
    }

    ok([
        'message' => 'Issuance recorded',
        'referenceNumber' => $ref,
        'inkCode' => $inkCode,
        'itemCode' => $inkCode,
        'quantity' => 1,
        'newStock' => $newQty,
        'department' => $dept,
        'location' => $location,
        'txnCode' => $txnCode,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fail('Server error: ' . $e->getMessage(), 500);
}