<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$activePage = 'signup';
$pageTitle = 'Join S³';

if (!function_exists('h')) {
    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
$old = $_SESSION['old_signup'] ?? [];

$interests = [];
$questions = [];
$dbPath = file_exists(__DIR__ . '/../config/db.php') ? __DIR__ . '/../config/db.php' : dirname(__DIR__) . '/config/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->query("SELECT id, name FROM interests ORDER BY name ASC");
            $interests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
        }
        try {
            $stmt = $pdo->query("SELECT qn_id, q_text FROM questions ORDER BY qn_id ASC");
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
        }
    }
}

if (empty($interests)) {
    $dbError = "Could not load interests from the database. Please try again later.";
}

require_once 'includes/header.php';
?>

<main class="container signup-main">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold signup-heading">Join S³ ✨</h1>
                <p class="text-muted">Find your vibe. Find your people.</p>
            </div>

            <div class="card shadow-sm border-0 signup-card">
                <div class="card-body p-4 p-md-5">

                    <!-- Step indicators -->
                    <div class="d-flex justify-content-between position-relative mb-5 px-2">
                        <div class="custom-progress-track">
                            <div class="custom-progress-fill" id="progressBar" style="width: 16%;"></div>
                        </div>
                        <div class="step-indicator active">1</div>
                        <div class="step-indicator">2</div>
                        <div class="step-indicator">3</div>
                        <div class="step-indicator">4</div>
                        <div class="step-indicator">5</div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-4"><?= h($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($dbError)): ?>
                        <div class="alert alert-danger rounded-4"><?= h($dbError) ?></div>
                    <?php endif; ?>

                    <form id="signupForm" action="process_register.php" method="POST" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                        <!-- Step 0: Account Basics -->
                        <div class="form-step active" id="step0">
                            <h4 class="fw-bold mb-4">Account Basics</h4>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">First Name</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="firstName" required>
                                    <div class="invalid-feedback fw-bold">First name is required.</div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Last Name</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="lastName" required>
                                    <div class="invalid-feedback fw-bold">Last name is required.</div>
                                </div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label class="form-label text-muted fw-bold">Username</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="username" required>
                                <div class="invalid-feedback fw-bold">Please choose a username.</div>
                            </div>
                            <div class="mb-3 position-relative">
                                <label class="form-label text-muted fw-bold">SIT Email</label>
                                <input type="email" class="form-control form-control-lg custom-input" name="email" placeholder="@sit.singaporetech.edu.sg" required>
                                <div class="invalid-feedback fw-bold">A valid email is required.</div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Password</label>
                                    <input type="password" id="pw1" class="form-control form-control-lg custom-input" name="password" minlength="8" required>
                                    <div class="invalid-feedback fw-bold">Password must be at least 8 characters.</div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Confirm Password</label>
                                    <input type="password" id="pw2" class="form-control form-control-lg custom-input" name="confirmPassword" required>
                                    <div class="invalid-feedback fw-bold" id="pwMatchError">Passwords do not match.</div>
                                </div>
                            </div>
                            <button type="button" class="btn-solid-custom w-100 py-3 btn-next">Continue &rarr;</button>
                        </div>

                        <div class="form-step d-none" id="step1">
                            <h4 class="fw-bold mb-4">Identity & Details</h4>

                            <div class="mb-3 position-relative">
                                <label class="form-label text-muted fw-bold">Display Name</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="display_name" required>
                                <div class="invalid-feedback fw-bold">Display name is required.</div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4 position-relative">
                                    <label class="form-label text-muted fw-bold">Age</label>
                                    <input type="number" class="form-control form-control-lg custom-input" name="age" min="18" max="99" required>
                                    <div class="invalid-feedback fw-bold">Please enter a valid age (18+).</div>
                                </div>
                                <div class="col-md-4 position-relative">
                                    <label class="form-label text-muted fw-bold">Gender</label>
                                    <select class="custom-select" name="gender" required>
                                        <option value="" disabled selected>Select...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Non-Binary">Non-Binary</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback fw-bold">Please select a gender.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold">Pronouns</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="pronouns" placeholder="e.g. He/Him">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Location</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="location" placeholder="e.g. Punggol">
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Course</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="occupation" placeholder="e.g. InfoSec">
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold">Education</label>
                                    <select class="custom-select" name="education">
                                        <option value="" disabled selected>Select...</option>
                                        <option value="Undergraduate">Undergraduate</option>
                                        <option value="Postgraduate">Postgraduate</option>
                                        <option value="Alumni">Alumni</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold">Height</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg custom-input" name="height" placeholder="e.g. 170" min="90" max="250">
                                        <span class="input-group-text bg-light border-0 text-muted fw-bold px-3" style="border-radius: 0 12px 12px 0;">cm</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="button" class="btn-outline-custom w-50 py-3 btn-prev">&larr; Back</button>
                                <button type="button" class="btn-solid-custom w-50 py-3 btn-next">Continue &rarr;</button>
                            </div>
                        </div>

                        <!-- Step 2: Your Vibe -->
                        <div class="form-step d-none" id="step2">
                            <h4 class="fw-bold mb-4">Your Vibe</h4>
                            <div class="mb-3 position-relative">
                                <label class="form-label text-muted fw-bold">Biography</label>
                                <textarea class="form-control custom-input" name="biography" rows="3" placeholder="Tell us about yourself..." required></textarea>
                                <div class="invalid-feedback fw-bold">A short bio is required.</div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold">Love Language</label>
                                    <select class="custom-select" name="love_language">
                                        <option value="Quality Time">Quality Time</option>
                                        <option value="Physical Touch">Physical Touch</option>
                                        <option value="Receiving Gifts">Receiving Gifts</option>
                                        <option value="Acts of Service">Acts of Service</option>
                                        <option value="Words of Affirmation">Words of Affirmation</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold">Favorite Song</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="favourite_song">
                                </div>
                            </div>

                            <label class="form-label text-muted fw-bold mb-2">Select Interests (Max 5)</label>
                            <div class="interests-scroll d-flex flex-wrap gap-2 mb-4">
                                <?php foreach ($interests as $int): ?>
                                    <div>
                                        <input type="checkbox" class="interest-checkbox" id="int_<?= $int['id'] ?>" name="interests[]" value="<?= $int['id'] ?>">
                                        <label class="interest-label" for="int_<?= $int['id'] ?>"><?= h($int['name']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="button" class="btn-outline-custom w-50 py-3 btn-prev">&larr; Back</button>
                                <button type="button" class="btn-solid-custom w-50 py-3 btn-next">Continue &rarr;</button>
                            </div>
                        </div>

                        <!-- Step 3: Final Details -->
                        <div class="form-step d-none" id="step3">
                            <h4 class="fw-bold mb-4">Final Details</h4>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Looking For</label>
                                    <select class="custom-select" name="looking_for" required>
                                        <option value="" disabled selected>Select...</option>
                                        <option value="A Relationship">A Relationship</option>
                                        <option value="Something Casual">Something Casual</option>
                                        <option value="New Friends">New Friends</option>
                                    </select>
                                    <div class="invalid-feedback fw-bold">Required.</div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Show Me</label>
                                    <select class="custom-select" name="show_me" required>
                                        <option value="" disabled selected>Select...</option>
                                        <option value="Everyone">Everyone</option>
                                        <option value="Women">Women</option>
                                        <option value="Men">Men</option>
                                    </select>
                                    <div class="invalid-feedback fw-bold">Required.</div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Minimum Age</label>
                                    <input type="number" id="ageMin" class="form-control form-control-lg custom-input" name="age_min" min="18" max="99" value="18" required>
                                    <div class="invalid-feedback fw-bold">Must be 18+.</div>
                                </div>
                                <div class="col-md-6 position-relative">
                                    <label class="form-label text-muted fw-bold">Maximum Age</label>
                                    <input type="number" id="ageMax" class="form-control form-control-lg custom-input" name="age_max" min="18" max="99" value="99" required>
                                    <div class="invalid-feedback fw-bold" id="ageMaxError">Must be valid.</div>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-1">Your Photos</h5>
                            <p class="text-muted small mb-3">Add up to 6 photos. The first one is your main profile picture.</p>

                            <div class="photo-upload-grid mb-5">
                                <label class="photo-slot main-slot position-relative">
                                    <input type="file" name="main_image" accept="image/*" class="d-none photo-input" required>
                                    <div class="photo-placeholder">
                                        <span class="photo-plus-icon">+</span><br>Main Photo
                                    </div>
                                    <div class="invalid-feedback fw-bold photo-required-feedback">Required</div>
                                </label>
                                <label class="photo-slot"><input type="file" name="image_2" accept="image/*" class="d-none photo-input">
                                    <div class="photo-placeholder">+</div>
                                </label>
                                <label class="photo-slot"><input type="file" name="image_3" accept="image/*" class="d-none photo-input">
                                    <div class="photo-placeholder">+</div>
                                </label>
                                <label class="photo-slot"><input type="file" name="image_4" accept="image/*" class="d-none photo-input">
                                    <div class="photo-placeholder">+</div>
                                </label>
                                <label class="photo-slot"><input type="file" name="image_5" accept="image/*" class="d-none photo-input">
                                    <div class="photo-placeholder">+</div>
                                </label>
                                <label class="photo-slot"><input type="file" name="image_6" accept="image/*" class="d-none photo-input">
                                    <div class="photo-placeholder">+</div>
                                </label>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="button" class="btn-outline-custom w-50 py-3 btn-prev">&larr; Back</button>
                                <button type="button" class="btn-solid-custom w-50 py-3 btn-next">Continue &rarr;</button>
                            </div>
                        </div>

                        <!-- Step 4: Q&A -->
                        <div class="form-step d-none" id="step4">
                            <h4 class="fw-bold mb-1">Tell Us More About You</h4>
                            <p class="text-muted mb-4">Answer at least <strong>3 questions</strong> (up to 6). These show up on your profile as conversation starters.</p>

                            <div id="qa-answer-count-msg" class="qa-answer-count mb-3">
                                0 / 6 answered
                            </div>

                            <div class="qa-list d-flex flex-column gap-3 mb-5">
                                <?php if (!empty($questions)): ?>
                                    <?php foreach ($questions as $q): ?>
                                        <div class="qa-card" id="qa-card-<?= $q['qn_id'] ?>">
                                            <label class="qa-question" for="ans_<?= $q['qn_id'] ?>">
                                                💬 <?= h($q['q_text']) ?>
                                            </label>
                                            <textarea
                                                class="qa-textarea"
                                                id="ans_<?= $q['qn_id'] ?>"
                                                name="answers[<?= $q['qn_id'] ?>]"
                                                rows="2"
                                                maxlength="300"
                                                placeholder="Your answer..."></textarea>
                                            <div class="qa-char-count" id="char-<?= $q['qn_id'] ?>">0 / 300</div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-danger rounded-4">
                                        Could not load questions from the database. Please refresh the page or try again later.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="button" class="btn-outline-custom w-50 py-3 btn-prev">&larr; Back</button>
                                <button type="submit" class="btn-solid-custom w-50 py-3">Finish Setup ✨</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // ── Custom Dropdown Logic ──
        document.querySelectorAll('.custom-select').forEach(select => {
            select.style.display = 'none';
            const wrapper = document.createElement('div');
            wrapper.className = 'custom-dropdown-wrapper form-control form-control-lg custom-input';

            const display = document.createElement('div');
            display.className = 'custom-dropdown-display';
            display.innerText = select.options[select.selectedIndex]?.text || 'Select...';
            if (select.value === '') display.style.color = '#999';

            const list = document.createElement('div');
            list.className = 'custom-dropdown-list shadow-lg rounded-3';

            Array.from(select.options).forEach((opt, idx) => {
                if (idx === 0 && opt.disabled) return;
                const item = document.createElement('div');
                item.className = 'custom-dropdown-item';
                item.innerText = opt.text;
                item.addEventListener('click', () => {
                    select.value = opt.value;
                    display.innerText = opt.text;
                    display.style.color = 'var(--text-dark)';
                    list.classList.remove('show');
                    wrapper.classList.remove('is-invalid');
                });
                list.appendChild(item);
            });

            display.addEventListener('click', (e) => {
                document.querySelectorAll('.custom-dropdown-list').forEach(l => {
                    if (l !== list) l.classList.remove('show')
                });
                list.classList.toggle('show');
                e.stopPropagation();
            });

            wrapper.appendChild(display);
            wrapper.appendChild(list);
            select.parentNode.insertBefore(wrapper, select.nextSibling);
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('.custom-dropdown-list').forEach(l => l.classList.remove('show'));
        });

        // ── Photo Grid Preview Logic ──
        document.querySelectorAll('.photo-input').forEach(input => {
            input.addEventListener('change', function() {
                const file = this.files[0];
                const slot = this.closest('.photo-slot');
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        slot.style.backgroundImage = `url(${e.target.result})`;
                        slot.querySelector('.photo-placeholder').style.opacity = '0';
                        slot.classList.remove('is-invalid');
                    }
                    reader.readAsDataURL(file);
                }
            });
        });

        // ── Q&A char counter + answered count ──
        const countMsg = document.getElementById('qa-answer-count-msg');

        function updateQaCount() {
            let count = 0;
            document.querySelectorAll('.qa-textarea').forEach(ta => {
                if (ta.value.trim().length > 0) count++;
            });
            if (countMsg) {
                countMsg.textContent = count + ' / 6 answered';
                countMsg.classList.toggle('qa-answer-count--ok', count >= 3);
            }
        }

        document.querySelectorAll('.qa-textarea').forEach(ta => {
            const qid = ta.id.replace('ans_', '');
            const charEl = document.getElementById('char-' + qid);
            const card = ta.closest('.qa-card');

            ta.addEventListener('input', () => {
                const n = ta.value.length;
                if (charEl) {
                    charEl.textContent = n + ' / 300';
                    charEl.classList.toggle('qa-char-count--warn', n > 260);
                }
                card?.classList.toggle('qa-card--answered', ta.value.trim().length > 0);
                updateQaCount();
            });
        });

        // ── Field error helpers ──
        function setFieldError(name, message) {
            const input = document.querySelector(`[name="${name}"]`);
            if (!input) return;
            input.classList.add('is-invalid', 'field-error');

            let fb = input.parentNode.querySelector('.server-feedback');
            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'server-feedback';
                input.parentNode.appendChild(fb);
            }
            fb.textContent = message;
        }

        function clearFieldError(name) {
            const input = document.querySelector(`[name="${name}"]`);
            if (!input) return;
            input.classList.remove('is-invalid', 'field-error');
            const fb = input.parentNode.querySelector('.server-feedback');
            if (fb) fb.remove();
        }

        // ── Step Navigation ──
        const steps = document.querySelectorAll(".form-step");
        const indicators = document.querySelectorAll(".step-indicator");
        const progressBar = document.getElementById("progressBar");
        let currentStep = 0;
        const TOTAL_STEPS = 5;

        function updateView() {
            steps.forEach((step, idx) => {
                if (idx === currentStep) {
                    step.classList.remove("d-none");
                    setTimeout(() => step.classList.add("fade-in-up"), 10);
                } else {
                    step.classList.add("d-none");
                    step.classList.remove("fade-in-up");
                }
            });
            indicators.forEach((ind, idx) => {
                idx <= currentStep ? ind.classList.add("active") : ind.classList.remove("active");
            });
            progressBar.style.width = (currentStep * 25) + "%";
        }

        document.querySelectorAll(".btn-next").forEach(btn => {
            btn.addEventListener("click", async () => {
                const currentFormStep = steps[currentStep];
                const inputs = currentFormStep.querySelectorAll('input[required], textarea[required], select[required]');
                let valid = true;

                inputs.forEach(input => {
                    if (!input.checkValidity() || input.value === "") {
                        input.classList.add('is-invalid');
                        if (input.tagName === 'SELECT') input.nextElementSibling?.classList.add('is-invalid');
                        valid = false;
                    } else {
                        input.classList.remove('is-invalid');
                        if (input.tagName === 'SELECT') input.nextElementSibling?.classList.remove('is-invalid');
                    }
                });

                // Step 0: password match + server-side check
                if (currentStep === 0) {
                    const p1 = document.getElementById('pw1');
                    const p2 = document.getElementById('pw2');
                    const pError = document.getElementById('pwMatchError');
                    if (p1.value !== p2.value || p1.value.length < 8) {
                        p2.classList.add('is-invalid');
                        pError.style.display = 'block';
                        valid = false;
                    } else {
                        p2.classList.remove('is-invalid');
                        pError.style.display = 'none';
                    }

                    if (valid) {
                        btn.disabled = true;
                        btn.textContent = 'Checking...';

                        const formData = new FormData();
                        formData.append('step', '0');
                        formData.append('firstName', document.querySelector('[name="firstName"]').value);
                        formData.append('lastName', document.querySelector('[name="lastName"]').value);
                        formData.append('username', document.querySelector('[name="username"]').value);
                        formData.append('email', document.querySelector('[name="email"]').value);
                        formData.append('password', p1.value);
                        formData.append('confirmPassword', p2.value);

                        try {
                            const res = await fetch('validate_step.php', {
                                method: 'POST',
                                body: formData
                            });
                            const data = await res.json();

                            if (!data.valid) {
                                valid = false;
                                Object.entries(data.errors).forEach(([field, msg]) => setFieldError(field, msg));
                            } else {
                                ['firstName', 'lastName', 'username', 'email', 'password', 'confirmPassword']
                                .forEach(f => clearFieldError(f));
                            }
                        } catch (e) {
                            console.error('Validation request failed:', e);
                        }

                        btn.disabled = false;
                        btn.textContent = 'Continue →';
                    }
                }

                // Step 3: age range check
                if (currentStep === 3) {
                    const ageMin = parseInt(document.getElementById('ageMin').value);
                    const ageMax = parseInt(document.getElementById('ageMax').value);
                    const maxError = document.getElementById('ageMaxError');
                    if (ageMin > ageMax) {
                        document.getElementById('ageMax').classList.add('is-invalid');
                        maxError.innerText = "Max age cannot be lower than Min age.";
                        maxError.style.display = 'block';
                        valid = false;
                    } else {
                        maxError.style.display = 'none';
                    }
                }

                if (valid && currentStep < TOTAL_STEPS - 1) {
                    currentStep++;
                    updateView();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });

        document.querySelectorAll(".btn-prev").forEach(btn => {
            btn.addEventListener("click", () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateView();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // ── Final submit: Q&A min 3 ──
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const textareas = document.querySelectorAll('.qa-textarea');
            let answered = 0;
            textareas.forEach(ta => {
                if (ta.value.trim().length > 0) answered++;
            });

            if (answered < 3) {
                e.preventDefault();
                if (countMsg) {
                    countMsg.textContent = '⚠️ Please answer at least 3 questions (' + answered + ' answered so far)';
                    countMsg.classList.add('qa-answer-count--error');
                    countMsg.classList.remove('qa-answer-count--ok');
                }
                textareas.forEach(ta => {
                    if (ta.value.trim().length === 0) {
                        ta.classList.add('field-error');
                        setTimeout(() => ta.classList.remove('field-error'), 2000);
                    }
                });
                return;
            }

            // Cap at 6
            let count = 0;
            textareas.forEach(ta => {
                if (ta.value.trim().length > 0) {
                    count++;
                    if (count > 6) ta.value = '';
                }
            });
        });

        // ── Interest limit ──
        document.querySelectorAll('.interest-checkbox').forEach(box => {
            box.addEventListener('change', function() {
                if (document.querySelectorAll('.interest-checkbox:checked').length > 5) {
                    this.checked = false;
                    alert("Maximum 5 interests allowed.");
                }
            });
        });

    });
</script>

<?php require_once 'includes/footer.php'; ?>