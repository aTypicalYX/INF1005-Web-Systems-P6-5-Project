<?php
/**
 * validate_step.php — AJAX endpoint for per-step signup validation
 * Called by signup.php JS before advancing each step.
 * Returns JSON: { valid: bool, errors: { field: message } }
 */
session_start();
header('Content-Type: application/json');

$dbPath = file_exists(__DIR__ . '/../config/db.php')
    ? __DIR__ . '/../config/db.php'
    : dirname(__DIR__) . '/config/db.php';

if (file_exists($dbPath)) require_once $dbPath;

$step   = (int)($_POST['step'] ?? -1);
$errors = [];

// ── Step 0: Account Basics ──
if ($step === 0) {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName']  ?? '');
    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $confirm   = $_POST['confirmPassword']?? '';

    // First / last name
    if ($firstName === '') $errors['firstName'] = 'First name is required.';
    if ($lastName  === '') $errors['lastName']  = 'Last name is required.';

    // Username format
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors['username'] = 'Username must be 3–20 characters: letters, numbers, underscores only.';
    } else {
        // Username uniqueness
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            if ($stmt->fetch()) $errors['username'] = 'That username is already taken.';
        } catch (Exception $e) {}
    }

    // Email format + domain
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (substr(strrchr($email, '@'), 1) !== 'sit.singaporetech.edu.sg') {
        $errors['email'] = 'Only SIT email addresses (@sit.singaporetech.edu.sg) are allowed.';
    } else {
        // Email uniqueness
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) $errors['email'] = 'An account with that email already exists.';
        } catch (Exception $e) {}
    }

    // Password
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors['confirmPassword'] = 'Passwords do not match.';
    }
}


echo json_encode([
    'valid'  => empty($errors),
    'errors' => $errors,
]);