<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ensure only reachable after a successful submission
if (empty($_SESSION['report_success'])) {
    header('Location: profiles.php');
    exit();
}
unset($_SESSION['report_success']);

require_once __DIR__ . '/../config/db.php';

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

// Optionally fetch the reported user's name for a personalised message
$reportedId   = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$reportedName = null;
if ($reportedId > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT COALESCE(NULLIF(p.display_name, ''), u.first_name) AS name
            FROM users u
            JOIN profile p ON p.user_id = u.id
            WHERE u.id = ? LIMIT 1
        ");
        $stmt->execute([$reportedId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $reportedName = $row['name'] ?? null;
    } catch (Exception $e) {
        // non-critical
    }
}

$activePage = 'discover';
$pageTitle  = 'Report Submitted';
require_once 'includes/header.php';
?>

<div class="success-wrap" role="main">
    <h1 class="success-heading">Report submitted</h1>
    <p class="success-subtext">
        <?php if ($reportedName): ?>
            Thanks for letting us know about <strong><?= h($reportedName) ?></strong>.
        <?php else: ?>
            Thanks for letting us know.
        <?php endif; ?>
        Our moderation team will review the report and take appropriate action.
        All reports are kept confidential.
    </p>

    <div class="success-actions">
        <a href="profiles.php" class="success-btn-primary">Keep Discovering</a>
        <?php if ($reportedId > 0): ?>
            <a href="profile.php?id=<?= h((string)$reportedId) ?>" class="success-btn-secondary">
                Back to profile
            </a>
        <?php endif; ?>
    </div>

    <p class="success-note">
        If you feel you're in immediate danger, please contact local emergency services.
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>