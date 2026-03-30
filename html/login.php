<?php
session_start();

$appConfig = require '/var/www/config/app.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$old = $_SESSION['old_login'] ?? ['email' => ''];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login page for Singapore Singles Society">
    <meta name="author" content="Singapore Singles Society — INF1005">
    <title>Login - Singapore Singles Society: S³</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <!-- Google reCAPTCHA v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
                        <h2 class="text-center mb-4 fw-bold" style="color: var(--text-dark);">Welcome Back 👋</h2>

                        <?php if ($error !== ''): ?>
                            <div class="d-flex justify-content-center mb-4 position-relative" id="errorAlert">
                                <div class="custom-alert-pill error-pill" role="alert">
                                    <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                                    <span class="d-flex align-items-center mt-1"><?= h($error) ?></span>
                                    <i class="bi bi-x ms-3 fs-4 alert-close-btn" onclick="this.closest('#errorAlert').remove();" title="Close"></i>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($success !== ''): ?>
                            <div class="d-flex justify-content-center mb-4 position-relative" id="successAlert">
                                <div class="custom-alert-pill success-pill" role="alert">
                                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                    <span class="d-flex align-items-center mt-1"><?= h($success) ?></span>
                                    <i class="bi bi-x ms-3 fs-4 alert-close-btn" onclick="this.closest('#successAlert').remove();" title="Close"></i>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form id="loginForm" action="process_login.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold text-muted">Email address or username</label>
                                <input type="text" class="form-control form-control-lg rounded-3 bg-light border-0" id="email" name="email" value="<?= h((string) $old['email']) ?>" placeholder="you@email.com or username" required>
                            </div>
                            <div class="mb-2">
                                <label for="password" class="form-label fw-bold text-muted">Password</label>
                                <input type="password" class="form-control form-control-lg rounded-3 bg-light border-0" id="password" name="password" placeholder="••••••••" required>
                            </div>

                            <!-- Forgot Password Link -->
                            <div class="text-end mb-3">
                                <a href="forgot-password.php" style="color: var(--primary-pink); font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                                    Forgot password?
                                </a>
                            </div>

                            <!-- Google reCAPTCHA v2 Widget -->
                            <!-- ⚠️ Replace YOUR_SITE_KEY with your actual reCAPTCHA Site Key -->
                            <div class="mb-4 d-flex justify-content-center">
                                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($appConfig['recaptcha_site_key'], ENT_QUOTES, 'UTF-8') ?>"></div>
                            </div>

                            <button type="submit" class="btn-solid-custom w-100 d-block text-center mb-3" style="font-size: 1.1rem; padding: 0.8rem;">Login to S³</button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">New to S³?</span>
                            <a href="signup.php" style="color: var(--primary-pink); font-weight: 700; text-decoration: none;">Create an account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>

<?php require_once 'includes/footer.php'; ?>

</html>