<?php
/**
 * Lookup MRR lines from ERP linked server.
 * GET ?mrr=MG009105
 *
 * Requires: logged-in session + linked server reachable from toner_inventory SQL instance.
 */
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_api();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    fail('Method not allowed', 405);
}

$mrr = strtoupper(trim($_GET['mrr'] ?? $_GET['mrr_no'] ?? ''));
if ($mrr === '') {
    fail('MRR number is required. Example: MG009105');
}

$pdo = db();

// Linked server / catalog / schema can be adjusted here if needed
$erpServer  = 'VM-EGNSERVER';
$erpCatalog = '100';
$erpSchema  = 'DBO';

$sql = "
SELECT
  gmt.ExternalNumber AS MRR_no,
  gmt.aantal AS MRR_Qty,
  CASE WHEN gmt.oorsprong = 'R' THEN gmt.datum END AS MRR_Date,
  gmt.artcode AS Item_code,
  i.Description_0 AS Item_Desc
FROM [{$erpServer}].[{$erpCatalog}].[{$erpSchema}].[gbkmut] gmt WITH (NOLOCK)
INNER JOIN [{$erpServer}].[{$erpCatalog}].[{$erpSchema}].[Items] i WITH (NOLOCK)
  ON i.ItemCode = gmt.artcode
 AND (gmt.reknr = i.GLAccountDistribution OR gmt.reknr = i.GLAccountAsset)
WHERE gmt.transtype IN ('X', 'N', 'C', 'P')
  AND gmt.oorsprong = 'R'
  AND gmt.transsubtype IN ('A')
  AND gmt.bkstnr_sub IS NOT NULL
  AND i.Condition IN ('A')
  AND i.Type <> 'P'
  AND gmt.ExternalNumber = ?
";

try {
    $dup = $pdo->prepare('SELECT TOP 1 id FROM dbo.toner_transactions WHERE reference_number = ?');
    $dup->execute([$mrr]);
    $already = (bool)$dup->fetch();

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mrr]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        fail(
            "MRR {$mrr} not found in ERP (no matching lines). Check the number or ERP filters.",
            404,
            ['mrr' => $mrr, 'alreadyRecorded' => $already]
        );
    }

    $lines = [];
    $totalQty = 0;
    $mrrDate = null;

    foreach ($rows as $r) {
        $r = array_change_key_case($r, CASE_LOWER);
        $itemCode = strtoupper(trim((string)($r['item_code'] ?? '')));
        $qty = (int)round((float)($r['mrr_qty'] ?? 0));
        $desc = trim((string)($r['item_desc'] ?? ''));
        $dateRaw = $r['mrr_date'] ?? null;
        $date = $dateRaw ? substr((string)$dateRaw, 0, 10) : '';
        if ($date && !$mrrDate) $mrrDate = $date;
        if ($itemCode === '' || $qty < 1) continue;

        $inv = $pdo->prepare('SELECT quantity FROM dbo.toner_inventory WHERE item_code = ?');
        $inv->execute([$itemCode]);
        $invRow = $inv->fetch(PDO::FETCH_ASSOC);
        $invRow = $invRow ? array_change_key_case($invRow, CASE_LOWER) : null;
        $current = $invRow ? (int)$invRow['quantity'] : 0;

        $lines[] = [
            'mrrNo' => strtoupper(trim((string)($r['mrr_no'] ?? $mrr))),
            'itemCode' => $itemCode,
            'description' => $desc,
            'quantity' => $qty,
            'date' => $date,
            'inInventory' => (bool)$invRow,
            'currentStock' => $current,
            'projectedStock' => $current + $qty,
        ];
        $totalQty += $qty;
    }

    if (!$lines) {
        fail("MRR {$mrr} returned rows but none had a valid item code / qty.", 404);
    }

    ok([
        'mrr' => $mrr,
        'mrrDate' => $mrrDate,
        'alreadyRecorded' => $already,
        'lineCount' => count($lines),
        'totalQty' => $totalQty,
        'lines' => $lines,
    ]);
} catch (Throwable $e) {
    $msg = $e->getMessage();
    $hint = '';
    if (stripos($msg, 'linked') !== false || stripos($msg, 'VM-EGNSERVER') !== false || stripos($msg, 'could not find server') !== false) {
        $hint = ' Linked server [VM-EGNSERVER] may be missing on the SQL instance used by PHP (VMAPPS2).';
    }
    fail('MRR lookup failed: ' . $msg . $hint, 500, ['mrr' => $mrr]);
}