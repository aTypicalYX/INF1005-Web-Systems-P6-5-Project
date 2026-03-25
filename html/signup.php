<?php
session_start();
$activePage = 'signup';
$pageTitle = 'Join S³';

if (!function_exists('h')) {
    function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
$old = $_SESSION['old_signup'] ?? [];

$interests = [];
$displayQuestions = []; // initialise an array for displaying questions
$dbPath = file_exists(__DIR__ . '/../config/db.php') ? __DIR__ . '/../config/db.php' : dirname(__DIR__) . '/config/db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->query("SELECT id, name FROM interests ORDER BY name ASC");
            $interests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch 3 random Questions
            $qStmt = $pdo->query("SELECT id, question_text FROM questions ORDER BY RAND() LIMIT 3");
            $displayQuestions = $qStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {} 
    }
}

// Fallback if DB fails (Localhost)
if (empty($interests)) {
    $fallbackNames = ["Gaming","Hiking","Traveling","Photography","Music","Concerts","Cooking","Baking","Gym","Running","Yoga","Cycling","Swimming","Basketball","Football","Badminton","Tennis","Volleyball","Movies","Anime","K-Dramas","Netflix","Reading","Writing","Art","Technology","Programming","Coffee","Tea","Wine","Pets","Dogs","Cats"];
    foreach ($fallbackNames as $index => $name) {
        $interests[] = ['id' => $index + 1, 'name' => $name];
    }
}
// Fallback for questions if DB fails
if (empty($displayQuestions)) {
    $displayQuestions = [
        ['id'=> 1, 'question_text' => "My secret talent is..."], // Question 1
        ['id' => 2, 'question_text' => "The quickest way to my heart is..."], // Question 2
        ['id' => 3, 'question_text' => "We'll get along if..."] // Question 3
    ];
}
require_once 'includes/header.php';
?>

