
<?php
require_once __DIR__ . '/config/auth_lib.php';
auth_logout();
header('Location: login.php');
exit;