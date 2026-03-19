<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '/var/www/config/db.php';

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}


// ── Get profile ID from URL ──
$profileId = isset($_GET['id']) ? (int) $_GET['id'] : 0;


if ($profileId === 0) {
    header('Location: profiles.php');
    exit();
}

// ── Fetch profile from DB ──
$profile = null;
try {
    $stmt = $pdo->prepare("
        SELECT u.id,
               p.display_name  AS name,
               p.age,
               p.location,
               p.occupation,
               p.biography     AS bio,
               p.main_image    AS image_1,
               p.image_2,
               p.image_3,
               p.image_4,
               p.image_5,
               p.image_6
        FROM users u
        JOIN profile p ON p.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$profileId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $profile = null;
}

// ── 404 if user/profile not found ──
if (!$profile) {
    http_response_code(404);
    // You can make a proper 404 page later — for now redirect back
    header('Location: profiles.php');
    exit();
}

// ── Build tags ──
// $tags = [];
// if (!empty($profile['interests'])) {
//     $tags = array_map('trim', explode(',', $profile['interests']));
// }
// if (!empty($profile['religion']))  $tags[] = $profile['religion'];
// if (!empty($profile['horoscope'])) $tags[] = '♏ ' . $profile['horoscope'];

// ── Build images list (hero + up to 5 extras) ──
// image_1 is the hero. Extra images are image_2 through image_6.
$heroImage  = $profile['image_1'] ?? null;
$extraImages = [];
for ($i = 2; $i <= 6; $i++) {
    $key = 'image_' . $i;
    if (!empty($profile[$key])) {
        $extraImages[] = $profile[$key];
    }
}

// Helper: resolve filename → web URL
function imageUrl(?string $img): ?string {
    if (empty($img)) return null;
    if (str_starts_with($img, 'http')) return $img; // full URL (test data)
    return '/images/' . $img;                        // local file
}

// ── Fetch answered prompts from DB ──
// SQL for when user_answers table is ready:
//
// SELECT q.question_text, a.answer_text
// FROM user_answers a
// JOIN questions q ON q.id = a.question_id
// WHERE a.user_id = ?
// ORDER BY a.id
// LIMIT 6
//
$prompts = [];
try {
    $stmtP = $pdo->prepare("
        SELECT q.question_text AS q, a.answer_text AS a
        FROM user_answers a
        JOIN questions q ON q.id = a.question_id
        WHERE a.user_id = ?
        ORDER BY a.id
        LIMIT 6
    ");
    $stmtP->execute([$profileId]);
    $prompts = $stmtP->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table doesn't exist yet — fall back to hardcoded prompts
    $prompts = [
        ['q' => 'My perfect Sunday looks like...',   'a' => 'Waking up at 10am, grabbing a flat white, then wandering around a hawker centre before ending up at a bookshop I\'ve never been to.'],
        ['q' => 'The way to my heart is...',         'a' => 'Recommending me a song I haven\'t heard yet. Bonus points if it becomes my new favourite.'],
        ['q' => 'I\'m weirdly passionate about...', 'a' => 'Mechanical keyboards. I have too many. My colleagues hate me.'],
        ['q' => 'A life goal of mine is...',         'a' => 'To build something people actually use every day — could be an app, could be a really good sandwich shop.'],
        ['q' => 'My most controversial opinion is...','a' => 'Pineapple on pizza is fine actually and I will die on this hill.'],
    ];
}

$activePage = 'discover';
$pageTitle  = h($profile['name']) . '\'s Profile';
require_once 'includes/header.php';
?>

<div class="vp-wrap">

    <!-- ── Hero Image ── -->
    <div class="vp-hero" aria-label="Profile photo of <?= h($profile['name']) ?>">

        <?php $heroUrl = imageUrl($heroImage); ?>
        <?php if ($heroUrl): ?>
            <img src="<?= h($heroUrl) ?>"
                 alt="Profile photo of <?= h($profile['name']) ?>"
                 class="vp-hero-img">
        <?php else: ?>
            <div class="vp-hero-placeholder" aria-hidden="true"></div>
            <div class="vp-hero-initials" aria-hidden="true">
                <?= h(mb_substr($profile['name'], 0, 1)) ?>
            </div>
        <?php endif; ?>

        <div class="vp-hero-fade" aria-hidden="true"></div>

        <!-- Back button -->
        <a href="javascript:history.back()" class="vp-back-btn" aria-label="Go back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </a>

        <!-- Pass / Like -->
        <div class="vp-hero-actions" aria-label="Profile actions">
            <button class="vp-action-btn vp-btn-pass"
                    aria-label="Pass on <?= h($profile['name']) ?>" title="Pass"
                    data-user-id="<?= h((string)$profile['id']) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <button class="vp-action-btn vp-btn-like"
                    aria-label="Like <?= h($profile['name']) ?>" title="Like"
                    data-user-id="<?= h((string)$profile['id']) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- ── Scrollable content ── -->
    <div class="vp-content">

        <!-- Name + age -->
        <div class="vp-identity">
            <h1 class="vp-name">
                <?= h($profile['name']) ?>,
                <span class="vp-age"><?= h((string)$profile['age']) ?></span>
            </h1>

            <!-- Location + occupation chips -->
            <?php if (!empty($profile['location']) || !empty($profile['occupation'])): ?>
            <p class="vp-meta">
                <?php if (!empty($profile['location'])): ?>
                <span class="vp-meta-chip">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <?= h($profile['location']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($profile['occupation'])): ?>
                <span class="vp-meta-chip">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                    <?= h($profile['occupation']) ?>
                </span>
                <?php endif; ?>
            </p>
            <?php endif; ?>

            <!-- Tags -->
            <!-- <?php if (!empty($tags)): ?>
            <div class="vp-tags" aria-label="Interests and attributes">
                <?php foreach ($tags as $tag): ?>
                    <span class="vp-tag"><?= h($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div> -->

        <div class="vp-divider" aria-hidden="true"></div>

        <!-- Bio -->
        <?php if (!empty($profile['bio'])): ?>
        <section aria-label="About <?= h($profile['name']) ?>">
            <p class="vp-bio"><?= nl2br(h($profile['bio'])) ?></p>
        </section>
        <div class="vp-divider" aria-hidden="true"></div>
        <?php endif; ?>

        <!-- ── Interleaved prompts + extra images ── -->
        <?php
        $totalSections = max(count($extraImages), count($prompts));
        for ($i = 0; $i < $totalSections; $i++):
        ?>

            <?php if (isset($prompts[$i])): ?>
            <div class="vp-prompt-card"
                 role="button"
                 tabindex="0"
                 aria-expanded="false"
                 aria-label="Reply to: <?= h($prompts[$i]['q']) ?>"
                 data-idx="<?= $i ?>">

                <span class="vp-prompt-label" aria-hidden="true">💬</span>
                <p class="vp-prompt-q"><?= h($prompts[$i]['q']) ?></p>
                <p class="vp-prompt-a"><?= h($prompts[$i]['a']) ?></p>
                <div class="vp-tap-hint" aria-hidden="true">Tap to reply →</div>

                <div class="vp-reply-box" id="reply-<?= $i ?>" hidden aria-hidden="true">
                    <textarea
                        class="vp-reply-textarea"
                        rows="3"
                        maxlength="500"
                        placeholder="Say something to <?= h($profile['name']) ?>..."
                        aria-label="Your reply to: <?= h($prompts[$i]['q']) ?>"></textarea>
                    <div class="vp-reply-footer">
                        <span class="vp-chars" id="chars-<?= $i ?>">0 / 500</span>
                        <button class="vp-send-btn"
                                data-profile-id="<?= h((string)$profile['id']) ?>"
                                data-prompt-q="<?= h($prompts[$i]['q']) ?>"
                                aria-label="Send reply">
                            Send
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($extraImages[$i])): ?>
            <?php $extraUrl = imageUrl($extraImages[$i]); ?>
            <?php if ($extraUrl): ?>
            <div class="vp-photo-block">
                <img src="<?= h($extraUrl) ?>"
                     alt="Photo of <?= h($profile['name']) ?>"
                     class="vp-photo" loading="lazy">
            </div>
            <?php endif; ?>
            <?php endif; ?>

        <?php endfor; ?>

        <div style="height:1.5rem" aria-hidden="true"></div>
    </div>
</div>

<script>
document.querySelectorAll('.vp-prompt-card').forEach(card => {
    const idx      = card.dataset.idx;
    const box      = document.getElementById('reply-' + idx);
    const textarea = box?.querySelector('.vp-reply-textarea');
    const charEl   = document.getElementById('chars-' + idx);
    const sendBtn  = box?.querySelector('.vp-send-btn');

    function toggle(e) {
        if (box && box.contains(e.target)) return;
        const opening = !card.classList.contains('is-open');
        card.classList.toggle('is-open', opening);
        box.hidden = !opening;
        box.setAttribute('aria-hidden', String(!opening));
        card.setAttribute('aria-expanded', String(opening));
        if (opening) setTimeout(() => textarea?.focus(), 40);
    }

    card.addEventListener('click', toggle);
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(e); }
    });

    textarea?.addEventListener('input', () => {
        const n = textarea.value.length;
        if (charEl) {
            charEl.textContent = n + ' / 500';
            charEl.style.color = n > 450 ? '#ef4444' : '#ccc';
        }
    });

    sendBtn?.addEventListener('click', e => {
        e.stopPropagation();
        const msg = textarea?.value.trim();
        if (!msg) {
            textarea?.focus();
            textarea.style.borderColor = '#ef4444';
            setTimeout(() => textarea.style.borderColor = '', 1200);
            return;
        }

        // TODO: wire to /api/send_reply.php when ready
        console.log('Reply to:', sendBtn.dataset.promptQ, '| Message:', msg);

        box.innerHTML = `
            <div class="vp-sent-msg" role="status" aria-live="polite">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Sent! We'll let <?= h($profile['name']) ?> know.
            </div>`;
        card.style.cursor = 'default';
        card.classList.add('is-open');
        card.removeEventListener('click', toggle);
    });
});

