<?php
session_start();

function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Forgot Password - Singapore Singles Society">
    <title>Forgot Password - Singapore Singles Society: S³</title>
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
                        <h2 class="text-center mb-2 fw-bold" style="color: var(--text-dark);">Forgot Password 🔑</h2>
                        <p class="text-center text-muted mb-4">Enter your SIT email and we'll send you a reset link. <br><strong class="text-danger" style="font-size: 0.85rem;">Note: For security, you must open the link in this exact same browser.</strong></p>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger rounded-4" role="alert"><?= h($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success !== ''): ?>
                            <div class="alert alert-success rounded-4" role="alert"><?= h($success) ?></div>
                        <?php endif; ?>

                        <form action="process_forgot_password.php" method="POST" novalidate>
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold text-muted">Email address</label>
                                <input
                                    type="email"
                                    class="form-control form-control-lg rounded-3 bg-light border-0"
                                    id="email"
                                    name="email"
                                    placeholder="you@sit.singaporetech.edu.sg"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn-solid-custom w-100 d-block text-center" style="font-size: 1.1rem; padding: 0.8rem;">
                                Send Reset Link
                            </button>
                        </form>

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