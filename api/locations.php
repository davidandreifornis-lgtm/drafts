<?php
/**
 * CRUD for editable department / location / printer / IP mappings.
 * Rules:
 *  - Same department, location, or printer name alone is allowed
 *  - Exact department + location + printer triple must be unique among active rows
 */
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/activity_log.php';
auth_require_api();

$pdo = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function ensure_locations_table(PDO $pdo): void {
    static $done = false;
    if ($done) return;
    try {
        $pdo->query('SELECT TOP 1 id FROM dbo.toner_locations');
    } catch (Throwable $e) {
        fail(
            'Table dbo.toner_locations is missing. Run sql/migration_issuance_fields.sql on database toner_inventory. Detail: ' . $e->getMessage(),
            500
        );
    }
    // Ensure ip_address column exists (safe to run repeatedly)
    try {
        $pdo->exec("
            IF COL_LENGTH('dbo.toner_locations', 'ip_address') IS NULL
            BEGIN
                ALTER TABLE dbo.toner_locations ADD ip_address NVARCHAR(45) NULL;
            END
        ");
    } catch (Throwable $e) {
        // Non-fatal: column may already exist or permissions limited
    }
    $done = true;
}

function map_loc(array $r): array {
    $r = array_change_key_case($r, CASE_LOWER);
    return [
        'id' => (int)($r['id'] ?? 0),
        'department' => (string)($r['department'] ?? ''),
        'location' => (string)($r['location'] ?? ''),
        'printerName' => (string)($r['printer_name'] ?? ''),
        'ipAddress' => (string)($r['ip_address'] ?? ''),
        'isActive' => !empty($r['is_active']),
        'createdAt' => (string)($r['created_at'] ?? ''),
        'updatedAt' => (string)($r['updated_at'] ?? ''),
    ];
}

function normalize_ip(?string $ip): string {
    $ip = trim((string)$ip);
    if ($ip === '') return '';
    // Allow IPv4 / IPv6 / hostname-like values up to 45 chars; basic sanity
    if (strlen($ip) > 45) {
        fail('IP address is too long (max 45 characters).', 400);
    }
    return $ip;
}

/**
 * Enforce uniqueness among active rows.
 * Same department, location, or printer name alone is OK.
 * Only the exact triple (department + location + printer) must be unique.
 * @param int|null $excludeId  Row id to exclude (on update)
 */
function assert_location_unique(PDO $pdo, string $dept, string $loc, string $printer, ?int $excludeId = null): void {
    if ($printer === '') {
        fail('Printer assigned is required.', 400);
    }

    // Exact triple department + location + printer must be unique (case-insensitive)
    $sqlTriple = "SELECT TOP 1 id
                  FROM dbo.toner_locations
                  WHERE is_active = 1
                    AND UPPER(LTRIM(RTRIM(department))) = UPPER(?)
                    AND LOWER(LTRIM(RTRIM(location))) = LOWER(?)
                    AND LOWER(LTRIM(RTRIM(ISNULL(printer_name, '')))) = LOWER(?)";
    $paramsT = [$dept, $loc, $printer];
    if ($excludeId !== null && $excludeId > 0) {
        $sqlTriple .= ' AND id <> ?';
        $paramsT[] = $excludeId;
    }
    $st2 = $pdo->prepare($sqlTriple);
    $st2->execute($paramsT);
    if ($st2->fetch(PDO::FETCH_ASSOC)) {
        fail(
            'This department + location + printer combination already exists. '
            . 'The same printer name can be used in other departments or locations.',
            409
        );
    }
}

try {
    ensure_locations_table($pdo);

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT id, department, location, printer_name, ip_address, is_active, created_at, updated_at
             FROM dbo.toner_locations
             WHERE is_active = 1
             ORDER BY department, location, printer_name'
        );
        $items = array_map('map_loc', $stmt->fetchAll(PDO::FETCH_ASSOC));
        ok(['locations' => $items, 'count' => count($items)]);
    }

    if ($method === 'POST') {
        $in = json_input();
        $dept = strtoupper(trim((string)($in['department'] ?? '')));
        $loc = trim((string)($in['location'] ?? ''));
        $printer = trim((string)($in['printerName'] ?? $in['printer_name'] ?? ''));
        $ip = normalize_ip($in['ipAddress'] ?? $in['ip_address'] ?? '');
        if ($dept === '') fail('Department is required.');
        if ($loc === '') fail('Location is required.');
        assert_location_unique($pdo, $dept, $loc, $printer, null);

        $stmt = $pdo->prepare(
            'INSERT INTO dbo.toner_locations (department, location, printer_name, ip_address, is_active, created_at, updated_at)
             OUTPUT INSERTED.*
             VALUES (?, ?, ?, ?, 1, SYSUTCDATETIME(), SYSUTCDATETIME())'
        );
        try {
            $stmt->execute([
                $dept,
                $loc,
                $printer,
                $ip !== '' ? $ip : null,
            ]);
        } catch (Throwable $e) {
            fail('Could not add location: ' . $e->getMessage(), 409);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $q = $pdo->prepare(
                'SELECT TOP 1 * FROM dbo.toner_locations
                 WHERE department = ? AND location = ? AND printer_name = ?
                 ORDER BY id DESC'
            );
            $q->execute([$dept, $loc, $printer]);
            $row = $q->fetch(PDO::FETCH_ASSOC);
        }
        activity_log('add_location', 'Added location', [
            'details' => $dept . ' / ' . $loc
                . ($printer !== '' ? ' — printer: ' . $printer : '')
                . ($ip !== '' ? ' — IP: ' . $ip : ''),
        ]);
        ok(['location' => map_loc($row ?: []), 'message' => 'Location added'], 201);
    }

    if ($method === 'PUT') {
        $in = json_input();
        $id = (int)($in['id'] ?? 0);
        if ($id < 1) fail('Location id is required.');
        $dept = strtoupper(trim((string)($in['department'] ?? '')));
        $loc = trim((string)($in['location'] ?? ''));
        $printer = trim((string)($in['printerName'] ?? $in['printer_name'] ?? ''));
        $ip = normalize_ip($in['ipAddress'] ?? $in['ip_address'] ?? '');
        if ($dept === '') fail('Department is required.');
        if ($loc === '') fail('Location is required.');
        assert_location_unique($pdo, $dept, $loc, $printer, $id);

        $upd = $pdo->prepare(
            'UPDATE dbo.toner_locations
             SET department = ?, location = ?, printer_name = ?, ip_address = ?, updated_at = SYSUTCDATETIME()
             WHERE id = ?'
        );
        $upd->execute([
            $dept,
            $loc,
            $printer,
            $ip !== '' ? $ip : null,
            $id,
        ]);
        $q = $pdo->prepare('SELECT * FROM dbo.toner_locations WHERE id = ?');
        $q->execute([$id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row) fail('Location not found.', 404);
        activity_log('edit_location', 'Edited location', [
            'details' => $dept . ' / ' . $loc
                . ($printer !== '' ? ' — printer: ' . $printer : '')
                . ($ip !== '' ? ' — IP: ' . $ip : ''),
        ]);
        ok(['location' => map_loc($row), 'message' => 'Location updated']);
    }

    if ($method === 'DELETE') {
        $in = json_input();
        $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
        if ($id < 1) fail('Location id is required.');
        $upd = $pdo->prepare(
            'UPDATE dbo.toner_locations SET is_active = 0, updated_at = SYSUTCDATETIME() WHERE id = ?'
        );
        $upd->execute([$id]);
        if ($upd->rowCount() === 0) fail('Location not found.', 404);
        activity_log('remove_location', 'Removed location', [
            'details' => 'Location id ' . $id,
        ]);
        ok(['deleted' => $id]);
    }

    fail('Method not allowed', 405);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}
