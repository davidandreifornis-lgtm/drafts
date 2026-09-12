<?php

function auth_start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function auth_config(): array {
    return require __DIR__ . '/auth.php';
}

function auth_check_credentials(string $user, string $pass): bool {
    $cfg = auth_config();
    if (!hash_equals((string)$cfg['username'], $user)) {
        return false;
    }
    $hash = trim((string)($cfg['password_hash'] ?? ''));
    if ($hash !== '') {
        return password_verify($pass, $hash);
    }
    return hash_equals((string)$cfg['password'], $pass);
}

function auth_login(string $user): void {
    auth_start();
    session_regenerate_id(true);
    $_SESSION['toner_admin'] = true;
    $_SESSION['toner_user'] = $user;
    $_SESSION['toner_login_at'] = time();
}

function auth_logout(): void {
    auth_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function auth_is_logged_in(): bool {
    auth_start();
    return !empty($_SESSION['toner_admin']);
}

function auth_require_login(): void {
    if (!auth_is_logged_in()) {
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = rtrim($base, '/');
        // If called from /api/*, go up one level for login.php
        if (substr($base, -4) === '/api') {
            $base = dirname($base);
            if ($base === '\\' || $base === '.') $base = '';
            $base = rtrim(str_replace('\\', '/', $base), '/');
        }
        $login = ($base === '' ? '' : $base) . '/login.php';
        header('Location: ' . $login);
        exit;
    }
}

function auth_user(): string {
    auth_start();
    return (string)($_SESSION['toner_user'] ?? 'admin');
}