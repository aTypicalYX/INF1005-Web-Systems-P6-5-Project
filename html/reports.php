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

// ── Filter & pagination ──
$allowedStatuses = ['all', 'pending', 'reviewed', 'dismissed'];
$status  = in_array($_GET['status'] ?? '', $allowedStatuses, true) ? $_GET['status'] : 'all';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

// ── Count per status for tabs ──
$counts = ['all' => 0, 'pending' => 0, 'reviewed' => 0, 'dismissed' => 0];
try {
    $rows = $pdo->query("SELECT status, COUNT(*) AS n FROM reports GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $counts[$r['status']] = (int)$r['n'];
        $counts['all'] += (int)$r['n'];
    }
} catch (Exception $e) {}

// ── Fetch reports ──
$reports = [];
$total   = 0;
try {
    $where = $status !== 'all' ? "WHERE r.status = " . $pdo->quote($status) : '';
    $total = (int)$pdo->query("SELECT COUNT(*) FROM reports r $where")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT
            r.id, r.reason, r.status, r.created_at,
            COALESCE(NULLIF(rp.display_name,''), ru.first_name) AS reporter_name,
            COALESCE(NULLIF(dp.display_name,''), du.first_name) AS reported_name,
            du.id AS reported_id,
            (SELECT id FROM bans WHERE user_id = du.id LIMIT 1) AS ban_id
        FROM reports r
        JOIN users ru ON ru.id = r.reporter_id
        LEFT JOIN profile rp ON rp.user_id = r.reporter_id
        JOIN users du ON du.id = r.reported_id
        LEFT JOIN profile dp ON dp.user_id = r.reported_id
        $where
        ORDER BY CASE r.status WHEN 'pending' THEN 0 ELSE 1 END, r.created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

$reasonLabels = [
    'harassment'            => 'Harassment',
    'fake_profile'          => 'Fake Profile',
    'inappropriate_content' => 'Inappropriate Content',
    'spam'                  => 'Spam',
    'other'                 => 'Other',
];

$activePage = '';
$pageTitle  = 'Reports';
require_once 'includes/header.php';
?>

<div class="ar-wrap">

    <div class="ar-page-header">
        <h1 class="ar-page-title">Reports</h1>
        <p class="ar-page-sub">
            <?= $counts['pending'] ?> pending review &middot; <?= $counts['all'] ?> total
        </p>
    </div>

    <div class="ar-card">
        <div class="ar-card-header">
            <div class="ar-tabs">
                <?php foreach (['all' => 'All', 'pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'] as $val => $label): ?>
                    <a href="reports.php?status=<?= $val ?>"
                       class="ar-tab <?= $status === $val ? 'ar-tab-active' : '' ?>">
                        <?= $label ?>
                        <?php if ($counts[$val] > 0): ?>
                            <span class="ar-tab-count"><?= $counts[$val] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <span class="ar-total"><?= $total ?> report<?= $total !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($reports)): ?>
            <div class="ar-empty">
                <div class="ar-empty-icon">📋</div>
                <div class="ar-empty-text">No <?= $status !== 'all' ? $status . ' ' : '' ?>reports found.</div>
            </div>
        <?php else: ?>
            <table class="ar-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reporter</th>
                        <th>Reported User</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $rep): ?>
                    <tr>
                        <td class="ar-col-id">#<?= (int)$rep['id'] ?></td>
                        <td class="ar-col-reporter"><?= h($rep['reporter_name']) ?></td>
                        <td>
                            <div class="ar-col-reported">
                                <span class="ar-col-reported-name"><?= h($rep['reported_name']) ?></span>
                                <?php if ($rep['ban_id']): ?>
                                    <span class="ar-badge ar-badge-banned">Banned</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="ar-badge ar-badge-reason">
                                <?= h($reasonLabels[$rep['reason']] ?? $rep['reason']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="ar-badge ar-badge-<?= h($rep['status']) ?>">
                                <?= ucfirst(h($rep['status'])) ?>
                            </span>
                        </td>
                        <td class="ar-col-date"><?= date('d M Y', strtotime($rep['created_at'])) ?></td>
                        <td>
                            <a href="report_view.php?id=<?= (int)$rep['id'] ?>"
                               class="ar-btn <?= $rep['status'] === 'pending' ? 'ar-btn-primary' : 'ar-btn-outline' ?>">
                                <?= $rep['status'] === 'pending' ? 'Review' : 'View' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div class="ar-pagination">
                <?php if ($page > 1): ?>
                    <a href="?status=<?= $status ?>&page=<?= $page - 1 ?>" class="ar-page-btn">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                <?php endif; ?>
                <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                    <a href="?status=<?= $status ?>&page=<?= $p ?>"
                       class="ar-page-btn <?= $p === $page ? 'ar-page-btn-active' : '' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?status=<?= $status ?>&page=<?= $page + 1 ?>" class="ar-page-btn">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>