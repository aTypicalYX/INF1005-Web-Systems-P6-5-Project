<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

$activePage  = 'messages';
$pageTitle   = 'Your Messages';
$currentUser = (int) $_SESSION['user_id'];
$matches     = [];

require_once '/var/www/config/db.php';

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch all matches for the current user
// matches table stores user_1_id (lower) and user_2_id (higher)
// so we check both columns
try {
    $stmt = $pdo->prepare("
        SELECT
            p.display_name  AS name,
            p.age,
            p.location,
            p.occupation,
            p.main_image    AS profile_pic,
            m.created_at    AS matched_at,
            -- Get the OTHER user's id regardless of which column we're in
            CASE
                WHEN m.user_1_id = ? THEN m.user_2_id
                ELSE m.user_1_id
            END AS matched_user_id
        FROM matches m
        JOIN users u ON u.id = CASE
            WHEN m.user_1_id = ? THEN m.user_2_id
            ELSE m.user_1_id
        END
        JOIN profile p ON p.user_id = u.id
        WHERE m.user_1_id = ? OR m.user_2_id = ?
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$currentUser, $currentUser, $currentUser, $currentUser]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $matches = [];
}

require_once 'includes/header.php';
?>

<main class="matches-main" role="main" aria-label="Your matches">

    <div class="matches-header">
        <p class="matches-label" aria-hidden="true">✦ Messages</p>
        <h1 class="matches-title">Your Sparks</h1>
        <p class="matches-subtitle">
            <?php if (!empty($matches)): ?>
                <?= count($matches) ?> mutual match<?= count($matches) !== 1 ? 'es' : '' ?> so far
            <?php else: ?>
                Keep swiping to find your spark
            <?php endif; ?>
        </p>
    </div>

    <?php if (empty($matches)): ?>

        <!-- Empty state -->
        <div class="matches-empty" role="status">
            <div class="matches-empty-icon" aria-hidden="true">💔</div>
            <h2 class="matches-empty-heading">No matches yet</h2>
            <p class="matches-empty-text">
                When someone likes you back, they'll show up here.
                Go discover more people and chat with them here!
            </p>
            <a href="profiles.php" class="matches-cta">
                Start Discovering →
            </a>
        </div>

    <?php else: ?>

        <div class="matches-grid" aria-label="Match cards">
            <?php foreach ($matches as $match): ?>
            <?php
                $pic = $match['profile_pic'] ?? null;
                if ($pic) {
                    $imgUrl = str_starts_with($pic, 'http') ? $pic : '/images/' . $pic;
                } else {
                    $name   = $match['name'] ?? '?';
                    $imgUrl = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4A1060&color=F5E6FF&size=400';
                }
            ?>
            <a href="profile.php?id=<?= h((string)$match['matched_user_id']) ?>"
               class="match-card"
               aria-label="View <?= h($match['name']) ?>'s profile">

                <!-- Photo -->
                <div class="match-card-photo">
                    <img src="<?= h($imgUrl) ?>"
                         alt="Photo of <?= h($match['name']) ?>"
                         class="match-card-img"
                         loading="lazy">
                    <div class="match-card-badge" aria-hidden="true">💚</div>
                </div>

                <!-- Info -->
                <div class="match-card-info">
                    <p class="match-card-name">
                        <?= h($match['name']) ?>
                        <?php if (!empty($match['age'])): ?>
                            <span class="match-card-age"><?= h((string)$match['age']) ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($match['location'])): ?>
                    <p class="match-card-location">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= h($match['location']) ?>
                    </p>
                    <?php endif; ?>
                    <p class="match-card-time">
                        Matched <?= h(date('d M Y', strtotime($match['matched_at']))) ?>
                    </p>
                </div>

            </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>