<?php
require_once __DIR__ . '/../config/bootstrap.php';
auth_require_api();

$pdo = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // Ensure table exists
    try {
        $pdo->query('SELECT TOP 1 id FROM dbo.toner_users');
    } catch (Throwable $e) {
        fail(
            'Table dbo.toner_users is missing. Run sql/create_users.sql on database toner_inventory. Detail: ' . $e->getMessage(),
            500
        );
    }

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT id, username, full_name, role, is_active, created_at, updated_at
             FROM dbo.toner_users ORDER BY username ASC'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array_map(function ($r) {
            $r = array_change_key_case($r, CASE_LOWER);
            return [
                'id' => (int)$r['id'],
                'username' => $r['username'],
                'fullName' => $r['full_name'] ?? '',
                'role' => $r['role'] ?? 'admin',
                'isActive' => !empty($r['is_active']),
                'createdAt' => (string)($r['created_at'] ?? ''),
                'updatedAt' => (string)($r['updated_at'] ?? ''),
            ];
        }, $rows);
        ok(['users' => $users]);
    }

    if ($method === 'POST') {
        $in = json_input();
        $username = strtolower(trim($in['username'] ?? ''));
        $password = (string)($in['password'] ?? '');
        $fullName = trim($in['fullName'] ?? '');
        $role = strtolower(trim($in['role'] ?? 'admin'));

        if ($username === '') fail('Username is required.');
        if (strlen($username) < 3) fail('Username must be at least 3 characters.');
        if (strlen($password) < 6) fail('Password must be at least 6 characters.');
        if (!in_array($role, ['admin'], true)) {
            $role = 'admin'; // only admin role for now
        }

        $check = $pdo->prepare('SELECT id FROM dbo.toner_users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetch()) fail('Username already exists.', 409);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO dbo.toner_users (username, password_hash, full_name, role, is_active, created_at, updated_at)
             OUTPUT INSERTED.id, INSERTED.username, INSERTED.full_name, INSERTED.role, INSERTED.is_active
             VALUES (?, ?, ?, ?, 1, SYSUTCDATETIME(), SYSUTCDATETIME())'
        );
        $stmt->execute([$username, $hash, $fullName, $role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $row = array_change_key_case($row ?: [], CASE_LOWER);
        ok([
            'user' => [
                'id' => (int)($row['id'] ?? 0),
                'username' => $row['username'] ?? $username,
                'fullName' => $row['full_name'] ?? $fullName,
                'role' => $row['role'] ?? $role,
                'isActive' => true,
            ],
        ], 201);
    }

    if ($method === 'PUT') {
        $in = json_input();
        $id = (int)($in['id'] ?? 0);
        if ($id <= 0) fail('User id is required.');

        $fullName = trim($in['fullName'] ?? '');
        $isActive = array_key_exists('isActive', $in) ? (!empty($in['isActive']) ? 1 : 0) : null;
        $password = (string)($in['password'] ?? '');

        // Prevent deactivating yourself
        if ($isActive === 0 && $id === auth_user_id()) {
            fail('You cannot deactivate your own account.');
        }

        if ($password !== '') {
            if (strlen($password) < 6) fail('Password must be at least 6 characters.');
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'UPDATE dbo.toner_users
                 SET full_name = ?, password_hash = ?, updated_at = SYSUTCDATETIME()'
                . ($isActive !== null ? ', is_active = ?' : '')
                . ' WHERE id = ?'
            );
            $params = [$fullName, $hash];
            if ($isActive !== null) $params[] = $isActive;
            $params[] = $id;
            $stmt->execute($params);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE dbo.toner_users SET full_name = ?, updated_at = SYSUTCDATETIME()'
                . ($isActive !== null ? ', is_active = ?' : '')
                . ' WHERE id = ?'
            );
            $params = [$fullName];
            if ($isActive !== null) $params[] = $isActive;
            $params[] = $id;
            $stmt->execute($params);
        }
        ok(['message' => 'User updated', 'id' => $id]);
    }

    if ($method === 'DELETE') {
        $in = json_input();
        $id = (int)($in['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) fail('User id is required.');
        if ($id === auth_user_id()) {
            fail('You cannot delete your own account.');
        }

        // Keep at least one active admin
        $cnt = (int)$pdo->query('SELECT COUNT(*) AS c FROM dbo.toner_users WHERE is_active = 1')->fetch()['c'];
        $target = $pdo->prepare('SELECT is_active FROM dbo.toner_users WHERE id = ?');
        $target->execute([$id]);
        $t = $target->fetch();
        if (!$t) fail('User not found.', 404);
        $t = array_change_key_case($t, CASE_LOWER);
        if (!empty($t['is_active']) && $cnt <= 1) {
            fail('Cannot delete the last active admin.');
        }

        $stmt = $pdo->prepare('DELETE FROM dbo.toner_users WHERE id = ?');
        $stmt->execute([$id]);
        ok(['deleted' => $id]);
    }

    fail('Method not allowed', 405);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}