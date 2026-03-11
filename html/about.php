<?php
session_start();

// Set the active page and title for the header
$activePage = 'about';
$pageTitle = 'About Us';

require_once 'includes/header.php';
?>


<main class="container d-flex flex-column" style="flex: 1;">

    <section class="about-hero">
        <span class="team-badge">INF1005 Project • Group 5</span>
        <h1>Built for SITizens.<br>Designed for <em>real connection.</em><i class="fa-solid fa-heart fa-bounce" style="color: var(--primary-pink);"></i></h1>
        <p class="lead text-muted mx-auto mt-3" style="max-width: 600px;">
            University life is more than just lectures, lab sessions, and chasing deadlines. It's about the people you meet along the way.
        </p>
    </section>

    <section class="mission-section">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4" style="font-size: 2.5rem; color: var(--text-dark);">Why we created S³</h2>
                <p class="fs-5 text-muted mb-3" style="line-height: 1.6;">
                    Singapore Singles Society (S³) was born out of a simple realization: finding your tribe on campus shouldn't be difficult. 
                </p>
                <p class="text-muted" style="line-height: 1.7;">
                    Whether you are an ICT-IS student frantically creating cheat sheets for Prof Wing Keong's networking quizzes, or an ICT-SE student trying to not re-mod Prof David's Operating Systems module, we've built a space exclusively for you. No pressure, no ghosting—just genuine vibes.
                </p>
            </div>
            <div class="col-lg-5 offset-lg-1 text-center">
                <div style="width: 100%; aspect-ratio: 1/1; border-radius: 30px; background: var(--card-gradient); display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2); transform: rotate(3deg);">
                    <div class="text-white text-center p-4">
                        <h3 class="fw-bold mb-0" style="font-size: 3rem;">S³</h3>
                        <p class="mb-0 opacity-75">Find your vibe.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5 pb-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: var(--text-dark);">Our Core Pillars</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Safe & Exclusive</h4>
                    <p class="text-muted mb-0">
                        Tailored specifically for the campus community. We prioritize a secure, respectful environment where you can truly be yourself without worry.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon" style="background: #3B0045;">
                        <i class="bi bi-controller"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Vibe-First Matching</h4>
                    <p class="text-muted mb-0">
                        We care more about your shared interests than just your photos. Connect over favorite games, study spots, or coffee preferences.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="value-icon" style="background: #7A004B;">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Zero Pressure</h4>
                    <p class="text-muted mb-0">
                        Looking for romance? Great. Just want a friendly companion to survive project week with? Also great. Define what you're looking for upfront.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="vibe-check-section mb-5 pb-5">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold" style="color: var(--primary-pink); letter-spacing: 1px; font-size: 0.9rem;">The Vibe Check</span>
            <h2 class="fw-bold mt-2" style="color: var(--text-dark);">Our Community Guidelines</h2>
            <p class="text-muted mx-auto mt-3" style="max-width: 600px;">To keep S³ a safe and fun space for all SITizens, we ask that everyone follows these three simple rules.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="vibe-card">
                    <div class="vibe-number">01</div>
                    <h4 class="fw-bold mb-3">Bring your real self.</h4>
                    <p class="mb-0">No fake personas, no catfishing, just genuine students. Authenticity is the fastest way to find people who actually match your energy.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="vibe-card">
                    <div class="vibe-number">02</div>
                    <h4 class="fw-bold mb-3">Respect the grind.</h4>
                    <p class="mb-0">We are all students. Understand that midterms happen, project deadlines loom, and replies might be slow. Be patient and kind.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="vibe-card">
                    <div class="vibe-number">03</div>
                    <h4 class="fw-bold mb-3">Shoot your shot, respectfully.</h4>
                    <p class="mb-0">Whether you're looking for a romantic date, a gym buddy, or someone to survive a group project with, always communicate clearly and respectfully.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section mb-5 pb-5">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold" style="color: var(--primary-pink); letter-spacing: 1px; font-size: 0.9rem;">FAQ</span>
            <h2 class="fw-bold mt-2" style="color: var(--text-dark);">Myth vs. Reality</h2>
            <p class="text-muted mx-auto mt-3" style="max-width: 600px;">Still on the fence? Let's clear up some common misconceptions about joining the S³ community.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="accordion custom-accordion" id="mythAccordion">
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <span class="fw-bold">Myth: It's only for dating.</span>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#mythAccordion">
                            <div class="accordion-body">
                                <strong>Reality:</strong> Nope! While you absolutely can find romance, S³ can also be used for finding FYP project mates, cafe buddies, or people to game with at night.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <span class="fw-bold">Myth: I have to make all my personal info public.</span>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#mythAccordion">
                            <div class="accordion-body">
                                <strong>Reality:</strong> You are in complete control of your vibe. Share as much or as little as you want using our custom tag system. We only show what you explicitly choose to feature on your profile.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                <span class="fw-bold">Myth: It's going to be awkward if I see them on campus.</span>
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#mythAccordion">
                            <div class="accordion-body">
                                <strong>Reality:</strong> That's actually the best part! Knowing you share the exact same vibe or interests before you even bump into them at the library removes the awkwardness and skips straight to the good part.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>



    <?php if (!isset($_SESSION['user_id'])): ?>
    <section class="text-center mb-5 pb-5">
        <h3 class="fw-bold mb-3" style="color: var(--text-dark);">Ready to meet your next coffee buddy?</h3>
        <p class="text-muted mb-4">Join hundreds of other students already on S³.</p>
        <a href="signup.php" class="btn-solid-custom d-inline-block" style="padding: 1rem 2.5rem; font-size: 1.1rem;">Create Your Profile</a>
    </section>
    <?php endif; ?>

</main>

<?php
require_once 'includes/footer.php';
?>