<?php
/**
 * process_forgot_password.php
 *
 * Generates a secure reset token stored in PHP session (no new DB table needed).
 * Sends the reset link via PHPMailer + Gmail SMTP.
 */
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Load PHPMailer ────────────────────────────────────────────────────────────
require '/var/www/vendor/autoload.php';

// ── Load mail credentials ─────────────────────────────────────────────────────
// ⚠️ Make sure /var/www/config/mail.php exists (see setup instructions)
$mailConfig = require '/var/www/config/mail.php';

// ── Helper ────────────────────────────────────────────────────────────────────
function resolveDbConfigPath(): ?string
{
    foreach ([
        __DIR__ . '/../config/db.php',
        dirname(__DIR__) . '/config/db.php',
        '/var/www/config/db.php',
    ] as $path) {
        if (is_file($path)) return $path;
    }
    return null;
}

// ── Input validation ──────────────────────────────────────────────────────────
$email = trim((string) ($_POST['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: forgot-password.php');
    exit;
}

// ── Look up user in DB ────────────────────────────────────────────────────────
$dbPath = resolveDbConfigPath();
if ($dbPath === null) {
    $_SESSION['error'] = 'System error — please try again later.';
    header('Location: forgot-password.php');
    exit;
}
require $dbPath;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    $_SESSION['error'] = 'System error — please try again later.';
    header('Location: forgot-password.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, first_name FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $_SESSION['error'] = 'System error — please try again later.';
    header('Location: forgot-password.php');
    exit;
}

// Always show the same message to prevent email enumeration attacks
$_SESSION['success'] = 'If that email is registered, a reset link has been sent. Check your inbox.';

if (!$user) {
    header('Location: forgot-password.php');
    exit;
}

// ── Generate secure token (stored in session — no new DB table needed) ────────
$token     = bin2hex(random_bytes(32)); // 64-char cryptographically secure token
$expiresAt = time() + 3600;            // expires in 1 hour

$_SESSION['password_reset'][$token] = [
    'user_id'    => $user['id'],
    'email'      => $email,
    'expires_at' => $expiresAt,
    'used'       => false,
];

// ── Build reset URL ───────────────────────────────────────────────────────────
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir      = dirname($_SERVER['SCRIPT_NAME']);
$resetUrl = $scheme . '://' . $host . rtrim($dir, '/') . '/reset-password.php?token=' . urlencode($token);

// ── Send email via PHPMailer + Gmail SMTP ─────────────────────────────────────
$firstName = $user['first_name'];

$emailBody = "Hi {$firstName},\n\n"
           . "We received a request to reset your S3 account password.\n\n"
           . "Click the link below to choose a new password (valid for 1 hour):\n\n"
           . $resetUrl . "\n\n"
           . "If you didn't request this, you can safely ignore this email.\n\n"
           . "— The S3 Team";

try {
    $mail = new PHPMailer(true);

    // SMTP config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Email content
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($mailConfig['username'], $mailConfig['from_name']);
    $mail->addAddress($email, $firstName);
    $mail->Subject = 'S3 - Password Reset Request';
    $mail->Body    = $emailBody;

    $mail->send();

} catch (Exception $e) {
    // Log the error but don't expose it to the user
    error_log('PHPMailer error (forgot password): ' . $e->getMessage());
}

header('Location: forgot-password.php');
exit;