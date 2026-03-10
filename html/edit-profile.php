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

// ══════════════════════════════════════════════════════
// Form Submission
// ══════════════════════════════════════════════════════
// ── Getting List of Interests ──

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
    $topArtists  = $_POST['top_artists'] ?? '';
    $lookingFor  = $_POST['looking_for'] ?? '';
    $showMe      = $_POST['show_me'] ?? '';
    $ageMin      = $_POST['age_min'] ?? null;
    $ageMax      = $_POST['age_max'] ?? null;

    try {
        // 1️⃣ Update profile table
        $stmt = $pdo->prepare("
            UPDATE profile SET 
                age = ?, gender = ?, pronouns = ?, location = ?, occupation = ?, height = ?, 
                biography = ?, education = ?, love_language = ?, pets = ?, workout = ?, social_media = ?, favourite_song = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$age, $gender, $pronouns, $location, $occupation, $height, $bio, $education, $loveLanguage, $pets, $workout, $socialMedia, $topArtists, $currentUser]);

        // 2️⃣ Update preferences table
        $stmt = $pdo->prepare("
            UPDATE preferences SET 
                looking_for = ?, show_me = ?, age_min = ?, age_max = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$lookingFor, $showMe, $ageMin, $ageMax, $currentUser]);

        // 3️⃣ Update interests (delete old ones first)
        $pdo->prepare("DELETE FROM user_interests WHERE user_id = ?")->execute([$currentUser]);

        if ($interests) {
            $interestIds = explode(',', $interests);
            $stmt = $pdo->prepare("INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)");
            foreach ($interestIds as $id) {
                $stmt->execute([$currentUser, $id]);
            }
        }

        $successMessage = "Profile updated successfully!";
    } catch (Exception $e) {
        $errorMessage = "Error saving profile: " . $e->getMessage();
    }
}

// ══════════════════════════════════════════════════════
// DB Queries
// ══════════════════════════════════════════════════════
// ── Getting List of Interests ──

require_once '/var/www/config/db.php';
try {
    $stmt = $pdo->query("SELECT id, name FROM interests ORDER BY name");
    $allInterests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allInterests = [];
}