<main class="container my-5 d-flex flex-column justify-content-center" style="flex: 1;">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="text-center mb-4">
                <h1 class="display-6 fw-bold" style="color: var(--text-dark);">Join S³ ✨</h1>
                <p class="text-muted">Find your vibe. Find your people.</p>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 24px; background: #fff;">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="d-flex justify-content-between position-relative mb-5 px-2">
                        <div class="progress position-absolute w-100" style="height: 4px; top: 50%; transform: translateY(-50%); z-index: 0;">
                            <div class="progress-bar" id="progressBar" style="width: 0%; background-color: var(--primary-pink); transition: width 0.4s ease;"></div>
                        </div>
                        <div class="step-indicator active">1</div>
                        <div class="step-indicator">2</div>
                        <div class="step-indicator">3</div>
                        <div class="step-indicator">4</div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-4"><?= h($error) ?></div>
                    <?php endif; ?>

                    <form id="signupForm" action="process_register.php" method="POST" enctype="multipart/form-data" novalidate>
                        
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
                            <div class="row g-3 mb-4">
                                <div class="col-md-4 position-relative">
                                    <label class="form-label text-muted fw-bold">Location</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="location" placeholder="e.g. Punggol">
                                </div>
                                <div class="col-md-4 position-relative">
                                    <label class="form-label text-muted fw-bold">Course</label>
                                    <input type="text" class="form-control form-control-lg custom-input" name="occupation" placeholder="e.g. InfoSec">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold">Education</label>
                                    <select class="custom-select" name="education">
                                        <option value="" disabled selected>Select...</option>
                                        <option value="Undergraduate">Undergraduate</option>
                                        <option value="Postgraduate">Postgraduate</option>
                                        <option value="Alumni">Alumni</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="button" class="btn-outline-custom w-50 py-3 btn-prev">&larr; Back</button>
                                <button type="button" class="btn-solid-custom w-50 py-3 btn-next">Continue &rarr;</button>
                            </div>
                        </div>

                        <div class="form-step d-none" id="step2">
                            <h4 class="fw-bold mb-4">Your Vibe</h4>
                            <div class="mb-3 position-relative">
                                <label class="form-label text-muted fw-bold">Biography</label>
                                <textarea class="form-control custom-input" name="biography" rows="3" placeholder="Tell us about yourself..." required></textarea>
                                <div class="invalid-feedback fw-bold">A short bio is required.</div>
                            </div>

                    <!--PARKING SELECTION OF QUESTIONS BELOW-->
                    <div class="mb-4">
                                <h5 class="fw-bold mb-3">Profile Prompts</h5>
                                <?php foreach ($displayQuestions as $index => $q): ?>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">
                                            <?= h($q['question_text']) ?>
                                        </label>
                                        <input type="hidden" name="question_id_<?= $index ?>" value="<?= $q['id'] ?>">
                                        <textarea 
                                            class="form-control custom-input" 
                                            name="security_answers[<?= $q['id'] ?>]" 
                                            rows="2" 
                                            placeholder="Type your answer here..." 
                                            required></textarea>
                                        <div class="invalid-feedback fw-bold">Please answer this prompt!</div>
                                    </div>
                                <?php endforeach; ?>
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
                            <div class="d-flex flex-wrap gap-2 mb-4" style="max-height: 200px; overflow-y: auto;">
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
                                        <option value="Female">Female</option>
                                        <option value="Male">Male</option>
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
                                    <div class="photo-placeholder"><span style="font-size: 2rem;">+</span><br>Main Photo</div>
                                    <div class="invalid-feedback fw-bold position-absolute bottom-0 text-center w-100 mb-1" style="background: rgba(255,255,255,0.8);">Required</div>
                                </label>
                                <label class="photo-slot"><input type="file" name="image_2" accept="image/*" class="d-none photo-input"><div class="photo-placeholder">+</div></label>
                                <label class="photo-slot"><input type="file" name="image_3" accept="image/*" class="d-none photo-input"><div class="photo-placeholder">+</div></label>
                                <label class="photo-slot"><input type="file" name="image_4" accept="image/*" class="d-none photo-input"><div class="photo-placeholder">+</div></label>
                                <label class="photo-slot"><input type="file" name="image_5" accept="image/*" class="d-none photo-input"><div class="photo-placeholder">+</div></label>
                                <label class="photo-slot"><input type="file" name="image_6" accept="image/*" class="d-none photo-input"><div class="photo-placeholder">+</div></label>
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
    // Custom Dropdown Logic
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
            if(idx === 0 && opt.disabled) return;
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
            document.querySelectorAll('.custom-dropdown-list').forEach(l => { if(l !== list) l.classList.remove('show') });
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

    // Photo Grid Preview Logic
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

    // Step Navigation & Validation
    const steps = document.querySelectorAll(".form-step");
    const indicators = document.querySelectorAll(".step-indicator");
    const progressBar = document.getElementById("progressBar");
    let currentStep = 0;

    function updateView() {
        steps.forEach((step, idx) => {
            if(idx === currentStep) {
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
        progressBar.style.width = (currentStep * 33.33) + "%";
    }

    document.querySelectorAll(".btn-next").forEach(btn => {
        btn.addEventListener("click", () => {
            const currentFormStep = steps[currentStep];
            const inputs = currentFormStep.querySelectorAll('input[required], textarea[required], select[required]');
            let valid = true;

            inputs.forEach(input => {
                if (!input.checkValidity() || input.value === "") {
                    input.classList.add('is-invalid');
                    if(input.tagName === 'SELECT') input.nextElementSibling?.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                    if(input.tagName === 'SELECT') input.nextElementSibling?.classList.remove('is-invalid');
                }
            });

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
            }

            if (valid && currentStep < 3) { currentStep++; updateView(); }
        });
    });

    document.querySelectorAll(".btn-prev").forEach(btn => {
        btn.addEventListener("click", () => { if (currentStep > 0) { currentStep--; updateView(); } });
    });

    // Final Form Submit Validation (Step 4)
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        const inputs = steps[3].querySelectorAll('input[required], select[required]');
        let valid = true;

        inputs.forEach(input => {
            if (!input.checkValidity() || input.value === "") {
                input.classList.add('is-invalid');
                if(input.tagName === 'SELECT') input.nextElementSibling?.classList.add('is-invalid');
                if(input.type === 'file') input.closest('.photo-slot')?.classList.add('is-invalid');
                valid = false;
            } else {
                input.classList.remove('is-invalid');
                if(input.tagName === 'SELECT') input.nextElementSibling?.classList.remove('is-invalid');
                if(input.type === 'file') input.closest('.photo-slot')?.classList.remove('is-invalid');
            }
        });

        // Min/Max Age Logic Check
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

        if (!valid) {
            e.preventDefault(); // Stop form submission if there are errors
        }
    });

    // Interest limit
    document.querySelectorAll('.interest-checkbox').forEach(box => {
        box.addEventListener('change', function() {
            if(document.querySelectorAll('.interest-checkbox:checked').length > 5) {
                this.checked = false;
                alert("Maximum 5 interests allowed.");
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>