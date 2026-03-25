<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

// Get reportedId from URL and reporterId from Session 
$reportedId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$reporterId = (int) $_SESSION['user_id'];

// Make sure you cannot report yourself
if ($reportedId === 0 || $reportedId === $reporterId) {
    header('Location: profiles.php');
    exit();
}

// Fetch the reported user's profile
$reportedUser = null;
try {
    $stmt = $pdo->prepare("
        SELECT u.id,
               COALESCE(NULLIF(p.display_name, ''), u.first_name) AS name,
               p.main_image AS image
        FROM users u
        JOIN profile p ON p.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$reportedId]);
    $reportedUser = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $reportedUser = null;
}

if (!$reportedUser) {
    header('Location: profiles.php');
    exit();
}

// Check if this reporter already has a pending report against this user
$alreadyReported = false;
try {
    $checkStmt = $pdo->prepare("
        SELECT id FROM reports
        WHERE reporter_id = ? AND reported_id = ? AND status = 'pending'
        LIMIT 1
    ");
    $checkStmt->execute([$reporterId, $reportedId]);
    $alreadyReported = (bool) $checkStmt->fetch();
} catch (Exception $e) {
    // If check fails, allow submission
}

// Reason labels
$reasonLabels = [
    'harassment'           => 'Harassment or bullying',
    'fake_profile'         => 'Fake or impersonation profile',
    'inappropriate_content'=> 'Inappropriate content',
    'spam'                 => 'Spam or scam',
    'other'                => 'Other',
];

// Handle flash messages from send_report.php
$flashError = $_SESSION['report_error'] ?? null;
unset($_SESSION['report_error']);

// Image URL helper
function imageUrl(?string $img): ?string {
    if (empty($img)) return null;
    if (str_starts_with($img, 'http')) return $img;
    return 'images/' . $img;
}

$avatarUrl = imageUrl($reportedUser['image']);

$activePage = 'discover';
$pageTitle  = 'Report Profile';
require_once 'includes/header.php';
?>

<div class="report-wrap">

    <a href="profile.php?id=<?= h((string)$reportedId) ?>" class="report-back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to profile
    </a>

    <div class="report-card">

        <!-- Subject banner -->
        <div class="report-subject">
            <?php if ($avatarUrl): ?>
                <img src="<?= h($avatarUrl) ?>"
                     alt="<?= h($reportedUser['name']) ?>"
                     class="report-avatar">
            <?php else: ?>
                <div class="report-avatar-placeholder" aria-hidden="true">
                    <?= h(mb_substr($reportedUser['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="report-subject-info">
                <div class="report-subject-label">Reporting</div>
                <div class="report-subject-name"><?= h($reportedUser['name']) ?></div>
            </div>
        </div>

        <div class="report-body">

            <?php if ($alreadyReported): ?>
                <!-- Already submitted a pending report -->
                <div class="already-reported-box">
                    <div class="already-reported-title">Report already submitted</div>
                    <p class="already-reported-sub">
                        You've already reported this profile and it's currently under review.
                        Our team will look into it shortly.
                    </p>
                </div>
            <?php else: ?>

                <h1 class="report-heading">Report a concern</h1>
                <p class="report-subtext">
                    Help keep our community safe. Your report is confidential and will be reviewed by our team.
                </p>

                <?php if ($flashError): ?>
                    <div class="report-alert report-alert-error" role="alert">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" flex-shrink="0" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <?= h($flashError) ?>
                    </div>
                <?php endif; ?>

                <form action="send_report.php" method="POST" id="reportForm" novalidate>
                    <input type="hidden" name="reported_id" value="<?= h((string)$reportedId) ?>">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))) ?>">

                    <!-- Reason selection -->
                    <div class="reason-grid" role="radiogroup" aria-label="Reason for report" aria-required="true">
                        <?php foreach ($reasonLabels as $value => $label): ?>
                        <div class="reason-option">
                            <input type="radio"
                                   name="reason"
                                   id="reason_<?= h($value) ?>"
                                   value="<?= h($value) ?>"
                                   required>
                            <label class="reason-label" for="reason_<?= h($value) ?>">
                                <span class="reason-radio-dot" aria-hidden="true"></span>
                                <span class="reason-text"><?= h($label) ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Description -->
                    <div class="report-field">
                        <label for="report-description">
                            Additional details <span style="color:var(--text-muted,#888);font-weight:400;">(optional)</span>
                        </label>
                        <textarea
                            id="report-description"
                            name="description"
                            class="report-textarea"
                            maxlength="1000"
                            placeholder="Describe what happened so our team can investigate effectively…"
                            aria-describedby="descCharCount"></textarea>
                        <div class="report-char-count" id="descCharCount" aria-live="polite">0 / 1000</div>
                    </div>

                    <button type="submit" class="report-submit-btn" id="submitBtn" disabled>
                        Submit Report
                    </button>

                    <p class="report-disclaimer">
                        False reports undermine trust in our community. Please only report genuine concerns.
                    </p>
                </form>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('reportForm');
    const radios = document.getElementsByName('reason');
    const textarea = document.getElementById('report-description');
    const charCount = document.getElementById('descCharCount');
    const submitBtn = document.getElementById('submitBtn');

    // Check if any radio button is selected
    function isReasonSelected() {
        for (let i = 0; i < radios.length; i++) {
            if (radios[i].checked) {
                return true;
            }
        }
        return false;
    }

    // Enable/disable submit button
    function checkValidity() {
        if (isReasonSelected()) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    // Listen for radio button changes
    for (let i = 0; i < radios.length; i++) {
        radios[i].addEventListener('change', checkValidity);
    }

    // Character counter
    textarea.addEventListener('input', function () {
        const length = textarea.value.length;
        charCount.textContent = length + ' / 1000';

        if (length > 900) {
            charCount.style.color = '#ef4444';
        } else {
            charCount.style.color = '';
        }
    });

    // Form submission
    form.addEventListener('submit', function (e) {
        if (!isReasonSelected()) {
            e.preventDefault();

            // Scroll to radio buttons
            const grid = document.querySelector('.reason-grid');
            if (grid) {
                grid.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
    });
</script>

<?php require_once 'includes/footer.php'; ?>