// ── Fetch profiles from DB ──
//$currentUser = $_SESSION['user_id'];
$currentUser = 11; // Jonathan Profile
try {
    $stmt = $pdo->prepare("
        SELECT 
            u.first_name,
            u.last_name,
            p.*,
            pref.looking_for,
            pref.show_me,
            pref.age_min,
            pref.age_max,
            GROUP_CONCAT(i.name ORDER BY i.name SEPARATOR ', ') AS interests
        FROM profile p

        LEFT JOIN users u
            ON u.id = p.user_id

        LEFT JOIN preferences pref 
            ON pref.user_id = p.user_id

        LEFT JOIN user_interests ui 
            ON ui.user_id = p.user_id

        LEFT JOIN interests i 
            ON i.id = ui.interest_id

        WHERE p.user_id = ?
        GROUP BY p.user_id, u.first_name, u.last_name, pref.looking_for, pref.show_me, pref.age_min, pref.age_max;
    ");
    $stmt->execute([$currentUser]);
    $profile = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $profile = $profile[0];
    ?>
    <script>
        console.log("Profile:", <?php echo json_encode($profile); ?>);
    </script>
    <?php
} catch (Exception $e) {
    $profile = [];
}

$activePage = 'discover';
$pageTitle  = h($profile['first_name']) . '\'s Profile';
require_once 'includes/header.php';
?>

    <main class="container py-5" style="max-width:1100px;">
        <h1 class="mb-4">Edit Your Profile</h1>

        <div class="row g-5">

            <!-- ═══════ FORM ═══════ -->
            <div class="col-lg-6">
                <form method="POST" enctype="multipart/form-data">

                    <!-- Core Information Fields -->
                    <div class="card border-0" style="border-radius: 20px;">

                        <div class="card-body">
                            <h2 class="text-start mb-4 fw-bold">Core Information</h2>

                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name"
                                            value="<?= h($profile['first_name']) ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name"
                                            value="<?= h($profile['last_name']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" id="age"
                                    value="<?= h((string)$profile['age']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-control" id="gender">
                                    <option <?= $profile['gender']=='Male'?'selected':'' ?>>Male</option>
                                    <option <?= $profile['gender']=='Female'?'selected':'' ?>>Female</option>
                                    <option <?= $profile['gender']=='Other'?'selected':'' ?>>Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pronouns</label>
                                <input type="text" class="form-control" id="pronouns"
                                    value="<?= h($profile['pronouns']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" id="location"
                                    value="<?= h($profile['location']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" id="occupation"
                                    value="<?= h($profile['occupation']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" class="form-control" id="height"
                                    value="<?= h($profile['height']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- About Me Information Fields -->
                    <div class="card border-0" style="border-radius: 20px;">

                        <div class="card-body">
                            <h2 class="text-start mb-4 fw-bold">About Me</h2>

                             <div class="mb-3">
                                <label class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" rows="4"><?= h($profile['biography']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Education</label>
                                <input type="text" class="form-control" id="education"
                                    value="<?= h($profile['education']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Love Language</label>
                                <select class="form-control" id="love_language">
                                    <option <?= $profile['love_language']=='Words of Affirmation'?'selected':'' ?>>Words of Affirmation</option>
                                    <option <?= $profile['love_language']=='Acts of Service'?'selected':'' ?>>Acts of Service</option>
                                    <option <?= $profile['love_language']=='Receiving Gifts'?'selected':'' ?>>Receiving Gifts</option>
                                    <option <?= $profile['love_language']=='Quality Time'?'selected':'' ?>>Quality Time</option>
                                    <option <?= $profile['love_language']=='Physical Touch'?'selected':'' ?>>Physical Touch</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Interests</label>

                                <!-- Selected interests -->
                                <div id="selected-interests" class="interests-input-container mb-2">
                                    <?php
                                    $tags = explode(',', $profile['interests']);
                                    foreach ($tags as $tag):
                                        $trimmed = trim($tag);
                                        if (!$trimmed) continue;
                                    ?>
                                        <span class="tag-span">
                                            <?= h($trimmed) ?>
                                            <span class="remove-tag" aria-label="Remove tag">×</span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Dropdown of all interests -->
                                <select id="interest-select" class="form-control">
                                    <option value="">Add an interest...</option>

                                    <?php foreach ($allInterests as $interest): ?>
                                        <option value="<?= $interest['id'] ?>">
                                            <?= h($interest['name']) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        
                        </div>
                    </div>

                    <!-- Lifestyle Fields -->
                    <div class="card border-0" style="border-radius: 20px;">
                        <div class="card-body">
                            <h2 class="text-start mb-4 fw-bold">Lifestyle</h2>

                            <div class="mb-3">
                                <label class="form-label">Pets</label>
                                <select class="form-control" id="pets">
                                    <option <?= $profile['pets']=='None'?'selected':'' ?>>None</option>
                                    <option <?= $profile['pets']=='Dog'?'selected':'' ?>>Dog</option>
                                    <option <?= $profile['pets']=='Cat'?'selected':'' ?>>Cat</option>
                                    <option <?= $profile['pets']=='Other'?'selected':'' ?>>Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Workout</label>
                                <select class="form-control" id="workout">
                                    <option <?= $profile['workout']=='Never'?'selected':'' ?>>Never</option>
                                    <option <?= $profile['workout']=='Sometimes'?'selected':'' ?>>Sometimes</option>
                                    <option <?= $profile['workout']=='Regularly'?'selected':'' ?>>Regularly</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Social Media Usage</label>
                                <select class="form-control" id="social_media">
                                    <option <?= $profile['social_media']=='Rarely'?'selected':'' ?>>Rarely</option>
                                    <option <?= $profile['social_media']=='Sometimes'?'selected':'' ?>>Sometimes</option>
                                    <option <?= $profile['social_media']=='Very Active'?'selected':'' ?>>Very Active</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Top Artists</label>
                                <input type="text" class="form-control" id="favourite_song"
                                    value="<?= h($profile['favourite_song']) ?>">
                            </div>

                        </div>
                    </div>

                    <!-- Preference Fields -->
                    <div class="card border-0" style="border-radius: 20px;">

                        <div class="card-body">
                            <h2 class="text-start mb-4 fw-bold">Preferences</h2>

                            <div class="mb-3">
                                <label class="form-label">Looking For</label>
                                <select class="form-control" id="looking_for">
                                    <option <?= $profile['looking_for']=='Something Casual'?'selected':'' ?>>Something Casual</option>
                                    <option <?= $profile['looking_for']=='Relationship'?'selected':'' ?>>A Relationship</option>
                                    <option <?= $profile['looking_for']=='Open'?'selected':'' ?>>Open to Anything</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Show Me</label>
                                <select class="form-control" id="show_me">
                                    <option <?= $profile['show_me']=='Male'?'selected':'' ?>>Male</option>
                                    <option <?= $profile['show_me']=='Female'?'selected':'' ?>>Female</option>
                                    <option <?= $profile['show_me']=='Everyone'?'selected':'' ?>>Everyone</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Age Range</label>

                                <div class="row">
                                    <div class="col">
                                        <input type="number" class="form-control"
                                            id="age_min"
                                            placeholder="Min"
                                            value="<?= h($profile['age_min']) ?>">
                                    </div>

                                    <div class="col">
                                        <input type="number" class="form-control"
                                            id="age_max"
                                            placeholder="Max"
                                            value="<?= h($profile['age_max']) ?>">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    
                    <button type="submit" class="btn px-4">
                        Save Changes
                    </button>

                </form>
            </div>

            <!-- ═══════ LIVE PREVIEW ═══════ -->
            <div class="card-profile-preview col-lg-6">
                <div class="profile-card card-front">
                    <div class="card-info">
                        <h3><?= h($profile['first_name']) ?>, <?= h((string)$profile['age']) ?></h3>
                        <p><?= h($profile['location']) ?> &bull; <?= h($profile['occupation']) ?> </p>
                        <div class="tags">
                            <span class="tag">Coffee</span>
                            <span class="tag">Art</span>
                        </div>
                        <p><?= h($profile['biography']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const interestSelect = document.getElementById('interest-select');
        const selectedContainer = document.getElementById('selected-interests');
        const hiddenInput = document.getElementById('interests-hidden');

        const MAX_INTERESTS = 5;

        function updateHiddenInterests(){
            const ids = Array.from(selectedContainer.querySelectorAll('.tag-span'))
                .map(tag => tag.dataset.id)
                .filter(Boolean);

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

            // prevent duplicates
            const exists = Array.from(selectedContainer.querySelectorAll('.tag-span'))
                .some(tag => tag.dataset.id === interestId);

            if (exists) {
                interestSelect.value = "";
                return;
            }

            const span = document.createElement('span');
            span.className = 'tag-span';
            span.dataset.id = interestId;

            span.innerHTML = `${interestName} <span class="remove-tag">×</span>`;

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
    </script>

<?php require_once 'includes/footer.php'; ?>