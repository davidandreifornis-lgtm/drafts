<?php
require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$in = json_input();
$ref = normalize_ref($in['referenceNumber'] ?? '');
$notes = trim($in['notes'] ?? '');

if ($ref === '') fail('Issuance reference is required.');

$pdo = db();

try {
    $pdo->beginTransaction();

    $rel = $pdo->prepare(
        "SELECT * FROM transactions WHERE reference_number = ? AND type = 'RELEASED' LIMIT 1 FOR UPDATE"
    );
    $rel->execute([$ref]);
    $release = $rel->fetch();
    if (!$release) {
        $pdo->rollBack();
        fail('No completed issuance found for this ticket.');
    }

    $already = $pdo->prepare(
        "SELECT id FROM transactions WHERE reference_number = ? AND type = 'DEFECTIVE' LIMIT 1"
    );
    $already->execute([$ref]);
    if ($already->fetch()) {
        $pdo->rollBack();
        fail('This issuance was already flagged as defective.');
    }

    $flag = $pdo->prepare(
        'UPDATE transactions SET defective = 1, defective_at = NOW(), defective_notes = ? WHERE id = ?'
    );
    $flag->execute([$notes, $release['id']]);

    $txnCode = new_txn_code($pdo);
    $ins = $pdo->prepare(
        "INSERT INTO transactions
         (txn_code, type, reference_number, ink_code, quantity, txn_date, department, location, purpose, status, defective)
         VALUES (?, 'DEFECTIVE', ?, ?, 1, CURDATE(), ?, ?, ?, 'DEFECTIVE', 1)"
    );
    $ins->execute([
        $txnCode,
        $ref,
        $release['ink_code'],
        $release['department'],
        $release['location'],
        $notes !== '' ? $notes : 'Defective return',
    ]);

    $pdo->commit();
    ok([
        'message' => 'Flagged as defective',
        'referenceNumber' => $ref,
        'inkCode' => $release['ink_code'],
        'txnCode' => $txnCode,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fail('Server error: ' . $e->getMessage(), 500);
}