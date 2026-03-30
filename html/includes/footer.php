<?php
// ── footer.php ──
// Include at the bottom of every page, just before closing </body>
?>
    <footer class="app-footer py-4 mt-auto">
        <div class="container">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                
                <div class="text-center text-md-start">
                    <a href="index.php" class="brand-logo d-inline-block text-decoration-none mb-1 app-footer-brand">S³</a>
                    <p class="mb-0 app-footer-copy">
                        &copy; <?= date('Y') ?> Singapore Singles Society.<br class="d-block d-md-none"> A safe space for SITizens.
                    </p>
                </div>
                
                <div class="d-flex flex-wrap justify-content-center gap-3 gap-md-4">
                    <a href="index.php" class="text-decoration-none footer-nav-link">Home</a>
                    <a href="about.php" class="text-decoration-none footer-nav-link">About Us</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="text-decoration-none footer-nav-link">Sign In</a>
                        <a href="signup.php" class="text-decoration-none footer-nav-link">Join Now</a>
                    <?php else: ?>
                        <a href="swipe.php" class="text-decoration-none footer-nav-link">Discover</a>
                        <a href="profile.php" class="text-decoration-none footer-nav-link">My Profile</a>
                    <?php endif; ?>
                </div>
                
                <div class="d-flex gap-3">
                    <a href="#" class="footer-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://github.com/aTypicalYX/INF1005-Web-Systems-P6-5-Project" class="footer-icon" aria-label="GitHub Repository"><i class="bi bi-github"></i></a>
                    <a href="#" class="footer-icon" aria-label="Email Support"><i class="bi bi-envelope-fill"></i></a>
                </div>

            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>