<?php
session_start();

// Prevent browser caching so swipes are always reflected on reload
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

$activePage = 'discover';
$pageTitle  = 'Discover';

// ── Fetch profiles from DB ──
$currentUser = $_SESSION['user_id'];
$profiles    = [];
// Fetch current user preferences
$prefs  = null;
$showMe = null;
$ageMin = null;
$ageMax = null;

require_once '/var/www/config/db.php';

try {
    $prefStmt = $pdo->prepare("
        SELECT show_me, age_min, age_max
        FROM preferences
        WHERE user_id = ?
        LIMIT 1
    ");
    $prefStmt->execute([$currentUser]);
    $prefs = $prefStmt->fetch(PDO::FETCH_ASSOC);

    if ($prefs) {
        $showMeRaw = strtolower(trim($prefs['show_me'] ?? ''));
        $showMe    = ($showMeRaw !== '' && $showMeRaw !== 'everyone')
            ? ucfirst($showMeRaw)  // converts back to 'Male'/'Female' to match what's stored in profile.gender
            : null;

        $ageMin = (!empty($prefs['age_min']) && (int)$prefs['age_min'] > 0)
            ? (int)$prefs['age_min']
            : null;
        $ageMax = (!empty($prefs['age_max']) && (int)$prefs['age_max'] > 0)
            ? (int)$prefs['age_max']
            : null;
    }
} catch (Exception $e) {
    // if no preferences found
    $prefs = null;
}

// Build profiles query dynamically based on preferences
try {
    $conditions = [
        "u.id != ?",
        "u.id NOT IN (SELECT swiped_id FROM swipes WHERE swiper_id = ?)",
        "u.id NOT IN (SELECT user_id FROM bans)"
    ];
    $params = [$currentUser, $currentUser];

    // Gender filter - male / female
    if ($showMe !== null) {
         $conditions[] = "p.gender = ?";
         $params[]     = $showMe;
    }

    // Age range filter — apply whichever bounds are set
    if ($ageMin !== null) {
        $conditions[] = "p.age >= ?";
        $params[]     = $ageMin;
    }
    if ($ageMax !== null) {
        $conditions[] = "p.age <= ?";
        $params[]     = $ageMax;
    }

    $where = implode(' AND ', $conditions);

    $stmt = $pdo->prepare("
        SELECT u.id,
               p.display_name  AS name,
               p.age,
               p.location,
               p.occupation,
               p.biography     AS bio,
               p.main_image    AS profile_pic
        FROM users u
        JOIN profile p ON p.user_id = u.id
        WHERE {$where}
        ORDER BY RAND()
        LIMIT 20
    ");
    $stmt->execute($params);
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Sanitize all text data before sending it to JavaScript to prevent DOM-based XSS
    foreach ($profiles as &$p) {
        $p['name']       = htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8');
        $p['location']   = htmlspecialchars((string)$p['location'], ENT_QUOTES, 'UTF-8');
        $p['occupation'] = htmlspecialchars((string)$p['occupation'], ENT_QUOTES, 'UTF-8');
        $p['bio']        = htmlspecialchars((string)$p['bio'], ENT_QUOTES, 'UTF-8');
    }
    unset($p); // Break the reference
} catch (Exception $e) {
    $profiles = [];
}
require_once 'includes/header.php';

?>

<main class="discover-main" role="main" aria-label="Discover profiles">
    <p class="discover-label" aria-hidden="true">✦ Discover</p>
    <h1 class="discover-title">Find Your Spark</h1>

    <?php if (empty($profiles)): ?>
        <div class="empty-state" role="status">
            <div class="empty-icon" aria-hidden="true">💫</div>
            <h3>You've seen everyone!</h3>
            <p>Check back later for new profiles, or revisit your matches.</p>
            <a href="matches.php" class="btn-solid-custom mt-3 d-inline-block"
               style="text-decoration:none; padding:0.6rem 1.8rem;">
                View Matches →
            </a>
        </div>

    <?php else: ?>
        <div class="swipe-arena" role="region" aria-label="Profile cards">

            <!-- ← Pass -->
            <button class="swipe-btn swipe-btn-no" id="btn-no"
                    aria-label="Pass on this profile" title="Pass">
                <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 21.23 C12 21.23 3.28 14.5 3.28 8.5 A4.72 4.72 0 0 1 12 6.27"/>
                    <path d="M12 21.23 C12 21.23 20.72 14.5 20.72 8.5 A4.72 4.72 0 0 0 12 6.27"/>
                    <polyline points="10.5,6.5 12.5,10 10,11.5 13,16"/>
                </svg>
            </button>

            <!-- Card Stack -->
            <div class="card-stack" role="region" aria-live="polite" aria-atomic="true"
                 aria-label="Current profile">
                <div class="swipe-feedback feedback-yes" id="feedback-yes" aria-hidden="true">LIKE</div>
                <div class="swipe-feedback feedback-no"  id="feedback-no"  aria-hidden="true">NOPE</div>

                <div class="profile-card" id="profile-card" tabindex="0" aria-label="Profile card">
                    <div class="card-image" id="card-image" role="img" aria-label="Profile photo"></div>
                    <div class="card-gradient" aria-hidden="true"></div>
                    <div class="card-body-content">
                        <h2 class="card-name" id="card-name"></h2>
                        <p class="card-meta" id="card-meta"></p>
                        <div class="card-tags" id="card-tags" role="group" aria-label="Interests"></div>
                        <p class="card-bio"  id="card-bio"></p>
                    </div>
                </div>
            </div>

            <!-- → Like -->
            <button class="swipe-btn swipe-btn-yes" id="btn-yes"
                    aria-label="Like this profile" title="Like">
                <svg viewBox="0 0 24 24" fill="#22c55e" stroke="none" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </button>
        </div>

        <!-- Counter -->
        <div class="profile-counter" aria-live="polite">
            <span id="counter-text"></span>
            <div class="counter-dots" id="counter-dots" aria-hidden="true"></div>
        </div>
    <?php endif; ?>
</main>

<script>
    const profiles = <?php echo json_encode($profiles, JSON_HEX_TAG | JSON_HEX_APOS); ?>;

    let currentIndex = 0;
    let isAnimating  = false;

    const card       = document.getElementById('profile-card');
    const cardImage  = document.getElementById('card-image');
    const cardName   = document.getElementById('card-name');
    const cardMeta   = document.getElementById('card-meta');
    const cardTags   = document.getElementById('card-tags');
    const cardBio    = document.getElementById('card-bio');
    const feedYes    = document.getElementById('feedback-yes');
    const feedNo     = document.getElementById('feedback-no');
    const counterTxt = document.getElementById('counter-text');
    const dotsWrap   = document.getElementById('counter-dots');

    // Gradient fallback for profiles with no photo
    const gradients = [
        'linear-gradient(135deg, #4A1060, #9B2368)',
        'linear-gradient(135deg, #0D3875, #1A6FA8)',
        'linear-gradient(135deg, #1A5C3A, #2E9E65)',
        'linear-gradient(135deg, #6B2D0A, #C4622D)',
        'linear-gradient(135deg, #3D0A4A, #7B2FBE)',
    ];

    function renderProfile(index) {
        if (index >= profiles.length) {
            document.querySelector('.swipe-arena').innerHTML = `
                <div class="empty-state" role="status">
                    <div class="empty-icon">💫</div>
                    <h3>You've seen everyone!</h3>
                    <p>Check back later for new profiles.</p>
                </div>`;
            if (dotsWrap)   dotsWrap.innerHTML     = '';
            if (counterTxt) counterTxt.textContent = '';
            return;
        }

        const p = profiles[index];
        //const cardGradient = document.querySelector('.card-gradient');

        // Image: real photo → avatar initials → gradient
        if (p.profile_pic) {
            const src = p.profile_pic.startsWith('http')
                ? p.profile_pic          // full URL stored in DB
                : `/images/${p.profile_pic}`;  // filename stored in DB
            cardImage.style.backgroundImage = `url('${src}')`;
            cardImage.style.backgroundSize  = 'cover';
            cardImage.style.opacity         = '1'; // full opacity with photo

        } else if (p.name) {
            const avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(p.name)}&background=4A1060&color=F5E6FF&size=400`;
            cardImage.style.backgroundImage = `url('${avatar}')`;
            cardImage.style.backgroundSize  = 'cover';
            cardImage.style.opacity         = '0.4';

        } else {
            cardImage.style.backgroundImage = gradients[index % gradients.length];
            cardImage.style.backgroundSize  = 'cover';
            cardImage.style.opacity         = '0.6';

        }

        cardImage.setAttribute('aria-label', `Photo of ${p.name ?? 'this person'}`);

        // Name & age
        cardName.textContent = [p.name, p.age].filter(Boolean).join(', ');

        // Location & occupation
        const parts = [p.location, p.occupation].filter(Boolean);
        cardMeta.innerHTML = parts.map((pt, i) =>
            i < parts.length - 1
                ? `${pt} <span class="card-meta-dot" aria-hidden="true"></span>`
                : pt
        ).join(' ');


        // Bio — column aliased as 'bio' in SQL
        cardBio.textContent = p.bio || '';

        // Counter
        const remaining = profiles.length - index;
        counterTxt.textContent = `${remaining} profile${remaining !== 1 ? 's' : ''} nearby`;

        dotsWrap.innerHTML = '';
        const dotCount = Math.min(profiles.length, 8);
        for (let i = 0; i < dotCount; i++) {
            const dot = document.createElement('div');
            dot.className = 'counter-dot' + (i === index % dotCount ? ' active' : '');
            dotsWrap.appendChild(dot);
        }
    }

    async function recordSwipe(direction) {
        const profile = profiles[currentIndex];
        if (!profile) return false;
        
        // Grab the PHP session token securely
        const csrfToken = "<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>";

        try {
            const res  = await fetch('/api/swipe.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                // Add the CSRF token to the JSON body sent to the server
                body:    JSON.stringify({ 
                    swiped_id: profile.id, 
                    direction: direction,
                    csrf_token: csrfToken 
                })
            });
            const data = await res.json();
            return data.match === true;
        } catch (e) {
            console.error('Swipe error:', e);
            return false;
        }
    }

    function showMatchPopup(name) {
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
        setTimeout(() => {
            overlay.style.transition = 'opacity 0.3s ease';
            overlay.style.opacity = '0';
            setTimeout(() => overlay.remove(), 300);
        }, 2500);
    }

    function showFeedback(el) {
        el.style.opacity = '1';
        setTimeout(() => { el.style.opacity = '0'; }, 350);
    }

    async function swipe(direction) {
        if (isAnimating || currentIndex >= profiles.length) return;
        isAnimating = true;

        if (direction === 'like') {
            showFeedback(feedYes);
            card.classList.add('slide-out-right');
        } else {
            showFeedback(feedNo);
            card.classList.add('slide-out-left');
        }

        const matchPromise = recordSwipe(direction);

        setTimeout(async () => {
            card.classList.remove('slide-out-right', 'slide-out-left');
            currentIndex++;
            renderProfile(currentIndex);
            card.classList.add('slide-in-up');
            card.addEventListener('animationend', async () => {
                card.classList.remove('slide-in-up');
                isAnimating = false;
                const isMatch = await matchPromise;
                if (isMatch) showMatchPopup(profiles[currentIndex - 1]?.name ?? 'them');
            }, { once: true });
        }, 400);
    }

    // Button controls
    document.getElementById('btn-yes')?.addEventListener('click', () => swipe('like'));
    document.getElementById('btn-no')?.addEventListener('click',  () => swipe('pass'));

    // Keyboard
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowRight') swipe('like');
        if (e.key === 'ArrowLeft')  swipe('pass');
    });

    // Touch + click
    let touchStartX = 0;
    let touchMoved  = false;

    card?.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
        touchMoved  = false;
    }, { passive: true });

    card?.addEventListener('touchmove', () => { touchMoved = true; });

    card?.addEventListener('touchend', e => {
        const delta = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(delta) > 60) swipe(delta > 0 ? 'like' : 'pass');
    });

    // Tap card → expanded profile view
    card?.addEventListener('click', () => {
        const p = profiles[currentIndex];
        if (p && !isAnimating && !touchMoved) {
            window.location.href = `profile.php?id=${p.id}`;
        }
    });

    if (profiles.length > 0) renderProfile(0);
</script>

<?php require_once 'includes/footer.php'; ?>