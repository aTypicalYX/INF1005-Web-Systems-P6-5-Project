<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

require_once '/var/www/config/db.php';
$currentUser = $_SESSION['user_id'];

// ══════════════════════════════════════════════════════
// Form Submission
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName  = $_POST['last_name'] ?? '';
    $age       = $_POST['age'] ?? null;
    $gender    = $_POST['gender'] ?? '';
    $pronouns  = $_POST['pronouns'] ?? '';
    $location  = $_POST['location'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $height    = $_POST['height'] ?? null;
    $bio       = $_POST['biography'] ?? '';
    $education = $_POST['education'] ?? '';
    $loveLanguage = $_POST['love_language'] ?? '';
    $interests = $_POST['interests'] ?? ''; // comma-separated IDs
    $pets      = $_POST['pets'] ?? '';
    $workout   = $_POST['workout'] ?? '';
    $socialMedia = $_POST['social_media'] ?? '';
    $favSong = $_POST['favourite_song'] ?? '';
    $lookingFor  = $_POST['looking_for'] ?? '';
    $showMe      = $_POST['show_me'] ?? '';
    $ageMin      = $_POST['age_min'] ?? null;
    $ageMax      = $_POST['age_max'] ?? null;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $currentUser]);

        $stmt = $pdo->prepare("
            UPDATE profile SET 
                age = ?, gender = ?, pronouns = ?, location = ?, occupation = ?, height = ?, 
                biography = ?, education = ?, love_language = ?, pets = ?, workout = ?, social_media = ?, favourite_song = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$age, $gender, $pronouns, $location, $occupation, $height, $bio, $education, $loveLanguage, $pets, $workout, $socialMedia, $favSong, $currentUser]);

        $stmt = $pdo->prepare("UPDATE preferences SET looking_for = ?, show_me = ?, age_min = ?, age_max = ? WHERE user_id = ?");
        $stmt->execute([$lookingFor, $showMe, $ageMin, $ageMax, $currentUser]);

        $pdo->prepare("DELETE FROM user_interests WHERE user_id = ?")->execute([$currentUser]);

        if ($interests) {
            $interestIds = explode(',', $interests);
            $stmt = $pdo->prepare("INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)");
            foreach ($interestIds as $id) {
                $stmt->execute([$currentUser, $id]);
            }
        }
        
        $pdo->commit();
        $successMessage = "Profile updated successfully!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errorMessage = "Error saving profile: " . $e->getMessage();
    }
}

// ══════════════════════════════════════════════════════
// DB Queries
// ══════════════════════════════════════════════════════
try {
    $stmt = $pdo->query("SELECT id, name FROM interests ORDER BY name");
    $allInterests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allInterests = [];
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.first_name, u.last_name, p.*, pref.looking_for, pref.show_me, pref.age_min, pref.age_max,
            GROUP_CONCAT(i.name ORDER BY i.name SEPARATOR ', ') AS interests
        FROM profile p
        LEFT JOIN users u ON u.id = p.user_id
        LEFT JOIN preferences pref ON pref.user_id = p.user_id
        LEFT JOIN user_interests ui ON ui.user_id = p.user_id
        LEFT JOIN interests i ON i.id = ui.interest_id
        WHERE p.user_id = ?
        GROUP BY p.user_id, u.first_name, u.last_name, pref.looking_for, pref.show_me, pref.age_min, pref.age_max;
    ");
    $stmt->execute([$currentUser]);
    $profile = $stmt->fetchAll(PDO::FETCH_ASSOC)[0] ?? [];
} catch (Exception $e) {
    $profile = [];
}

$activePage = 'profile';
$pageTitle  = h($profile['first_name'] ?? 'Your') . '\'s Profile';
require_once 'includes/header.php';
?>

