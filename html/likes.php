<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

$activePage  = 'likes';
$pageTitle   = 'Your Likes';
$currentUser = (int) $_SESSION['user_id'];
$likes       = [];

require_once '/var/www/config/db.php';

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

// Fetch everyone the current user has swiped right on
try {
    $stmt = $pdo->prepare("
        SELECT
            u.id               AS user_id,
            p.display_name     AS name,
            p.age,
            p.location,
            p.occupation,
            p.main_image       AS profile_pic,
            s.created_at       AS liked_at,
            CASE WHEN s2.id IS NOT NULL THEN 1 ELSE 0 END AS is_match
        FROM swipes s
        JOIN users u    ON u.id = s.swiped_id
        JOIN profile p  ON p.user_id = u.id
        LEFT JOIN swipes s2 ON s2.swiper_id = s.swiped_id
                        AND s2.swiped_id = s.swiper_id
                        AND s2.direction = 'like'
        WHERE s.swiper_id = ?
        AND s.direction = 'like'
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$currentUser]);
    $likes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $likes = [];
}

require_once 'includes/header.php';
?>

<main class="likes-main" role="main" aria-label="Your likes">

    <div class="likes-header">
        <p class="likes-label" aria-hidden="true">✦ Likes</p>
        <h1 class="likes-title">People You've Liked</h1>
        <p class="likes-subtitle">
            <?php if (!empty($likes)): ?>
                <?= count($likes) ?> like<?= count($likes) !== 1 ? 's' : '' ?> sent
                <?php $matchCount = count(array_filter($likes, fn($l) => $l['is_match'])); ?>
                <?php if ($matchCount > 0): ?>
                    &nbsp;·&nbsp; <?= $matchCount ?> mutual match<?= $matchCount !== 1 ? 'es' : '' ?> 💚
                <?php endif; ?>
            <?php else: ?>
                Start swiping to see your likes here
            <?php endif; ?>
        </p>
    </div>

    <?php if (empty($likes)): ?>

        <div class="likes-empty" role="status">
            <div class="likes-empty-icon" aria-hidden="true">🤍</div>
            <h2 class="likes-empty-heading">No likes yet</h2>
            <p class="likes-empty-text">
                Profiles you swipe right on will show up here.
            </p>
            <a href="profiles.php" class="likes-cta">Start Discovering →</a>
        </div>

    <?php else: ?>

        <div class="likes-grid" aria-label="Liked profiles">
            <?php foreach ($likes as $like): ?>
            <?php
                $pic = $like['profile_pic'] ?? null;
                if ($pic) {
                    $imgUrl = str_starts_with($pic, 'http') ? $pic : '/images/' . $pic;
                } else {
                    $imgUrl = 'https://ui-avatars.com/api/?name=' . urlencode($like['name'] ?? '?') . '&background=4A1060&color=F5E6FF&size=400';
                }
            ?>
            <a href="profile.php?id=<?= h((string)$like['user_id']) ?>&from=chat"
               class="like-card <?= $like['is_match'] ? 'like-card--match' : '' ?>"
               aria-label="View <?= h($like['name']) ?>'s profile">

                <div class="like-card-photo">
                    <img src="<?= h($imgUrl) ?>"
                         alt="Photo of <?= h($like['name']) ?>"
                         class="like-card-img"
                         loading="lazy">
                    <?php if ($like['is_match']): ?>
                        <div class="like-card-badge like-card-badge--match" aria-label="Mutual match">💚</div>
                    <?php else: ?>
                        <div class="like-card-badge" aria-label="Liked">🤍</div>
                    <?php endif; ?>
                </div>

                <div class="like-card-info">
                    <p class="like-card-name">
                        <?= h($like['name']) ?>
                        <?php if (!empty($like['age'])): ?>
                            <span class="like-card-age"><?= h((string)$like['age']) ?></span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($like['location'])): ?>
                    <p class="like-card-location">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= h($like['location']) ?>
                    </p>
                    <?php endif; ?>
                    <p class="like-card-time">
                        <?= $like['is_match'] ? 'Matched' : 'Liked' ?> <?= h(date('d M Y', strtotime($like['liked_at']))) ?>
                    </p>
                </div>

            </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>