<?php
/**
 * Admin credentials — change before production.
 * Default login: admin / admin123
 */
return [
    'username' => getenv('ADMIN_USER') ?: 'admin',
    'password' => getenv('ADMIN_PASS') ?: 'admin123',
    // If set, used instead of plain 'password' (password_hash)
    'password_hash' => getenv('ADMIN_PASS_HASH') ?: '',
];