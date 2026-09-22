<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/activity_log.php';
auth_require_api();

$pdo = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    try {
        $pdo->query('SELECT TOP 1 id FROM dbo.toner_users');
    } catch (Throwable $e) {
        fail(
            'Table dbo.toner_users is missing. Run sql/create_users.sql. Detail: ' . $e->getMessage(),
            500
        );
    }

    // Keep email column in sync with username (username IS the notification address)
    try {
        $pdo->exec("IF COL_LENGTH('dbo.toner_users', 'email') IS NULL ALTER TABLE dbo.toner_users ADD email NVARCHAR(255) NULL");
    } catch (Throwable $e) { /* ignore */ }

    if ($method === 'GET') {
        $stmt = $pdo->query(
            'SELECT id, username, full_name, role, is_active, created_at, updated_at
             FROM dbo.toner_users ORDER BY username ASC'
        );
        $users = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $r = array_change_key_case($r, CASE_LOWER);
            $uname = (string)($r['username'] ?? '');
            $users[] = [
                'id' => (int)$r['id'],
                'username' => $uname,
                'fullName' => $r['full_name'] ?? '',
                'email' => $uname, // username is the email
                'role' => $r['role'] ?? 'admin',
                'isActive' => !empty($r['is_active']),
                'createdAt' => (string)($r['created_at'] ?? ''),
                'updatedAt' => (string)($r['updated_at'] ?? ''),
            ];
        }
        ok(['users' => $users]);
    }

    if ($method === 'POST') {
        $in = json_input();
        // Username must be a valid email (used for login + low-stock alerts)
        $username = strtolower(trim($in['username'] ?? $in['email'] ?? ''));
        $password = (string)($in['password'] ?? '');
        $fullName = trim($in['fullName'] ?? '');
        $role = 'admin';

        if ($username === '') fail('Email (username) is required.');
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            fail('Username must be a valid email address (used for notifications).');
        }
        if (strlen($password) < 6) fail('Password must be at least 6 characters.');

        $check = $pdo->prepare('SELECT id FROM dbo.toner_users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetch()) fail('This email is already registered.', 409);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO dbo.toner_users (username, password_hash, full_name, email, role, is_active, created_at, updated_at)
             OUTPUT INSERTED.id, INSERTED.username, INSERTED.full_name, INSERTED.role, INSERTED.is_active
             VALUES (?, ?, ?, ?, ?, 1, SYSUTCDATETIME(), SYSUTCDATETIME())'
        );
        $stmt->execute([$username, $hash, $fullName, $username, $role]);
        $row = array_change_key_case($stmt->fetch(PDO::FETCH_ASSOC) ?: [], CASE_LOWER);
        activity_log('add_user', 'Added admin user', ['details' => $username]);
        ok([
            'user' => [
                'id' => (int)($row['id'] ?? 0),
                'username' => $row['username'] ?? $username,
                'fullName' => $row['full_name'] ?? $fullName,
                'email' => $row['username'] ?? $username,
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
        // Optional: allow changing username/email
        $newUsername = strtolower(trim($in['username'] ?? ''));

        if ($isActive === 0 && $id === auth_user_id()) {
            fail('You cannot deactivate your own account.');
        }

        $sets = ['full_name = ?', 'updated_at = SYSUTCDATETIME()'];
        $params = [$fullName];

        if ($newUsername !== '') {
            if (!filter_var($newUsername, FILTER_VALIDATE_EMAIL)) {
                fail('Username must be a valid email address.');
            }
            $dup = $pdo->prepare('SELECT id FROM dbo.toner_users WHERE username = ? AND id <> ?');
            $dup->execute([$newUsername, $id]);
            if ($dup->fetch()) fail('This email is already registered.', 409);
            $sets[] = 'username = ?';
            $params[] = $newUsername;
            $sets[] = 'email = ?';
            $params[] = $newUsername;
        }

        if ($password !== '') {
            if (strlen($password) < 6) fail('Password must be at least 6 characters.');
            $sets[] = 'password_hash = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        if ($isActive !== null) {
            $sets[] = 'is_active = ?';
            $params[] = $isActive;
        }
        $params[] = $id;

        $sql = 'UPDATE dbo.toner_users SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $pdo->prepare($sql)->execute($params);
        activity_log('edit_user', 'Updated admin user', ['details' => 'User id ' . $id]);
        ok(['message' => 'User updated', 'id' => $id]);
    }

    if ($method === 'DELETE') {
        $in = json_input();
        $id = (int)($in['id'] ?? ($_GET['id'] ?? 0));
        if ($id <= 0) fail('User id is required.');
        if ($id === auth_user_id()) {
            fail('You cannot delete your own account.');
        }

        $cntRow = $pdo->query('SELECT COUNT(*) AS c FROM dbo.toner_users WHERE is_active = 1')->fetch(PDO::FETCH_ASSOC);
        $cntRow = array_change_key_case($cntRow ?: [], CASE_LOWER);
        $cnt = (int)($cntRow['c'] ?? 0);
        $target = $pdo->prepare('SELECT is_active FROM dbo.toner_users WHERE id = ?');
        $target->execute([$id]);
        $t = $target->fetch(PDO::FETCH_ASSOC);
        if (!$t) fail('User not found.', 404);
        $t = array_change_key_case($t, CASE_LOWER);
        if (!empty($t['is_active']) && $cnt <= 1) {
            fail('Cannot delete the last active admin.');
        }

        $pdo->prepare('DELETE FROM dbo.toner_users WHERE id = ?')->execute([$id]);
        activity_log('delete_user', 'Deleted admin user', ['details' => 'User id ' . $id]);
        ok(['deleted' => $id]);
    }

    // logging is done on successful mutations above when present
    fail('Method not allowed', 405);
} catch (Throwable $e) {
    fail('Server error: ' . $e->getMessage(), 500);
}