// Pass / Like buttons — call swipe API then redirect back
function fadeOutAndRedirect(url) {
    document.body.style.transition = 'opacity 0.35s ease';
    document.body.style.opacity    = '0';
    setTimeout(() => { window.location.href = url; }, 380);
}

document.querySelectorAll('.vp-btn-pass, .vp-btn-like').forEach(btn => {
    btn.addEventListener('click', async () => {
        const isLike    = btn.classList.contains('vp-btn-like');
        const direction = isLike ? 'like' : 'pass';
        const userId    = btn.dataset.userId;

        // Await result BEFORE showing anything
        let isMatch = false;
        try {
            const res  = await fetch('/api/swipe.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ swiped_id: parseInt(userId), direction })
            });
            const data = await res.json();
            isMatch = data.match === true;
        } catch (e) {
            console.error('Swipe error:', e);
        }

        if (isMatch) {
            // Match — skip LIKE overlay, show match popup instead
            const name = document.querySelector('.vp-name')
                ?.firstChild?.textContent?.trim()?.replace(',','') ?? 'them';
            const overlay = document.createElement('div');
            overlay.className = 'match-popup-overlay';
            overlay.innerHTML = `
                <div class="match-popup" role="alertdialog" aria-live="assertive">
                    <span class="match-popup-emoji">💚</span>
                    <p class="match-popup-heading">It's a Match!</p>
                    <p class="match-popup-sub">You and ${name} liked each other</p>
                    <p class="match-popup-hint">Check your matches tab</p>
                    <div class="match-popup-bar">
                        <div class="match-popup-bar-fill"></div>
                    </div>
                </div>`;
            document.body.appendChild(overlay);
            setTimeout(() => fadeOutAndRedirect('profiles.php?t=' + Date.now()), 2000);

        } else {
            // No match — show LIKE/NOPE overlay then redirect
            const overlay = document.createElement('div');
            overlay.setAttribute('aria-hidden', 'true');
            overlay.style.cssText = `
                position: fixed; inset: 0; z-index: 9999;
                display: flex; align-items: center; justify-content: center;
                font-size: 3.5rem; font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif;
                letter-spacing: 0.05em; border: 6px solid; border-radius: 16px;
                margin: 2rem; pointer-events: none;
                animation: overlayPop 0.25s ease forwards;
                ${isLike
                    ? 'color: #22c55e; border-color: #22c55e; background: rgba(34,197,94,0.08);'
                    : 'color: #ef4444; border-color: #ef4444; background: rgba(239,68,68,0.08);'}
            `;
            overlay.textContent = isLike ? 'LIKE' : 'NOPE';
            document.body.appendChild(overlay);
            setTimeout(() => fadeOutAndRedirect('profiles.php?t=' + Date.now()), 400);
        }
    });
});

// Inject keyframe for the overlay pop animation
const style = document.createElement('style');
style.textContent = `
    @keyframes overlayPop {
        from { opacity: 0; transform: scale(0.8); }
        to   { opacity: 1; transform: scale(1); }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once 'includes/footer.php'; ?>