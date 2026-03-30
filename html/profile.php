<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php'; // Ensure correct path to your DB config

if (!function_exists('h')) {
    function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

// ── Get profile ID from URL ──
$profileId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$fromChat = isset($_GET['from']) && $_GET['from'] === 'chat';

if ($profileId === 0) {
    header('Location: profiles.php');
    exit();
}

// ── Fetch Extended Profile Data ──
$profile = null;
try {
    $stmt = $pdo->prepare("
        SELECT u.id,
                p.display_name  AS name,
                p.age,
                p.gender,
                p.pronouns,
                p.location,
                p.occupation,
                p.height,
                p.education,
                p.love_language,
                p.pets,
                p.workout,
                p.social_media,
                p.favourite_song,
                p.biography     AS bio,
                p.main_image    AS image_1,
                p.image_2,
                p.image_3,
                p.image_4,
                p.image_5,
                p.image_6,
                (SELECT GROUP_CONCAT(i.name ORDER BY i.name SEPARATOR ',')
                    FROM user_interests ui
                    JOIN interests i ON i.id = ui.interest_id
                    WHERE ui.user_id = u.id) AS interests,
                pref.looking_for  /* <-- ADDED THIS */
        FROM users u
        JOIN profile p ON p.user_id = u.id
        LEFT JOIN preferences pref ON pref.user_id = u.id /* <-- ADDED THIS JOIN */
        WHERE u.id = ?
    ");
    $stmt->execute([$profileId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Error handling
}

if (!$profile) {
    $pageTitle = "Profile Not Found";
    require_once 'includes/header.php';
    echo "
    <div class='container text-center' style='margin-top: 15vh; min-height: 50vh;'>
        <div class='mb-4' style='font-size: 4rem; color: #ccc;'><i class='bi bi-person-x-fill'></i></div>
        <h2 class='fw-bold' style='color: var(--text-dark);'>Profile not found</h2>
        <p class='text-muted mb-4'>This user may have deleted their account or you followed a broken link.</p>
        <a href='profiles.php' class='btn-solid-custom px-4 py-2 text-decoration-none'>Back to Swiping</a>
    </div>";
    require_once 'includes/footer.php';
    exit();
}

// ── Ban check — non-admins cannot view banned profiles ──
if (($_SESSION['role'] ?? '') !== 'admin') {
    try {
        $banStmt = $pdo->prepare("SELECT id FROM bans WHERE user_id = ? LIMIT 1");
        $banStmt->execute([$profileId]);
        if ($banStmt->fetch()) {
            header('Location: profiles.php');
            exit();
        }
    } catch (Exception $e) {
        // Fail open — don't block on DB error
    }
}

// ── Fetch Answers ──
$answers = [];
try {
    $stmt = $pdo->prepare("
        SELECT q.q_text, a.ans_text 
        FROM Answers a
        JOIN questions q ON a.qn_id = q.qn_id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$profileId]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

// Gather secondary images
$images = [];
foreach (['image_2', 'image_3', 'image_4', 'image_5', 'image_6'] as $col) {
    if (!empty($profile[$col])) {
        $images[] = $profile[$col];
    }
}

// Create the interleaved sequence of Prompts and Images
$displayItems = [];
$maxLength = max(count($images), count($answers));
for ($i = 0; $i < $maxLength; $i++) {
    if (isset($answers[$i])) {
        $displayItems[] = ['type' => 'prompt', 'data' => $answers[$i], 'idx' => $i];
    }
    if (isset($images[$i])) {
        $displayItems[] = ['type' => 'image', 'data' => $images[$i]];
    }
}

$pageTitle = h($profile['name']) . "'s Profile";
require_once 'includes/header.php';
?>

<main class="vp-main" style="background-color: #FAFAFA; min-height: 100vh;">

    <div class="vp-nav">
        <?php if ($fromChat): ?>
            <a href="chat.php?id=<?= $profileId ?>" class="vp-back-btn" aria-label="Back to chat">
                <i class="bi bi-chevron-left"></i>
            </a>
        <?php else: ?>
            <a href="profiles.php" class="vp-back-btn" aria-label="Back to swipe">
                <i class="bi bi-chevron-left"></i>
            </a>
        <?php endif; ?>
    </div>

    <div class="vp-wrap pb-5 mb-5 mt-3">

        <div class="vp-photo-card shadow-sm mb-4">
            <img src="<?= h(empty($profile['image_1']) ? 'images/Default.webp' : 'images/' . $profile['image_1']) ?>"
                alt="<?= h($profile['name']) ?>">
            <div class="vp-photo-overlay">
                <h1 class="vp-name">
                    <?= h($profile['name']) ?>
                    <?php if (!empty($profile['age'])): ?>
                        <span class="vp-age"><?= h((string)$profile['age']) ?></span>
                    <?php endif; ?>
                </h1>
                <?php if (!empty($profile['location'])): ?>
                    <p class="vp-location mb-0">
                        <i class="bi bi-geo-alt-fill"></i> <?= h($profile['location']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="vp-info-card">
            <h6 class="vp-section-title">The Vitals</h6>
            <div class="vp-vitals-grid mb-3">
                <?php if (!empty($profile['pronouns'])): ?>
                    <div class="vp-vital-pill"><i class="bi bi-person-badge"></i> <?= h($profile['pronouns']) ?></div>
                <?php endif; ?>
                <?php if (!empty($profile['height'])): ?>
                    <div class="vp-vital-pill"><i class="bi bi-rulers"></i> <?= h((string)$profile['height']) ?> cm</div>
                <?php endif; ?>
                <?php if (!empty($profile['occupation'])): ?>
                    <div class="vp-vital-pill"><i class="bi bi-briefcase-fill"></i> <?= h($profile['occupation']) ?></div>
                <?php endif; ?>
                <?php if (!empty($profile['education'])): ?>
                    <div class="vp-vital-pill"><i class="bi bi-mortarboard-fill"></i> <?= h($profile['education']) ?></div>
                <?php endif; ?>
                <?php if (!empty($profile['gender'])): ?>
                    <div class="vp-vital-pill"><i class="bi bi-gender-ambiguous"></i> <?= h($profile['gender']) ?></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($profile['interests'])): ?>
                <h6 class="vp-section-title mt-4">Interests</h6>
                <div class="vp-vitals-grid">
                    <?php
                    $tags = explode(',', $profile['interests']);
                    foreach ($tags as $tag):
                        if (trim($tag)):
                    ?>
                            <span class="vp-vital-pill" style="background: rgba(216, 27, 96, 0.05); color: var(--primary-pink); border-color: rgba(216, 27, 96, 0.1);">
                                <?= h(trim($tag)) ?>
                            </span>
                    <?php endif;
                    endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        $itemCount = 0;
        $bioShown = false;
        $musicShown = false;
        $intentShown = false;
        $loveShown = false;

        foreach ($displayItems as $item):
            $itemCount++;
        ?>

            <?php if ($itemCount === 1 && !$intentShown && !empty($profile['looking_for'])): $intentShown = true; ?>
                <div class="vp-intent-card">
                    <div class="intent-icon-wrapper"><i class="bi bi-search-heart"></i></div>
                    <div class="intent-info">
                        <span class="intent-label">I'm Looking For</span>
                        <span class="intent-value"><?= h($profile['looking_for']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($itemCount === 2 && !$bioShown): $bioShown = true; ?>
                <div class="vp-info-card">
                    <?php if (!empty($profile['bio'])): ?>
                        <h6 class="vp-section-title">About Me</h6>
                        <p class="mb-4" style="color: var(--text-dark); font-size: 1.05rem; line-height: 1.6;"><?= nl2br(h($profile['bio'])) ?></p>
                    <?php endif; ?>

                    <h6 class="vp-section-title">Lifestyle</h6>
                    <div class="vp-vitals-grid">
                        <?php if (!empty($profile['pets'])): ?>
                            <div class="vp-vital-pill"><i class="bi bi-balloon-heart-fill"></i> Pets: <?= h($profile['pets']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($profile['workout'])): ?>
                            <div class="vp-vital-pill"><i class="bi bi-activity"></i> Workout: <?= h($profile['workout']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($profile['social_media'])): ?>
                            <div class="vp-vital-pill"><i class="bi bi-phone-vibrate-fill"></i> Socials: <?= h($profile['social_media']) ?></div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($itemCount === 3 && !$loveShown && !empty($profile['love_language'])): $loveShown = true; ?>
                <div class="vp-love-card">
                    <div class="love-icon-wrapper"><i class="bi bi-chat-heart-fill"></i></div>
                    <div class="love-info">
                        <span class="love-label">My Love Language</span>
                        <span class="love-value"><?= h($profile['love_language']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($itemCount === 4 && !$musicShown && !empty($profile['favourite_song'])): $musicShown = true; ?>
                <div class="vp-music-card">
                    <div class="music-icon-wrapper"><i class="bi bi-music-note-beamed"></i></div>
                    <div class="music-info">
                        <span class="music-label">My Anthem</span>
                        <span class="music-track"><?= h($profile['favourite_song']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($item['type'] === 'prompt'): ?>
                <div class="vp-prompt-card shadow-sm mb-4" role="button" tabindex="0" aria-label="Reply to: <?= h($item['data']['q_text']) ?>" data-idx="<?= $item['idx'] ?>">
                    <div class="vp-prompt-q"><?= h($item['data']['q_text']) ?></div>
                    <div class="vp-prompt-a"><?= h($item['data']['ans_text']) ?></div>
                </div>
            <?php elseif ($item['type'] === 'image'): ?>
                <div class="vp-photo-card shadow-sm mb-4">
                    <img src="images/<?= h($item['data']) ?>" loading="lazy" alt="Profile Photo">
                </div>
            <?php endif; ?>

        <?php endforeach; ?>

        <?php if (!$bioShown && (!empty($profile['bio']) || !empty($profile['pets']) || !empty($profile['workout']))): ?>
            <div class="vp-info-card">
                <?php if (!empty($profile['bio'])): ?>
                    <h6 class="vp-section-title">About Me</h6>
                    <p class="mb-4" style="color: var(--text-dark); font-size: 1.05rem; line-height: 1.6;"><?= nl2br(h($profile['bio'])) ?></p>
                <?php endif; ?>
                <div class="vp-vitals-grid">
                    <?php if (!empty($profile['pets'])): ?><div class="vp-vital-pill"><i class="bi bi-balloon-heart-fill"></i> Pets: <?= h($profile['pets']) ?></div><?php endif; ?>
                    <?php if (!empty($profile['workout'])): ?><div class="vp-vital-pill"><i class="bi bi-activity"></i> Workout: <?= h($profile['workout']) ?></div><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$musicShown && !empty($profile['favourite_song'])): ?>
            <div class="vp-music-card">
                <div class="music-icon-wrapper"><i class="bi bi-music-note-beamed"></i></div>
                <div class="music-info">
                    <span class="music-label">My Anthem</span>
                    <span class="music-track"><?= h($profile['favourite_song']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$loveShown && !empty($profile['love_language'])): ?>
            <div class="vp-love-card">
                <div class="love-icon-wrapper"><i class="bi bi-chat-heart-fill"></i></div>
                <div class="love-info">
                    <span class="love-label">My Love Language</span>
                    <span class="love-value"><?= h($profile['love_language']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="report.php?user_id=<?= $profileId ?>" class="vp-report-link">
                <i class="bi bi-flag-fill"></i> Report this profile
            </a>
        </div>
    </div>

    <?php if (!$fromChat): ?>
        <div class="vp-actions vp-fixed-actions" style="background: transparent; border: none; box-shadow: none; backdrop-filter: none; gap: 2rem;">
            
            <button class="swipe-btn swipe-btn-no" id="btn-pass" aria-label="Pass">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 21.23 C12 21.23 3.28 14.5 3.28 8.5 A4.72 4.72 0 0 1 12 6.27"/>
                    <path d="M12 21.23 C12 21.23 20.72 14.5 20.72 8.5 A4.72 4.72 0 0 0 12 6.27"/>
                    <polyline points="10.5,6.5 12.5,10 10,11.5 13,16"/>
                </svg>
            </button>

            <button class="swipe-btn" id="btn-super" aria-label="Super Like" style="width: 56px; height: 56px; box-shadow: 0 4px 24px rgba(59, 130, 246, 0.25);">
                <svg viewBox="0 0 24 24" fill="#3b82f6" stroke="none" style="width: 24px; height: 24px;" aria-hidden="true">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </button>

            <button class="swipe-btn swipe-btn-yes" id="btn-like" aria-label="Like">
                <svg viewBox="0 0 24 24" fill="#22c55e" stroke="none" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>

        </div>
    <?php endif; ?>

</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const profileId = <?= $profileId ?>;

        // Expand prompts on click
        document.querySelectorAll('.vp-prompt-card').forEach(card => {
            card.addEventListener('click', () => {
                const wasOpen = card.classList.contains('is-open');
                document.querySelectorAll('.vp-prompt-card').forEach(c => {
                    c.classList.remove('is-open');
                    c.setAttribute('aria-expanded', 'false');
                });
                if (!wasOpen) {
                    card.classList.add('is-open');
                    card.setAttribute('aria-expanded', 'true');
                }
            });
        });

        // Helper for Like/Pass actions
        const handleAction = (action) => {
            const formData = new FormData();
            formData.append('target_id', profileId);
            formData.append('action', action);

            fetch('api/swipe.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    const isLike = (action === 'like' || action === 'superlike');

                    if (data.status === 'match') {
                        const overlay = document.createElement('div');
                        overlay.style.cssText = `
                        position: fixed; inset: 0; z-index: 9999;
                        background: rgba(255, 74, 122, 0.95); color: white;
                        display: flex; flex-direction: column; align-items: center; justify-content: center;
                        animation: fadeIn 0.3s forwards;
                    `;
                        overlay.innerHTML = `
                        <h1 style="font-weight: 800; font-size: 3.5rem; margin-bottom: 1rem;">It's a Match!</h1>
                        <p style="font-size: 1.2rem; margin-bottom: 2rem;">You and <?= h($profile['name']) ?> liked each other.</p>
                        <a href="chat.php?id=${profileId}" style="background: white; color: var(--primary-pink); padding: 1rem 3rem; border-radius: 50px; font-weight: 800; text-decoration: none; font-size: 1.1rem; margin-bottom: 1rem;">Send a Message</a>
                        <a href="profiles.php" style="color: white; text-decoration: underline; font-weight: 600;">Keep Swiping</a>
                    `;
                        document.body.appendChild(overlay);
                    } else {
                        const overlay = document.createElement('div');
                        overlay.style.cssText = `
                        position: fixed; inset: 0; z-index: 9999;
                        display: flex; align-items: center; justify-content: center;
                        font-size: 3.5rem; font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif;
                        letter-spacing: 0.05em; border: 6px solid; border-radius: 16px;
                        margin: 2rem; pointer-events: none;
                        animation: overlayPop 0.25s ease forwards;
                        ${isLike ? 'color: #15803d; border-color: #15803d; background: rgba(21,128,61,0.08);' : 'color: #b91c1c; border-color: #b91c1c; background: rgba(185,28,28,0.08);'}
                    `;
                        overlay.textContent = isLike ? 'LIKE' : 'NOPE';
                        document.body.appendChild(overlay);
                        setTimeout(() => {
                            window.location.href = 'profiles.php';
                        }, 400);
                    }
                })
                .catch(err => console.error(err));
        };

        const btnPass = document.getElementById('btn-pass');
        const btnLike = document.getElementById('btn-like');
        const btnSuper = document.getElementById('btn-super');

        if (btnPass) btnPass.addEventListener('click', () => handleAction('pass'));
        if (btnLike) btnLike.addEventListener('click', () => handleAction('like'));
        if (btnSuper) btnSuper.addEventListener('click', () => handleAction('superlike'));
    });

    // Animations
    const style = document.createElement('style');
    style.textContent = `
    @keyframes overlayPop {
        0% { transform: scale(0.5); opacity: 0; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
`;
    document.head.appendChild(style);
</script>

<?php require_once 'includes/footer.php'; ?>