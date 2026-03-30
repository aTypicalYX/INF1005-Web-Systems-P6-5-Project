<?php
session_start();

$activePage = 'about';
$pageTitle  = 'About Us';

require_once 'includes/header.php';
?>

<main class="container d-flex flex-column" style="flex: 1;">

    <section class="about-hero">
        <span class="team-badge">INF1005 Project &bull; Group 5</span>
        <h1>Built for SITizens.<br>Designed for <em>real connection.</em><i class="fa-solid fa-heart fa-bounce" style="color: var(--primary-pink);"></i></h1>
        <p class="lead text-muted mx-auto mt-3" style="max-width: 600px;">
            University life is more than just lectures, lab sessions, and chasing deadlines.
            It's about the people you meet along the way.
        </p>
    </section>

    <section class="mb-5 pb-5 mt-4">
        <div class="text-center mb-5 pb-3">
            <span class="badge rounded-pill mb-3 shadow-sm" style="background: rgba(147, 51, 234, 0.1); color: #9333ea; padding: 0.6rem 1.2rem; font-weight: 700; letter-spacing: 0.05em;">
                SITIZEN INSPIRED
            </span>
            <h2 class="fw-bold mt-2" style="color: var(--text-dark); font-size: clamp(2rem, 4vw, 2.5rem); letter-spacing: -1px;">Our S³ DNA <i class="fa-solid fa-dna fa-beat" style="color: #9333ea;"></i></h2>
            <p class="text-muted mx-auto mt-3" style="max-width: 650px; font-size: 1.1rem; line-height: 1.6;">
                We took the core values of the official SITizen DNA and engineered them to build better campus connections.
            </p>
        </div>
        
        <div class="row g-4 px-3">
            <div class="col-lg-4 col-md-6">
                <div class="dna-card">
                    <div class="dna-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <h3 class="fw-bold mb-3 h4" style="color: var(--text-dark);">Grounded in the Community</h3>
                    <p class="mb-0" style="color: #595959; font-size: 0.95rem; line-height: 1.6;">Exclusive to @sit.singaporetech.edu.sg emails. We keep things safe, local, and highly relevant to the SIT student experience.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mt-lg-5">
                <div class="dna-card">
                    <div class="dna-icon"><i class="bi bi-cpu-fill"></i></div>
                    <h3 class="fw-bold mb-3 h4" style="color: var(--text-dark);">Thinking Tinkerers</h3>
                    <p class="mb-0" style="color: #595959; font-size: 0.95rem; line-height: 1.6;">Built by ICT students who understand that the best connections require a smart algorithm and an intuitive, flawless UI.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mx-auto">
                <div class="dna-card">
                    <div class="dna-icon"><i class="bi bi-stars"></i></div>
                    <h3 class="fw-bold mb-3 h4" style="color: var(--text-dark);">Catalysts for Connection</h3>
                    <p class="mb-0" style="color: #595959; font-size: 0.95rem; line-height: 1.6;">Moving beyond the superficial swipe to foster genuine, lasting relationships and professional networks on campus.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="vibe-check-section mb-5 pb-5">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold" style="color: var(--primary-pink); letter-spacing: 1px; font-size: 0.9rem;">The Vibe Check</span>
            <h2 class="fw-bold mt-2" style="color: var(--text-dark);">Our Community Guidelines <i class="fa-solid fa-arrows-down-to-people" style="color: var(--primary-pink);"></i></h2>
            <p class="text-muted mx-auto mt-3" style="max-width: 600px;">To keep S³ a safe and fun space for all SITizens, we ask that everyone follows these three simple rules.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="vibe-card">
                    <div class="vibe-number">01</div>
                    <h3 class="fw-bold mb-3 h4">Bring your real self.</h3>
                    <p class="mb-0">No fake personas, no catfishing, just genuine students. Authenticity is the fastest way to find people who actually match your energy.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="vibe-card">
                    <div class="vibe-number">02</div>
                    <h3 class="fw-bold mb-3 h4">Respect the grind.</h3>
                    <p class="mb-0">We are all students. Understand that midterms happen, project deadlines loom, and replies might be slow. Be patient and kind.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="vibe-card">
                    <div class="vibe-number">03</div>
                    <h3 class="fw-bold mb-3 h4">Shoot your shot, respectfully.</h3>
                    <p class="mb-0">Whether you're looking for a romantic date, a gym buddy, or someone to survive a group project with, always communicate clearly and respectfully.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="security-wrapper">
        <div class="container">
            <div class="text-center mb-5">
                <span class="text-uppercase fw-bold" style="color: var(--primary-pink); letter-spacing: 1px; font-size: 0.9rem;">InfoSec Standard</span>
                <h2 class="fw-bold mt-2 text-white">Your Privacy, Our Priority <i class="fa-solid fa-lock fa-flip" style="color: rgb(255, 255, 255);"></i></h2>
                <p class="mx-auto mt-3" style="max-width: 600px; color: rgba(255,255,255,0.7);">Built by Information Security students, S³ is engineered from the ground up to protect your data and keep the community secure.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="security-card">
                        <span class="status-badge status-live">LIVE</span>
                        <div class="security-icon"><i class="bi bi-building-lock"></i></div>
                        <h4 class="fw-bold mb-2">Campus Walled Garden</h4>
                        <p class="mb-0" style="color: rgba(255,255,255,0.75);">Strict <strong>@sit.singaporetech.edu.sg</strong> email verification keeps outsiders out. Only verified SIT students can access the platform.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="security-card">
                        <span class="status-badge status-live">LIVE</span>
                        <div class="security-icon"><i class="bi bi-file-earmark-lock2"></i></div>
                        <h4 class="fw-bold mb-2">Encrypted Data</h4>
                        <p class="mb-0" style="color: rgba(255,255,255,0.75);">Your passwords are never stored in plain text. We utilize industry-standard bcrypt hashing algorithms to ensure your credentials are fully encrypted and safe.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="security-card">
                        <span class="status-badge status-live">LIVE</span>
                        <div class="security-icon"><i class="bi bi-sliders"></i></div>
                        <h4 class="fw-bold mb-2">Bot & Threat Mitigation</h4>
                        <p class="mb-0" style="color: rgba(255,255,255,0.75);">Our perimeter is secured by Google reCAPTCHA v2 and strict <code class="text-white bg-dark px-1 rounded">@sit.singaporetech.edu.sg</code> email validation, actively defending the community against automated bots, credential stuffing, and external bad actors.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="security-card">
                        <span class="status-badge status-live">LIVE</span>
                        <div class="security-icon"><i class="bi bi-shield-shaded"></i></div>
                        <h4 class="fw-bold mb-2">Continuous Moderation</h4>
                        <p class="mb-0" style="color: rgba(255,255,255,0.75);">We intercept malicious behavior at the source. Real-time profanity filters block inappropriate payloads before database insertion, while our comprehensive Admin Dashboard allows for the swift, permanent banning of hostile accounts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section mb-5 pb-5">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold" style="color: var(--primary-pink); letter-spacing: 1px; font-size: 0.9rem;">FAQ</span>
            <h2 class="fw-bold mt-2" style="color: var(--text-dark);">Myth vs. Reality <i class="fa-solid fa-question fa-beat-fade" style="color: var(--primary-pink);"></i></h2>
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
                        <div id="collapseOne" class="accordion-collapse collapse show" role="region" aria-labelledby="headingOne" data-bs-parent="#mythAccordion">
                            <div class="accordion-body">
                                <strong>Reality:</strong> Nope! While you absolutely can find romance, S³ is heavily used for finding FYP project mates, gym buddies, or fellow gamers to queue up with after class.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <span class="fw-bold">Myth: I have to make all my personal info public.</span>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" role="region" aria-labelledby="headingTwo" data-bs-parent="#mythAccordion">
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
                        <div id="collapseThree" class="accordion-collapse collapse" role="region" aria-labelledby="headingThree" data-bs-parent="#mythAccordion">
                            <div class="accordion-body">
                                <strong>Reality:</strong> That's actually the best part! Knowing you share the exact same vibe before you even bump into them at the library removes the awkwardness and skips straight to the good part.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                <span class="fw-bold">Myth: Everyone is able to sign up for the website.</span>
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" role="region" aria-labelledby="headingFour" data-bs-parent="#mythAccordion">
                            <div class="accordion-body">
                                <strong>Reality:</strong> S³ is exclusive to verified SIT students. We require email verification with a valid <strong>@sit.singaporetech.edu.sg</strong> address to ensure that our community remains safe and genuine.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <?php if (!isset($_SESSION['user_id'])): ?>
    <section class="text-center mb-5 pb-5">
        <h3 class="fw-bold mb-3" style="color:var(--text-dark);">Ready to meet your next coffee buddy?</h3>
        <p class="text-muted mb-4">Join hundreds of other students already on S³.</p>
        <a href="signup.php" class="btn-solid-custom d-inline-block" style="padding:1rem 2.5rem; font-size:1.1rem;">
            Create Your Profile
        </a>
    </section>
    <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>