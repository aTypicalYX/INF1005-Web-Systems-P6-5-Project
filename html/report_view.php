<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit();
}

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}

$reportId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($reportId === 0) {
    header('Location: reports.php');
    exit();
}

// ── Fetch full report ──
$report = null;
try {
    $stmt = $pdo->prepare("
        SELECT
            r.*,
            ru.id            AS reporter_user_id,
            ru.email         AS reporter_email,
            ru.created_at    AS reporter_joined,
            COALESCE(NULLIF(rp.display_name,''), ru.first_name) AS reporter_name,
            rp.main_image    AS reporter_image,
            du.id            AS reported_user_id,
            du.email         AS reported_email,
            du.created_at    AS reported_joined,
            COALESCE(NULLIF(dp.display_name,''), du.first_name) AS reported_name,
            dp.main_image    AS reported_image,
            au.username      AS reviewed_by_username,
            b.id             AS ban_id,
            b.reason         AS ban_reason,
            b.banned_at
        FROM reports r
        JOIN users ru ON ru.id = r.reporter_id
        LEFT JOIN profile rp ON rp.user_id = r.reporter_id
        JOIN users du ON du.id = r.reported_id
        LEFT JOIN profile dp ON dp.user_id = r.reported_id
        LEFT JOIN users au ON au.id = r.reviewed_by
        LEFT JOIN bans b ON b.user_id = r.reported_id
        WHERE r.id = ?
        LIMIT 1
    ");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

if (!$report) {
    header('Location: reports.php');
    exit();
}

// Count other reports against same user 
$otherReportCount = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE reported_id = ? AND id != ?");
    $stmt->execute([$report['reported_user_id'], $reportId]);
    $otherReportCount = (int)$stmt->fetchColumn();
} catch (Exception $e) {}

// Flash messages
$flashSuccess = $_SESSION['admin_success'] ?? null;
$flashError   = $_SESSION['admin_error']   ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

function imageUrl(?string $img): ?string {
    if (empty($img)) return null;
    if (str_starts_with($img, 'http')) return $img;
    return 'images/' . $img;
}

$reasonLabels = [
    'harassment'            => 'Harassment or bullying',
    'fake_profile'          => 'Fake or impersonation profile',
    'inappropriate_content' => 'Inappropriate content',
    'spam'                  => 'Spam or scam',
    'other'                 => 'Other',
];

$activePage = '';
$pageTitle  = 'Report #' . $reportId;
require_once 'includes/header.php';
?>

<div class="rv-wrap">

    <?php if ($flashSuccess): ?>
        <div class="rv-alert rv-alert-success">
            <i class="bi bi-check-circle-fill"></i> <?= h($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="rv-alert rv-alert-error">
            <i class="bi bi-exclamation-circle-fill"></i> <?= h($flashError) ?>
        </div>
    <?php endif; ?>

    <a href="reports.php" class="rv-back">
        <i class="bi bi-arrow-left"></i> All Reports
    </a>

    <div class="rv-status-bar">
        <h1>Report #<?= $reportId ?></h1>
        <div class="rv-status-meta">
            <span class="rv-badge rv-badge-<?= h($report['status']) ?> rv-badge-lg">
                <?= ucfirst(h($report['status'])) ?>
                <?php if ($report['status'] !== 'pending' && $report['reviewed_by_username']): ?>
                    &middot; reviewed by <?= h($report['reviewed_by_username']) ?>
                    <?php if ($report['reviewed_at']): ?>
                        on <?= date('d M Y', strtotime($report['reviewed_at'])) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </span>
            <span class="rv-filed-date">
                Filed <?= date('d M Y, H:i', strtotime($report['created_at'])) ?>
            </span>
        </div>
    </div>

    <div class="row g-3">

        <!-- Report Details -->
        <div class="col-lg-8">

            <!-- Reason & Description -->
            <div class="rv-card">
                <div class="rv-card-header">Report Details</div>
                <div class="rv-card-body">
                    <div class="rv-detail-section">
                        <div class="rv-label">Reason</div>
                        <span class="rv-badge rv-badge-pending rv-badge-reason">
                            <?= h($reasonLabels[$report['reason']] ?? $report['reason']) ?>
                        </span>
                    </div>
                    <div class="rv-detail-section">
                        <div class="rv-label">Description</div>
                        <?php if (!empty($report['description'])): ?>
                            <p class="rv-desc-box"><?= nl2br(h($report['description'])) ?></p>
                        <?php else: ?>
                            <p class="rv-no-desc">No additional details provided.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Reporter -->
            <div class="rv-card">
                <div class="rv-card-header">Reporter</div>
                <div class="rv-card-body">
                    <div class="rv-user-row">
                        <?php $rImg = imageUrl($report['reporter_image']); ?>
                        <?php if ($rImg): ?>
                            <img src="<?= h($rImg) ?>" class="rv-avatar" alt="">
                        <?php else: ?>
                            <div class="rv-avatar-placeholder">
                                <?= mb_strtoupper(mb_substr($report['reporter_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="rv-user-info">
                            <div class="rv-user-name"><?= h($report['reporter_name']) ?></div>
                            <div class="rv-user-meta">
                                <?= h($report['reporter_email']) ?>
                                &middot; Joined <?= date('d M Y', strtotime($report['reporter_joined'])) ?>
                            </div>
                        </div>
                        <a href="profile.php?id=<?= (int)$report['reporter_user_id'] ?>" target="_blank"
                           class="rv-btn rv-btn-outline rv-btn-sm">
                            View Profile <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reported user -->
            <div class="rv-card">
                <div class="rv-card-header">
                    Reported User
                    <?php if ($otherReportCount > 0): ?>
                        <span class="rv-card-header-warning">
                            +<?= $otherReportCount ?> other report<?= $otherReportCount !== 1 ? 's' : '' ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="rv-card-body">
                    <div class="rv-user-row">
                        <?php $dImg = imageUrl($report['reported_image']); ?>
                        <?php if ($dImg): ?>
                            <img src="<?= h($dImg) ?>" class="rv-avatar" alt="">
                        <?php else: ?>
                            <div class="rv-avatar-placeholder">
                                <?= mb_strtoupper(mb_substr($report['reported_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="rv-user-info">
                            <div class="rv-user-name rv-user-name-row">
                                <?= h($report['reported_name']) ?>
                                <?php if ($report['ban_id']): ?>
                                    <span class="rv-badge rv-badge-banned">Banned</span>
                                <?php endif; ?>
                            </div>
                            <div class="rv-user-meta">
                                <?= h($report['reported_email']) ?>
                                &middot; Joined <?= date('d M Y', strtotime($report['reported_joined'])) ?>
                            </div>
                        </div>
                        <a href="profile.php?id=<?= (int)$report['reported_user_id'] ?>" target="_blank"
                           class="rv-btn rv-btn-outline rv-btn-sm">
                            View Profile <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>

                    <?php if ($report['ban_id']): ?>
                    <div class="rv-ban-notice">
                        <div class="rv-ban-notice-label">Active Ban</div>
                        <div class="rv-ban-notice-text">
                            Banned on <?= date('d M Y', strtotime($report['banned_at'])) ?>
                            <?php if (!empty($report['ban_reason'])): ?>
                                — "<?= h($report['ban_reason']) ?>"
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Actions -->
        <div class="col-lg-4">
            <div class="rv-action-card">
                <div class="rv-action-header">Actions</div>
                <div class="rv-action-body">

                    <?php if ($report['status'] === 'pending'): ?>

                        <!-- Dismiss -->
                        <form method="POST" action="report_action.php">
                            <input type="hidden" name="report_id"  value="<?= $reportId ?>">
                            <input type="hidden" name="action"     value="dismiss">
                            <button type="submit" class="rv-btn rv-btn-dismiss"
                                    onclick="return confirm('Mark this report as dismissed?')">
                                <i class="bi bi-x-circle"></i> Dismiss Report
                            </button>
                        </form>

                        <!-- Mark reviewed -->
                        <form method="POST" action="report_action.php">
                            <input type="hidden" name="report_id"  value="<?= $reportId ?>">
                            <input type="hidden" name="action"     value="review">
                            <button type="submit" class="rv-btn rv-btn-success">
                                <i class="bi bi-check-circle"></i> Mark as Reviewed
                            </button>
                        </form>

                        <!-- Ban / Unban -->
                        <?php if (!$report['ban_id']): ?>
                        <form method="POST" action="report_action.php">
                            <input type="hidden" name="report_id"  value="<?= $reportId ?>">
                            <input type="hidden" name="action"     value="ban">
                            <div class="rv-ban-field">
                                <div class="rv-label">Ban reason (optional)</div>
                                <textarea name="ban_reason" rows="2"
                                          class="rv-ban-textarea"
                                          placeholder="Reason for the ban…"></textarea>
                            </div>
                            <button type="submit" class="rv-btn rv-btn-danger"
                                    onclick="return confirm('Permanently ban <?= h(addslashes($report['reported_name'])) ?>? This cannot be undone from here.')">
                                <i class="bi bi-slash-circle"></i> Ban User
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" action="report_action.php">
                            <input type="hidden" name="report_id"  value="<?= $reportId ?>">
                            <input type="hidden" name="action"     value="unban">
                            <button type="submit" class="rv-btn rv-btn-outline"
                                    onclick="return confirm('Remove the ban for this user?')">
                                <i class="bi bi-arrow-counterclockwise"></i> Unban User
                            </button>
                        </form>
                        <?php endif; ?>

                    <?php else: ?>

                        <div class="rv-resolved-msg">
                            This report has been <?= h($report['status']) ?>.
                        </div>

                        <?php if ($report['ban_id']): ?>
                        <form method="POST" action="report_action.php">
                            <input type="hidden" name="report_id"  value="<?= $reportId ?>">
                            <input type="hidden" name="action"     value="unban">
                            <button type="submit" class="rv-btn rv-btn-outline"
                                    onclick="return confirm('Remove the ban for this user?')">
                                <i class="bi bi-arrow-counterclockwise"></i> Unban User
                            </button>
                        </form>
                        <?php endif; ?>

                        <form method="POST" action="report_action.php">
                            <input type="hidden" name="report_id"  value="<?= $reportId ?>">
                            <input type="hidden" name="action"     value="reopen">
                            <button type="submit" class="rv-btn rv-btn-dismiss">
                                <i class="bi bi-arrow-repeat"></i> Re-open Report
                            </button>
                        </form>

                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once 'includes/footer.php'; ?>