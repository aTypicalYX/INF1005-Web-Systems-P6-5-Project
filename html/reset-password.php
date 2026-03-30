<?php
/**
 * reset-password.php
 *
 * Validates the reset token (stored in session) and lets the user set a new password.
 * Updates users.password_hash — no new DB columns needed.
 */
session_start();

// NEW: 1. Generate CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

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

// ── Validate token from URL ───────────────────────────────────────────────────
$token      = trim((string) ($_GET['token'] ?? ''));
$resetData  = $_SESSION['password_reset'][$token] ?? null;
$tokenValid = false;

if (
    $token !== '' &&
    $resetData !== null &&
    $resetData['used'] === false &&
    time() < $resetData['expires_at']
) {
    $tokenValid = true;
}

// ── Handle POST (new password submission) ─────────────────────────────────────
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    
    // NEW: 2. Validate the CSRF token before processing the password change
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        $newPassword     = (string) ($_POST['new_password']     ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $dbPath = resolveDbConfigPath();
            if ($dbPath === null) {
                $error = 'System error — please try again later.';
            } else {
                require $dbPath;

                if (!isset($pdo) || !($pdo instanceof PDO)) {
                    $error = 'System error — please try again later.';
                } else {
                    try {
                        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                        $stmt   = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
                        $stmt->execute([$hashed, $resetData['user_id']]);

                        // Mark token as used so it cannot be replayed
                        $_SESSION['password_reset'][$token]['used'] = true;

                        $success    = 'Your password has been reset! You can now log in.';
                        $tokenValid = false; // Hide the form
                    } catch (PDOException $e) {
                        $error = 'Database error — please try again later.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset Password - Singapore Singles Society">
    <title>Reset Password - Singapore Singles Society: S³</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container" aria-label="Main navigation">
        <nav class="custom-navbar">
            <a href="index.php" class="brand-logo" aria-label="Singapore Singles Society home">S³</a>
            <div class="nav-center">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="#">Pricing</a>
            </div>
            <div class="nav-right">
                <a href="login.php" class="btn-outline-custom active">Log In</a>
                <a href="signup.php" class="btn-solid-custom">Join Now &rarr;</a>
            </div>
        </nav>
    </div>

    <main class="container mt-5 mb-5" style="flex: 1;">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-2 fw-bold" style="color: var(--text-dark);">Reset Password 🔒</h2>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger rounded-4" role="alert"><?= h($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success !== ''): ?>
                            <div class="alert alert-success rounded-4" role="alert"><?= h($success) ?></div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn-solid-custom d-inline-block px-5 py-2">
                                    Go to Login
                                </a>
                            </div>

                        <?php elseif (!$tokenValid): ?>
                            <div class="alert alert-warning rounded-4" role="alert">
                                This reset link is invalid or has expired. Please request a new one.
                            </div>
                            <div class="text-center mt-3">
                                <a href="forgot-password.php" style="color: var(--primary-pink); font-weight: 700; text-decoration: none;">
                                    Request a new link
                                </a>
                            </div>

                        <?php else: ?>
                            <p class="text-center text-muted mb-4">Choose a strong new password for your account.</p>

                            <form action="reset-password.php?token=<?= urlencode($token) ?>" method="POST" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted">New Password</label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        class="form-control form-control-lg rounded-3 bg-light border-0"
                                        minlength="8"
                                        placeholder="Min. 8 characters"
                                        required
                                    >
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-muted">Confirm New Password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        class="form-control form-control-lg rounded-3 bg-light border-0"
                                        minlength="8"
                                        placeholder="Repeat password"
                                        required
                                    >
                                </div>
                                <button type="submit" class="btn-solid-custom w-100 d-block text-center" style="font-size: 1.1rem; padding: 0.8rem;">
                                    Reset Password
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="login.php" style="color: var(--primary-pink); font-weight: 700; text-decoration: none;">
                                &larr; Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="custom-footer mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <a href="index.php" class="brand-logo d-inline-block mb-3">S³</a>
                    <p class="text-muted pe-md-5">A safe, fun space to meet people who share your vibe. Based in Singapore.</p>
                </div>
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5 class="footer-heading">Explore</h5>
                    <a href="#" class="footer-link">Browse Profiles</a>
                    <a href="#" class="footer-link">Pricing</a>
                    <a href="login.php" class="footer-link">Sign In</a>
                </div>
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5 class="footer-heading">Company</h5>
                    <a href="about.php" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Blog</a>
                    <a href="#" class="footer-link">Careers</a>
                </div>
                <div class="col-md-3 col-12">
                    <h5 class="footer-heading">Support</h5>
                    <a href="#" class="footer-link">Safety Centre</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="text-center text-muted" style="font-size: 0.9rem;">
                <p class="mb-0">&copy; 2026 Singapore Singles Society S³. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>