<main class="container py-4" style="max-width:1200px; flex: 1;">
    
    <div class="ep-header-banner text-center text-md-start d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold mb-1" style="color: var(--text-dark);">Edit Profile ✨</h1>
            <p class="text-muted mb-0">Keep your vibe fresh and your details up to date.</p>
        </div>
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success mb-0 rounded-4 px-4 py-2 border-0 shadow-sm"><i class="bi bi-check-circle-fill"></i> <?= h($successMessage) ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger mb-0 rounded-4 px-4 py-2 border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i> <?= h($errorMessage) ?></div>
        <?php endif; ?>
    </div>

    <div class="row g-4 g-lg-5">

        <div class="col-lg-7">
            <form method="POST" id="editProfileForm">

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center gap-2" style="color: var(--primary-pink);">
                            <i class="bi bi-person-vcard fs-3"></i> Core Information
                        </h4>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold">First Name</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="first_name" value="<?= h($profile['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold">Last Name</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="last_name" value="<?= h($profile['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Age</label>
                                <input type="number" class="form-control form-control-lg custom-input" name="age" value="<?= h((string)($profile['age'] ?? '')) ?>" min="18">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Gender</label>
                                <select class="custom-select" name="gender">
                                    <option <?= ($profile['gender'] ?? '')=='Male'?'selected':'' ?>>Male</option>
                                    <option <?= ($profile['gender'] ?? '')=='Female'?'selected':'' ?>>Female</option>
                                    <option <?= ($profile['gender'] ?? '')=='Non-Binary'?'selected':'' ?>>Non-Binary</option>
                                    <option <?= ($profile['gender'] ?? '')=='Other'?'selected':'' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Pronouns</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="pronouns" value="<?= h($profile['pronouns'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label text-muted fw-bold">Location</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="location" value="<?= h($profile['location'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Occupation</label>
                                <input type="text" class="form-control form-control-lg custom-input" name="occupation" value="<?= h($profile['occupation'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted fw-bold">Height (cm)</label>
                                <input type="number" class="form-control form-control-lg custom-input" name="height" value="<?= h((string)($profile['height'] ?? '')) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center gap-2" style="color: var(--primary-pink);">
                            <i class="bi bi-stars fs-3"></i> About Me
                        </h4>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Biography</label>
                            <textarea class="form-control custom-input" name="biography" rows="4"><?= h($profile['biography'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold">Education</label>
                                <select class="custom-select" name="education">
                                    <option <?= ($profile['education'] ?? '')=='Undergraduate'?'selected':'' ?>>Undergraduate</option>
                                    <option <?= ($profile['education'] ?? '')=='Postgraduate'?'selected':'' ?>>Postgraduate</option>
                                    <option <?= ($profile['education'] ?? '')=='Alumni'?'selected':'' ?>>Alumni</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold">Love Language</label>
                                <select class="custom-select" name="love_language">
                                    <option <?= ($profile['love_language'] ?? '')=='Words of Affirmation'?'selected':'' ?>>Words of Affirmation</option>
                                    <option <?= ($profile['love_language'] ?? '')=='Acts of Service'?'selected':'' ?>>Acts of Service</option>
                                    <option <?= ($profile['love_language'] ?? '')=='Receiving Gifts'?'selected':'' ?>>Receiving Gifts</option>
                                    <option <?= ($profile['love_language'] ?? '')=='Quality Time'?'selected':'' ?>>Quality Time</option>
                                    <option <?= ($profile['love_language'] ?? '')=='Physical Touch'?'selected':'' ?>>Physical Touch</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label text-muted fw-bold mb-3">Your Interests (Max 5)</label>
                            <div id="selected-interests" class="mb-3 d-flex flex-wrap">
                                <?php
                                    $tags = explode(',', $profile['interests'] ?? '');
                                    foreach ($tags as $tag):
                                        $trimmed = trim($tag);
                                        if (!$trimmed) continue;
                                        $interestId = '';
                                        foreach ($allInterests as $int) {
                                            if ($int['name'] === $trimmed) { $interestId = $int['id']; break; }
                                        }
                                    ?>
                                    <span class="tag-span" data-id="<?= h((string)$interestId) ?>">
                                        <?= h($trimmed) ?> <i class="bi bi-x-circle-fill remove-tag ms-1"></i>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="interests-hidden" name="interests" value="">
                            <select id="interest-select" class="custom-select">
                                <option value="" selected>+ Add an interest...</option>
                                <?php foreach ($allInterests as $interest): ?>
                                    <option value="<?= $interest['id'] ?>"><?= h($interest['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-4 d-flex align-items-center gap-2" style="color: var(--primary-pink);">
                            <i class="bi bi-cup-hot fs-3"></i> Lifestyle & Matching
                        </h4>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Pets</label>
                                <select class="custom-select" name="pets">
                                    <option <?= ($profile['pets'] ?? '')=='None'?'selected':'' ?>>None</option>
                                    <option <?= ($profile['pets'] ?? '')=='Dog'?'selected':'' ?>>Dog</option>
                                    <option <?= ($profile['pets'] ?? '')=='Cat'?'selected':'' ?>>Cat</option>
                                    <option <?= ($profile['pets'] ?? '')=='Other'?'selected':'' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Workout</label>
                                <select class="custom-select" name="workout">
                                    <option <?= ($profile['workout'] ?? '')=='Never'?'selected':'' ?>>Never</option>
                                    <option <?= ($profile['workout'] ?? '')=='Sometimes'?'selected':'' ?>>Sometimes</option>
                                    <option <?= ($profile['workout'] ?? '')=='Active'?'selected':'' ?>>Active</option>
                                    <option <?= ($profile['workout'] ?? '')=='Very Active'?'selected':'' ?>>Very Active</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold">Social Media</label>
                                <select class="custom-select" name="social_media">
                                    <option <?= ($profile['social_media'] ?? '')=='Never'?'selected':'' ?>>Never</option>
                                    <option <?= ($profile['social_media'] ?? '')=='Sometimes'?'selected':'' ?>>Sometimes</option>
                                    <option <?= ($profile['social_media'] ?? '')=='Very Active'?'selected':'' ?>>Very Active</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Favourite Song / Artist</label>
                            <input type="text" class="form-control form-control-lg custom-input" name="favourite_song" value="<?= h($profile['favourite_song'] ?? '') ?>">
                        </div>

                        <hr class="my-4" style="border-color: rgba(0,0,0,0.1);">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold">Looking For</label>
                                <select class="custom-select" name="looking_for">
                                    <option <?= ($profile['looking_for'] ?? '')=='Something Casual'?'selected':'' ?>>Something Casual</option>
                                    <option <?= ($profile['looking_for'] ?? '')=='A Relationship'?'selected':'' ?>>A Relationship</option>
                                    <option <?= ($profile['looking_for'] ?? '')=='New Friends'?'selected':'' ?>>New Friends</option>
                                    <option <?= ($profile['looking_for'] ?? '')=='Not Sure Yet'?'selected':'' ?>>Not Sure Yet</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold">Show Me</label>
                                <select class="custom-select" name="show_me">
                                    <option <?= ($profile['show_me'] ?? '')=='Male'?'selected':'' ?>>Men</option>
                                    <option <?= ($profile['show_me'] ?? '')=='Female'?'selected':'' ?>>Women</option>
                                    <option <?= ($profile['show_me'] ?? '')=='Everyone'?'selected':'' ?>>Everyone</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-6">
                                <label class="form-label text-muted fw-bold">Min Age</label>
                                <input type="number" class="form-control form-control-lg custom-input" name="age_min" value="<?= h((string)($profile['age_min'] ?? '18')) ?>" min="18">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted fw-bold">Max Age</label>
                                <input type="number" class="form-control form-control-lg custom-input" name="age_max" value="<?= h((string)($profile['age_max'] ?? '99')) ?>" max="99">
                            </div>
                        </div>

                    </div>
                </div>
                
                <button type="submit" class="btn-solid-custom w-100 py-3 mb-5 fs-5 shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-floppy-fill"></i> Save All Changes
                </button>

            </form>
        </div>

        <div class="col-lg-5 d-none d-lg-flex justify-content-center align-items-start">
            <div class="card-stack mt-lg-2" style="width: 100%; max-width: 400px; height: 600px; position: sticky; top: 100px;">
                <div class="profile-card card-back-2"></div>
                <div class="profile-card card-back-1"></div>
                
                <div class="profile-card card-front p-0 border-0 overflow-hidden" 
                     style="<?php if(empty($profile['main_image'])) echo 'background: var(--card-gradient);'; ?>">
                    
                    <?php if (!empty($profile['main_image'])): ?>
                        <div class="card-image" style="background-image: url('images/<?= h($profile['main_image']) ?>');"></div>
                        <div class="card-gradient"></div>
                    <?php endif; ?>

                    <div class="card-body-content w-100 h-100 d-flex flex-column justify-content-end position-relative" style="z-index: 3; padding: 2rem;">
                        <span class="badge bg-light text-dark position-absolute top-0 end-0 m-3 fw-bold shadow-sm" style="font-size: 0.8rem;"><i class="bi bi-eye"></i> Live Preview</span>
                        
                        <h3 class="card-name mb-1" style="color: white; font-size: 2.2rem; font-weight: 800; line-height: 1.1;">
                            <?= h($profile['first_name'] ?? 'Your Name') ?>, <?= h((string)($profile['age'] ?? 'Age')) ?>
                        </h3>
                        
                        <p class="card-meta mb-3" style="color: rgba(255,255,255,0.9); font-size: 0.95rem;">
                            <i class="bi bi-geo-alt-fill text-white"></i> <?= h($profile['location'] ?? 'Location') ?> &bull; <?= h($profile['occupation'] ?? 'Occupation') ?>
                        </p>
                        
                        <div class="card-tags mb-3">
                            <?php 
                            if (!empty($profile['interests'])) {
                                $tags = explode(',', $profile['interests']);
                                foreach (array_slice($tags, 0, 3) as $tag) { 
                                    $trimmed = trim($tag);
                                    if ($trimmed) echo "<span class=\"card-tag\">" . h($trimmed) . "</span>";
                                }
                                if (count($tags) > 3) echo "<span class=\"card-tag\">+" . (count($tags) - 3) . "</span>";
                            } else {
                                echo "<span class=\"card-tag\">Your</span><span class=\"card-tag\">Interests</span>";
                            }
                            ?>
                        </div>
                        
                        <p class="card-bio mb-0" style="color: rgba(255,255,255,0.85); font-size: 0.95rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= h($profile['biography'] ?? 'Your bio will appear here...') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // --- Custom Select Dropdown Logic ---
    document.querySelectorAll('.custom-select').forEach(select => {
        select.style.display = 'none'; // Hide native ugly select
        
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-dropdown-wrapper form-control form-control-lg custom-input';
        // Add minimal bottom margin so it doesn't break layout spacing
        wrapper.style.marginBottom = '0';
        
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
                
                // If it's the interest select, trigger the change event manually!
                if(select.id === 'interest-select') {
                    select.dispatchEvent(new Event('change'));
                    display.innerText = '+ Add an interest...'; // reset display after selection
                }
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

    // --- Interest Tags Logic ---
    const interestSelect = document.getElementById('interest-select');
    const selectedContainer = document.getElementById('selected-interests');
    const hiddenInput = document.getElementById('interests-hidden');
    const MAX_INTERESTS = 5;

    function updateHiddenInterests(){
        const ids = Array.from(selectedContainer.querySelectorAll('.tag-span')).map(tag => tag.dataset.id).filter(Boolean);
        hiddenInput.value = ids.join(',');
    }

    interestSelect.addEventListener('change', () => {
        const currentCount = selectedContainer.querySelectorAll('.tag-span').length;
        if (currentCount >= MAX_INTERESTS) {
            alert("You can select up to 5 interests.");
            interestSelect.value = "";
            return;
        }

        const selectedOption = interestSelect.options[interestSelect.selectedIndex];
        const interestName = selectedOption.text;
        const interestId = interestSelect.value;
        if (!interestId) return;

        const exists = Array.from(selectedContainer.querySelectorAll('.tag-span')).some(tag => tag.dataset.id === interestId);
        if (exists) { interestSelect.value = ""; return; }

        const span = document.createElement('span');
        span.className = 'tag-span';
        span.dataset.id = interestId;
        span.innerHTML = `${interestName} <i class="bi bi-x-circle-fill remove-tag ms-1"></i>`;
        
        selectedContainer.appendChild(span);
        interestSelect.value = "";
        updateHiddenInterests();
    });

    selectedContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-tag')) {
            e.target.parentElement.remove();
            updateHiddenInterests();
        }
    });

    updateHiddenInterests();
</script>

<?php require_once 'includes/footer.php'; ?>