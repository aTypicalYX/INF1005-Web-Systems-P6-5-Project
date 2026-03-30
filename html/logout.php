<?php
session_start();

// Reject any request that isn't a POST with a valid CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    header('Location: index.php');
    exit;
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: index.php');
exit;