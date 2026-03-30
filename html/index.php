<?php
session_start();

// 1. Establish Database Connection 
// (Using the robust path check from your other files)
$dbPath = file_exists(__DIR__ . '/../config/db.php') ? __DIR__ . '/../config/db.php' : dirname(__DIR__) . '/config/db.php';
require_once $dbPath;

$activePage = 'home';
$pageTitle = 'Home';

require_once 'includes/header.php';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// 2. Fetch 3 Random, Valid Q&A Prompts
$dynamicPrompts = [];
try {
    // We join the 4 tables needed and ensure the answer text isn't empty
    $stmt = $pdo->query("
        SELECT 
            u.first_name, 
            p.age, 
            p.main_image, 
            q.q_text, 
            a.ans_text 
        FROM Answers a
        JOIN questions q ON a.qn_id = q.qn_id
        JOIN profile p ON a.user_id = p.user_id
        JOIN users u ON u.id = p.user_id
        WHERE a.ans_text IS NOT NULL AND TRIM(a.ans_text) != ''
        ORDER BY RAND()
        LIMIT 3
    ");
    $dynamicPrompts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If the tables aren't set up yet, it will fail silently and use defaults
}

// 3. Set Defaults (Fallback)
// If the database has less than 3 answers, we pad it with these so the design doesn't break.
$defaultPrompts = [
    [
        'q_text' => "I'll know we'll get along if...",
        'ans_text' => "You also survive on iced lattes during finals week ☕",
        'first_name' => "Aisha",
        'age' => 21,
        'main_image' => "aisha.webp"
    ],
    [
        'q_text' => "My most controversial opinion is...",
        'ans_text' => "The Punggol campus food court is actually underrated.",
        'first_name' => "Ethan",
        'age' => 22,
        'main_image' => "ethan.webp"
    ],
    [
        'q_text' => "I'm weirdly passionate about...",
        'ans_text' => "Writing perfectly clean PHP code without any errors 🤓",
        'first_name' => "Jonathan",
        'age' => 23,
        'main_image' => "jonathan.jpeg"
    ]
];

// Merge the dynamic DB results with the defaults to guarantee we always have exactly 3 cards
$displayPrompts = [];
for ($i = 0; $i < 3; $i++) {
    $displayPrompts[] = $dynamicPrompts[$i] ?? $defaultPrompts[$i];
}
?>

<main class="container d-flex flex-column" style="flex: 1;">

    <?php if ($success !== ''): ?>
        <div class="d-flex justify-content-center mt-3 position-relative w-100" style="z-index: 100;" id="successAlert">
            <div class="custom-alert-pill" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <span class="d-flex align-items-center mt-1"><?= h($success) ?></span>
                <i class="bi bi-x ms-3 fs-4 alert-close-btn" onclick="this.closest('#successAlert').remove();" title="Close"></i>
            </div>
        </div>
    <?php endif; ?>

    <div class="row align-items-center justify-content-between mt-5 mb-5 py-4" style="min-height: 75vh;">

        <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
            <h1 class="fw-bold mb-4" style="font-size: clamp(3.5rem, 5vw, 5.5rem); letter-spacing: -2px; line-height: 1.1; color: var(--text-dark);">
                Swipe Less.<br>Connect More.
            </h1>
            <p class="mb-5 mx-auto mx-lg-0" style="font-size: clamp(1.1rem, 2vw, 1.3rem); color: #595959; line-height: 1.6; max-width: 500px;">
                No games, no ghosting, just genuine vibes with people who actually get you!
                <i class="fa-solid fa-heart fa-bounce ms-1" style="color: var(--primary-pink);"></i>
            </p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="signup.php" class="btn-solid-custom d-inline-block shadow-sm" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px;">
                    Join the Community
                </a>
            <?php endif; ?>
        </div>

        <div class="col-lg-5 d-flex justify-content-center position-relative mt-5 mt-lg-0">
            <div class="interactive-demo-wrapper position-relative">

                <div id="tinder-stack" class="w-100 position-relative" style="height: 480px;">
                    <div class="demo-card shadow-lg" style="background-image: url('images/marcus.avif');">
                        <div class="demo-card-gradient"></div>
                        <div class="text-white p-4 w-100 position-absolute bottom-0 text-start z-3">
                            <h2 class="fw-bold mb-1" style="font-size: 2rem;">Marcus, 23</h3>
                            <p class="mb-3 fw-bold opacity-75"><i class="bi bi-geo-alt-fill"></i> Punggol &bull; SITizen</p>
                            <div class="d-flex gap-2"><span class="badge bg-light text-dark rounded-pill py-2 px-3">Gym</span><span class="badge bg-light text-dark rounded-pill py-2 px-3">Gaming</span></div>
                        </div>
                    </div>
                    <div class="demo-card shadow-lg" style="background-image: url('images/aisha.webp');">
                        <div class="demo-card-gradient"></div>
                        <div class="text-white p-4 w-100 position-absolute bottom-0 text-start z-3">
                            <h2 class="fw-bold mb-1" style="font-size: 2rem;">Aisha, 21</h3>
                            <p class="mb-3 fw-bold opacity-75"><i class="bi bi-geo-alt-fill"></i> Dover &bull; InfoSec</p>
                            <div class="d-flex gap-2"><span class="badge bg-light text-dark rounded-pill py-2 px-3">Coffee</span><span class="badge bg-light text-dark rounded-pill py-2 px-3">Cats</span></div>
                        </div>
                    </div>
                    <div class="demo-card shadow-lg" style="background-image: url('images/jonathan.jpeg');">
                        <div class="demo-card-gradient"></div>
                        <div class="text-white p-4 w-100 position-absolute bottom-0 text-start z-3">
                            <h2 class="fw-bold mb-1" style="font-size: 2rem;">Jonathan, 20</h3>
                            <p class="mb-3 fw-bold opacity-75"><i class="bi bi-geo-alt-fill"></i> Clementi &bull; Developer</p>
                            <div class="d-flex gap-2"><span class="badge bg-light text-dark rounded-pill py-2 px-3">Art</span><span class="badge bg-light text-dark rounded-pill py-2 px-3">Music</span></div>
                        </div>
                    </div>
                </div>

                <div class="demo-actions d-flex justify-content-center gap-4 w-100 position-absolute">
                    <button class="action-btn nope-btn" aria-label="Pass" onclick="demoSwipe('left')"><i class="bi bi-x-lg"></i></button>
                    <button class="action-btn like-btn" aria-label="Like" onclick="demoSwipe('right')"><i class="bi bi-heart-fill" style="color: var(--primary-pink);"></i></button>
                </div>

                <div class="demo-signup-overlay d-none" id="demo-signup-overlay">
                    <div class="text-center p-4 bg-white shadow-lg mx-3 w-100" style="border-radius: 24px; border: 1px solid rgba(255, 74, 122, 0.1);">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 65px; height: 65px; font-size: 1.8rem; background: rgba(255, 74, 122, 0.1); color: var(--primary-pink);">
                            <i class="bi bi-stars"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Out of swipes!</h4>
                        <p class="text-muted mb-4 small">Want to keep swiping and see who liked you? Join S³ today.</p>
                        <a href="signup.php" class="btn-solid-custom w-100 py-2 d-block text-decoration-none shadow-sm">Create an Account</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <section class="py-5 my-5">
        <div class="text-center mb-5">
            <span class="badge rounded-pill mb-3 shadow-sm" style="background: rgba(216, 27, 96, 0.08); color: #c2185b; padding: 0.6rem 1.2rem; font-weight: 700; letter-spacing: 0.05em;">
                PERSONALITY FIRST
            </span>
            <h2 class="fw-bold" style="color: var(--text-dark); font-size: clamp(2rem, 4vw, 2.5rem); letter-spacing: -1px;">
                More Than Just a Photo
            </h2>
            <p class="mx-auto mt-3" style="max-width: 600px; font-size: 1.1rem; color: #595959; line-height: 1.6;">
                Skip the superficial small talk. Our unique profile prompts let your true personality shine, sparking conversations that actually matter.
            </p>
        </div>

        <div class="row g-4 justify-content-center align-items-center px-3">
            <?php
            // Define the CSS transforms and classes to keep that staggered "polaroid stack" look
            $cardTransforms = [
                "transform: rotate(-3deg);",
                "transform: rotate(2deg) translateY(15px);",
                "transform: rotate(-2deg);"
            ];
            $cardClasses = [
                "col-md-5 col-lg-4",
                "col-md-5 col-lg-4",
                "col-md-5 col-lg-4 d-none d-lg-block"
            ];

            foreach ($displayPrompts as $idx => $prompt):
                // Handle the image path dynamically (fallback to Default.webp if empty)
                $imgPath = !empty($prompt['main_image']) ? 'images/' . $prompt['main_image'] : 'images/Default.webp';
            ?>
                <div class="<?= $cardClasses[$idx] ?>">
                    <div class="landing-prompt-card shadow-sm" style="<?= $cardTransforms[$idx] ?>">
                        <div class="prompt-q"><?= h($prompt['q_text']) ?></div>
                        <div class="prompt-a"><?= h($prompt['ans_text']) ?></div>
                        <div class="prompt-user">
                            <img src="<?= h($imgPath) ?>" alt="<?= h($prompt['first_name']) ?>" class="prompt-avatar" loading="lazy">
                            <?= h($prompt['first_name']) ?>, <?= h((string)$prompt['age']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="py-5 my-5 bg-white shadow-sm border" style="border-radius: 32px; border-color: rgba(216, 27, 96, 0.08) !important; padding: 2rem 3rem;">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 text-center text-lg-start">
                <span class="badge rounded-pill mb-3 shadow-sm" style="background: rgba(34, 197, 94, 0.1); color: #15803d; padding: 0.6rem 1.2rem; font-weight: 700; letter-spacing: 0.05em;">
                    SIT COMMUNITY
                </span>
                <h2 class="fw-bold mb-4" style="color: var(--text-dark); font-size: clamp(2rem, 4vw, 2.5rem); letter-spacing: -1px;">
                    Find Your Study Buddy<br>(Or More)
                </h2>
                <p class="mb-4" style="font-size: 1.1rem; line-height: 1.6; color: #595959;">
                    S³ isn't just about romance. It is about building your network at SIT. Whether you need someone to help debug your Web Systems project or grab lunch between lectures, you will find them here.
                </p>
                <a href="signup.php" class="btn-outline-custom d-inline-flex align-items-center gap-2 mt-2">
                    Explore the Community <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="campus-card bg-light p-4 rounded-4 h-100 text-center transition-hover">
                            <div class="mb-3 text-primary bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="bi bi-laptop"></i>
                            </div>
                            <h3 class="fw-bold h5" style="color: var(--text-dark);">Hackathon Teams</h3>
                            <p class="small mb-0" style="color: #595959;">Find skilled peers for your next big ICT project.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="campus-card bg-light p-4 rounded-4 h-100 text-center transition-hover" style="transform: translateY(20px);">
                            <div class="mb-3 text-warning bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="bi bi-cup-hot-fill"></i>
                            </div>
                            <h3 class="fw-bold h5" style="color: var(--text-dark);">Kopi Buddies</h3>
                            <p class="small mb-0" style="color: #595959;">Grab a quick coffee at the Punggol campus.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="campus-card bg-light p-4 rounded-4 h-100 text-center transition-hover">
                            <div class="mb-3 text-success bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="bi bi-controller"></i>
                            </div>
                            <h3 class="fw-bold h5" style="color: var(--text-dark);">Gaming Squads</h3>
                            <p class="small mb-0" style="color: #595959;">Relax after classes with fellow SIT gamers.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="campus-card bg-light p-4 rounded-4 h-100 text-center transition-hover" style="transform: translateY(20px);">
                            <div class="mb-3 text-danger bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="bi bi-moon-stars-fill"></i>
                            </div>
                            <h3 class="fw-bold h5" style="color: var(--text-dark);">Study Partners</h3>
                            <p class="small mb-0" style="color: #595959;">Survive the late-night exam grinds together.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-features-wrapper mt-5 mb-0">
        <div class="container px-4">
            <div class="text-center mb-5">
                <span class="badge rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.12); color: #fff; padding: 0.6rem 1.2rem; font-weight: 700; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.25);">
                    PLATFORM FEATURES
                </span>
                <h2 class="fw-bold text-white" style="font-size: clamp(2rem, 4vw, 2.5rem); letter-spacing: -1px;">
                    Everything you need to connect.
                </h2>
                <p class="mx-auto mt-3" style="color: rgba(255, 255, 255, 0.9); max-width: 600px; font-size: 1.1rem; line-height: 1.6;">
                    From setting your preferences to sending that first message, S³ is built to make campus networking effortless.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="home-feature-card">
                        <div class="feature-icon"><i class="bi bi-person-vcard-fill"></i></div>
                        <h3 class="fw-bold mb-3 h4 text-white">1. Rich SIT Profiles</h3>
                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; line-height: 1.6;">Create an account with your SIT email. Build a profile that showcases your degree, your love language, and your answers to our interactive Q&A icebreakers.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="home-feature-card">
                        <div class="feature-icon"><i class="bi bi-sliders"></i></div>
                        <h3 class="fw-bold mb-3 h4 text-white">2. Smart Preferences</h3>
                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; line-height: 1.6;">Control exactly who you see. Filter the campus by age, gender, dating intent (casual vs. relationship), and even specific SIT degree clusters.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="home-feature-card">
                        <div class="feature-icon"><i class="bi bi-phone-vibrate-fill"></i></div>
                        <h3 class="fw-bold mb-3 h4 text-white">3. The Swipe Arena</h3>
                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; line-height: 1.6;">A flawless, interactive swiping experience. Swipe right to Like, left to Pass, or hit the Super Like button to make sure you stand out in their queue.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="home-feature-card">
                        <div class="feature-icon"><i class="bi bi-chat-heart-fill"></i></div>
                        <h3 class="fw-bold mb-3 h4 text-white">4. Matches & Real-Time Chat</h3>
                        <p class="mb-0" style="color: rgba(255, 255, 255, 0.85); font-size: 0.95rem; line-height: 1.6;">When the feeling is mutual, it's a match! Instantly unlock our real-time messaging platform to start planning your first study date on campus.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>





</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stack = document.getElementById('tinder-stack');
        if (!stack) return;

        const overlay = document.getElementById('demo-signup-overlay');
        const actions = document.querySelector('.demo-actions');
        let cards = Array.from(document.querySelectorAll('.demo-card'));

        // Cards are stacked in DOM order. index 2 is the Top card.
        let topCardIndex = cards.length - 1;

        function updateCards() {
            cards.forEach((card, i) => {
                card.classList.remove('is-dragging');
                card.style.transform = '';

                let offset = topCardIndex - i;
                if (offset < 0 || card.classList.contains('swiped')) return; // Already swiped

                // Generate the stacked depth effect
                if (offset === 0) {
                    card.style.transform = `translateY(0) scale(1)`;
                    card.style.zIndex = 3;
                    card.style.filter = `brightness(1)`;
                } else if (offset === 1) {
                    card.style.transform = `translateY(15px) scale(0.95)`;
                    card.style.zIndex = 2;
                    card.style.filter = `brightness(0.85)`;
                } else if (offset === 2) {
                    card.style.transform = `translateY(30px) scale(0.9)`;
                    card.style.zIndex = 1;
                    card.style.filter = `brightness(0.7)`;
                }
            });
        }

        function handleSwipe(card, direction) {
            card.classList.add('swiped');
            card.style.transition = 'transform 0.5s ease-out, opacity 0.5s';

            // Throw the card off screen
            const moveOutWidth = document.body.clientWidth;
            const endX = direction === 'right' ? moveOutWidth : -moveOutWidth;
            card.style.transform = `translate(${endX}px, 50px) rotate(${direction === 'right' ? 30 : -30}deg)`;
            card.style.opacity = '0';

            topCardIndex--;

            if (topCardIndex >= 0) {
                initDrag(cards[topCardIndex]);
                updateCards();
            } else {
                // Out of cards -> Trigger the signup wall!
                setTimeout(() => {
                    overlay.classList.remove('d-none');
                    actions.style.display = 'none';
                }, 300);
            }
        }

        // Connects the UI buttons to the swipe engine
        window.demoSwipe = function(direction) {
            if (topCardIndex < 0) return;
            handleSwipe(cards[topCardIndex], direction);
        }

        function initDrag(card) {
            let startX = 0,
                startY = 0,
                currentX = 0,
                currentY = 0;
            let isDragging = false;

            function onStart(e) {
                if (e.target.closest('.action-btn')) return; // Don't drag if clicking buttons
                isDragging = true;
                startX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                startY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
                card.classList.add('is-dragging');
            }

            function onMove(e) {
                if (!isDragging) return;
                currentX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                currentY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;

                const deltaX = currentX - startX;
                const deltaY = currentY - startY;
                const rotate = deltaX * 0.05; // Slightly tilt the card as you drag

                card.style.transform = `translate(${deltaX}px, ${deltaY}px) rotate(${rotate}deg)`;
            }

            function onEnd(e) {
                if (!isDragging) return;
                isDragging = false;
                card.classList.remove('is-dragging');

                const deltaX = currentX - startX;
                // If dragged more than 100px, trigger a swipe. Otherwise, snap it back.
                if (Math.abs(deltaX) > 100 && currentX !== 0) {
                    handleSwipe(card, deltaX > 0 ? 'right' : 'left');
                } else {
                    updateCards(); // Resets back to center
                }
                currentX = 0;
                currentY = 0;
            }

            // Mouse Events
            card.addEventListener('mousedown', onStart);
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onEnd);

            // Touch Events for Mobile
            card.addEventListener('touchstart', onStart, {
                passive: true
            });
            document.addEventListener('touchmove', onMove, {
                passive: true
            });
            document.addEventListener('touchend', onEnd);
        }

        // Initialize the first card on load
        if (topCardIndex >= 0) {
            updateCards();
            initDrag(cards[topCardIndex]);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>