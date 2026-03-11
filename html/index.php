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
                <p>No games, no ghosting, just genuine vibes with people who actually get you<i class="fa-solid fa-heart fa-bounce" style="color: var(--primary-pink);"></i></p>
                <?php if (!isset($_SESSION['username'])): ?>
                    <a href="signup.php" class="btn-solid-custom" style="padding: 1rem 2.5rem; font-size: 1.1rem;">Join Now &rarr;</a>
                <?php endif; ?>
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
            <p class="text-center text-muted fw-bold mb-4" style="font-size: 1.25rem; letter-spacing: 1px;">POPULAR VIBES ON CAMPUS</p>
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


    </main>
 


   <footer class="custom-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <a href="index.php" class="brand-logo d-inline-block mb-3">S³</a>
                    <p class="text-muted pe-md-5">A safe, fun space to meet people who share your vibe. Based in Singapore.</p>
                </div>
                
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5 class="footer-heading">Explore</h5>
                    <a href="#" class="footer-link">Browse Profiles</a>
                    <a href="#" class="footer-link">Pricing</a>
                    <a href="login.php" class="footer-link">Sign In</a>
                </div>
                
                <div class="col-md-2 col-6 mb-4 mb-md-0">
                    <h5 class="footer-heading">Company</h5>
                    <a href="about.php" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Blog</a>
                    <a href="#" class="footer-link">Careers</a>
                </div>
                
                <div class="col-md-3 col-12">
                    <h5 class="footer-heading">Support</h5>
                    <a href="#" class="footer-link">Safety Centre</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                </div>
            </div>
            
            <hr class="footer-divider">
            
            <div class="text-center text-muted" style="font-size: 0.9rem;">
                <p class="mb-0">&copy; 2026 Singapore Singles Society S³. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
