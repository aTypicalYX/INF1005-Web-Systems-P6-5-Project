<?php
session_start();

$activePage = 'home';
$pageTitle = 'Home';

require_once 'includes/header.php';

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>


    <main class="container d-flex flex-column" style="flex: 1;">
        <?php if ($success !== ''): ?>
            <div class="alert alert-success" role="alert"><?= h($success) ?></div>
        <?php endif; ?>


        <div class="hero-wrapper">
            <div class="hero-text">
                <h1>Swipe Less.<br>Connect More.</h1> 
                <p>No games, no ghosting, just genuine vibes with people who actually get you !<i class="fa-solid fa-heart fa-bounce" style="color: var(--primary-pink);"></i></p>
                
                <!--
                <?php if (!isset($_SESSION['username'])): ?>
                    <a href="signup.php" class="btn-solid-custom" style="padding: 1rem 2.5rem; font-size: 1.1rem;">Join Now &rarr;</a>
                <?php endif; ?>
               
                -->
            </div>

            <div class="hero-visual">
                <div class="card-stack">
                    <div class="profile-card card-back-2"></div>
                    <div class="profile-card card-back-1"></div>
                    <div class="profile-card card-front">
                        <div class="card-info">
                            <h3>Jonathan</h3>
                            <p>Singapore &bull; Developer <em>20</em></p>
                            <div class="tags">
                                <span class="tag">Coffee</span>
                                <span class="tag">Art</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="marquee-section my-5">
            <h2 class="text-center fw-bold mb-4">POPULAR VIBES ON CAMPUS</h2>
            <div class="marquee-container">
                <div class="marquee-content">
                    <span class="marquee-tag">#InformationSecurity</span>
                    <span class="marquee-tag">#Valorant</span>
                    <span class="marquee-tag">#LateNightSupper</span>
                    <span class="marquee-tag">#PunggolCampus</span>
                    <span class="marquee-tag">#Hackathons</span>
                    <span class="marquee-tag">#CoffeeAddicts</span>
                    <span class="marquee-tag">#StudyBuddies</span>
                    <span class="marquee-tag">#MobileLegends</span>
                    <span class="marquee-tag">#InformationSecurity</span>
                    <span class="marquee-tag">#Valorant</span>
                    <span class="marquee-tag">#LateNightSupper</span>
                    <span class="marquee-tag">#PunggolCampus</span>
                    <span class="marquee-tag">#Hackathons</span>
                    <span class="marquee-tag">#CoffeeAddicts</span>
                    <span class="marquee-tag">#StudyBuddies</span>
                    <span class="marquee-tag">#MobileLegends</span>
                </div>
            </div>
        </section>

        <section class="how-it-works my-5 pt-4 pb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold mt-2 display-6" style="color: var(--text-dark);">How S³ works</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-icon" aria-hidden="true"><i class="fa-solid fa-plus" style="color: var(--primary-pink);"></i></div>
                    <h4>Create your profile</h4>
                    <p>Tell us about yourself — your values, interests, what you're looking for, and what makes you unique. The more you share, the better your matches.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-icon" aria-hidden="true"><i class="fa-solid fa-magnifying-glass" style="color: var(--primary-pink);"></i></div>
                    <h4>Discover & Swipe</h4>
                    <p>Browse curated profiles or use our swipe interface. Like, pass, or super-like profiles to signal your interest level clearly.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-icon" aria-hidden="true"><i class="fa-solid fa-comment-dots" style="color: var(--primary-pink);"></i></div>
                    <h4>Connect & Chat</h4>
                    <p>When you both like each other, it's a match! Start a conversation, share more about yourselves, and take things at your own pace.</p>
                </div>
            </div>
        </div>
    </section>


    <section class="trust-banner mt-4 mb-5 pb-4">
        <div class="container text-center px-4 py-5">
            <div class="row g-4">
                
                <div class="col-md-4 px-4">
                    <div class="trust-feature">
                        <div class="trust-icon">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h5 class="fw-bold" style="color: var(--text-dark);">SIT Exclusive</h5>
                        <p class="text-muted small mb-0">Requires a verified <strong>@sit.singaporetech.edu.sg</strong> email address to join the community.</p>
                    </div>
                </div>
                
                <div class="col-md-4 px-4">
                    <div class="trust-feature">
                        <div class="trust-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h5 class="fw-bold" style="color: var(--text-dark);">Zero Creep Policy</h5>
                        <p class="text-muted small mb-0">Robust reporting tools and immediate bans for bad behavior to keep the environment respectful.</p>
                    </div>
                </div>
                
                <div class="col-md-4 px-4">
                    <div class="trust-feature">
                        <div class="trust-icon">
                            <i class="bi bi-chat-dots-fill"></i>
                        </div>
                        <h5 class="fw-bold" style="color: var(--text-dark);">Anti-Ghosting Nudges</h5>
                        <p class="text-muted small mb-0">Smart, gentle reminders to encourage active conversations and real, meaningful connections.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    </main>
 


<?php require_once 'includes/footer.php'; ?>
