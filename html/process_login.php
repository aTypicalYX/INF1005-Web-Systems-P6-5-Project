<?php
session_start();

$errorMsg = '';
$success  = true;

$loginId  = trim((string) ($_POST['email']    ?? ''));
$password = (string)       ($_POST['password'] ?? '');

$_SESSION['old_login'] = ['email' => $loginId];

$appConfig = require '/var/www/config/app.php';

// 1. reCAPTCHA verification
$recaptchaSecret   = $appConfig['recaptcha_secret_key'];
$recaptchaResponse = trim((string) ($_POST['g-recaptcha-response'] ?? ''));

if ($recaptchaResponse === '') {
    $errorMsg = 'Please complete the reCAPTCHA check.';
    $success  = false;
}

if ($success) {
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $verifyData = http_build_query([
        'secret'   => $recaptchaSecret,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $verifyData,
            'timeout' => 5,
        ],
    ]);

    $verifyResult = @file_get_contents($verifyUrl, false, $context);
    $verifyJson   = $verifyResult ? json_decode($verifyResult, true) : null;

    if (!$verifyJson || $verifyJson['success'] !== true) {
        $errorMsg = 'reCAPTCHA verification failed. Please try again.';
        $success  = false;
    }
}

// 2. Basic input validation 

if ($success && $loginId === '') {
    $errorMsg = 'Please enter your email address or username.';
    $success  = false;
}

if ($success && $password === '') {
    $errorMsg = 'Password is required.';
    $success  = false;
}

// 3. Authenticate against database 

if ($success) {
    authenticateUser();
}

// 4. Redirect 

if ($success) {
    // Prevent Session Fixation by issuing a new session ID upon login
    session_regenerate_id(true);
    unset($_SESSION['old_login']);
    $_SESSION['user_id']     = $userId;
    $_SESSION['user_email']  = $email;
    $_SESSION['username']    = $username;
    $_SESSION['first_name']  = $firstName;
    $_SESSION['last_name']   = $lastName;
    $_SESSION['role']        = $role;
    $_SESSION['success']     = 'You are now logged in.';

    header('Location: index.php');
    exit;
}

$_SESSION['error'] = $errorMsg;
header('Location: login.php');
exit;

// Helper functions

function resolveDbConfigPath(): ?string
{
    $candidates = [
        __DIR__ . '/../config/db.php',
        dirname(__DIR__) . '/config/db.php',
        '/var/www/config/db.php',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

function loadPdo(string &$errorMsg): ?PDO
{
    $dbPath = resolveDbConfigPath();
    if ($dbPath === null) {
        $errorMsg = 'Database configuration file not found on server.';
        return null;
    }

    require $dbPath;

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $errorMsg = 'Database connection not initialized.';
        return null;
    }

    return $pdo;
}

function authenticateUser(): void
{
    global $firstName, $lastName, $email, $password, $username, $role, $errorMsg, $success, $loginId, $userId;

    $pdo = loadPdo($errorMsg);
    if ($pdo === null) {
        $success = false;
        return;
    }

    try {
        $stmt = $pdo->prepare('SELECT id, first_name, last_name, username, email, password_hash, role FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->execute([$loginId, $loginId]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, $row['password_hash'])) {
            $errorMsg = 'Login credentials are invalid.';
            $success  = false;
            return;
        }

        $firstName = $row['first_name'];
        $lastName  = $row['last_name'];
        $username  = $row['username'];
        $email     = $row['email'];
        $role      = $row['role'];
        $userId    = $row['id'];

    } catch (PDOException $e) {
        $errorMsg = 'Database error: ' . $e->getMessage();
        $success  = false;
    }
}
?>