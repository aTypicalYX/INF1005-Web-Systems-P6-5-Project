<?php
// ── header.inc.php ──
// Usage: include this at the top of every page.
//
// To highlight the correct nav link, define $activePage before including:
//   $activePage = 'discover';   // 'home' | 'discover' | 'matches' | 'messages' | 'profile' | 'about' | 'pricing'
//
// Example:
//   <?php $activePage = 'discover'; require_once 'includes/header.inc.php'; 

// Generate a secure CSRF token if one doesn't exist for the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($activePage)) $activePage = '';

// Helper — echo HTML-escaped string
if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

$loggedIn = isset($_SESSION['user_id']);

// Check if logged in user is inside the banned table
if ($loggedIn && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM bans WHERE user_id = ? LIMIT 1");
        $stmt->execute([(int)$_SESSION["user_id"]]);
        if ($stmt->fetch()) {
            $_SESSION['is_banned'] = true;
            header("Location: banned.php");
            exit();
        }
    } catch (Exception $e) {
        // Fail open
    }
}

// Helper — returns 'active' + aria-current if page matches
function navClass(string $page, string $active): string {
    if ($page === $active) {
        return ' class="active" aria-current="page"';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' – Singapore Singles Society' : 'Singapore Singles Society' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">    <script src="https://kit.fontawesome.com/585cd70d2d.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css?v=<?= time(); ?>">
    <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>

<div class="navbar-wrapper">
    <div class="container">
        
        <nav class="custom-navbar navbar navbar-expand-lg">

            <a href="index.php" class="brand-logo" aria-label="Singapore Singles Society home">S³</a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="mobileMenu">
                
                <div class="nav-center mx-lg-auto my-3 my-lg-0">
                    <a href="index.php"<?= navClass('home', $activePage) ?>>Home</a>

                    <?php if ($loggedIn): ?>
                        <a href="profiles.php"<?= navClass('discover', $activePage) ?>>Discover</a>
                        <a href="likes.php"<?= navClass('likes',  $activePage) ?>>Likes</a>
                        <a href="messages.php"<?= navClass('messages', $activePage) ?>>Messages</a>
                    <?php endif; ?>

                    <a href="about.php"<?= navClass('about', $activePage) ?>>About</a>
                </div>

                <div class="nav-right">
                    <?php if ($loggedIn): ?>
                        <a href="edit-profile.php"
                           class="btn-outline-custom<?= $activePage === 'profile' ? ' active' : '' ?> d-none d-lg-block"
                           aria-label="My profile">
                            My Profile
                        </a>

                        <div class="dropdown w-100">
                            <a class="btn-solid-custom dropdown-toggle d-block d-lg-inline-block text-center"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false"
                               aria-label="Account menu for <?= h($_SESSION['username'] ?? 'User') ?>">
                                <?= h($_SESSION['username'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end mt-2 mobile-dropdown">
                                <li>
                                    <a class="dropdown-item" href="edit-profile.php">
                                        <span aria-hidden="true"><i class="fa-solid fa-address-book" style="color: var(--primary-pink);"></i></span> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="matches.php">
                                        <span aria-hidden="true"><i class="fa-solid fa-heart" style="color: var(--primary-pink);"></i></span> My Matches
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="messages.php">
                                        <span aria-hidden="true"><i class="fa-solid fa-comment" style="color: var(--primary-pink);"></i></span> Messages
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="edit-password.php">
                                        <span aria-hidden="true"><i class="fa-solid fa-lock" style="color: var(--primary-pink);"></i></span> Edit Password
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>

                                
                                <?php if ($_SESSION['role'] == "admin"): ?>
                                <li>
                                    <a class="dropdown-item" href="reports.php">
                                        <span aria-hidden="true"><i class="fa-solid fa-lock" style="color: var(--primary-pink);"></i></span> View Reports
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                
                                <?php endif; ?>
                                <li>
                                    <form method="POST" action="logout.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start">
                                            <span aria-hidden="true"><i class="fa-solid fa-arrow-right" style="color: var(--primary-pink);"></i></span> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <a href="login.php" class="btn-outline-custom text-center w-100 w-lg-auto mb-2 mb-lg-0">Log In</a>
                        <a href="signup.php" class="btn-solid-custom text-center w-100 w-lg-auto">Join Now &rarr;</a>
                    <?php endif; ?>
                </div>

            </div>
        </nav>
    </div>
</div>