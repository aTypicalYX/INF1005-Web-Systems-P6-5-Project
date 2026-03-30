<?php

// Harden the session cookie before session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);


session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$activePage = 'profile';
$pageTitle  = 'Edit Password';

require_once '/var/www/config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error   = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // CSRF Verification
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    if ($submittedToken === '' || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: edit-password.php');
        exit();
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    try {
        // 1. Fetch current password hash
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        // 2. Verify current password
        if (!password_verify($current, $user['password_hash'])) {
            throw new Exception("Current password is incorrect.");
        }

        // 3. Validate new password
        if (strlen($new) < 8) {
            throw new Exception("New password must be at least 8 characters.");
        }

        if ($new !== $confirm) {
            throw new Exception("New passwords do not match.");
        }

        if ($current === $new) {
            throw new Exception("New password must be different from your current password.");
        }

        // 4. Update password
        $hashed = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashed, $_SESSION['user_id']]);

        $_SESSION['success'] = "Password updated successfully!";
        header("Location: edit-password.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit-password.php");
        exit();
    }
}

require_once 'includes/header.php';
?>

<main class="container my-5" style="max-width: 480px;">
    <div class="card border-0 shadow-sm" style="border-radius: 24px;">
        <div class="card-body p-4 p-md-5">

            <h2 class="fw-bold mb-1" style="color: var(--text-dark);">Edit Password 🔒</h2>
            <p class="text-muted mb-4">Choose a strong password to keep your account secure.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-4"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success rounded-4"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="edit-password.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                >
                <div class="mb-3">
                     <label for="current_password" class="form-label text-muted fw-bold">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control form-control-lg custom-input" autocomplete="current-password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label text-muted fw-bold">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control form-control-lg custom-input" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="form-label text-muted fw-bold">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg custom-input" autocomplete="new-password" minlength="8" required>
                </div>
                <button type="submit" class="btn-solid-custom w-100 py-3">Update Password</button>
            </form>

        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>