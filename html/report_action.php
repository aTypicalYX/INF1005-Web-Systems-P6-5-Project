<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit();
}

// Ensure only POST Actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reports.php');
    exit();
}

$reportId  = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
$action    = trim($_POST['action'] ?? '');
$adminId   = (int)$_SESSION['user_id'];

$allowedActions = ['dismiss', 'review', 'ban', 'unban', 'reopen'];
if ($reportId === 0 || !in_array($action, $allowedActions, true)) {
    $_SESSION['admin_error'] = 'Invalid action.';
    header('Location: reports.php');
    exit();
}

// Fetch report
$report = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ? LIMIT 1");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

if (!$report) {
    $_SESSION['admin_error'] = 'Report not found.';
    header('Location: reports.php');
    exit();
}

$reportedId = (int)$report['reported_id'];
$now        = date('Y-m-d H:i:s');

try {
    switch ($action) {

        // ── Dismiss: no action against user ──
        case 'dismiss':
            $pdo->prepare("
                UPDATE reports
                SET status = 'dismissed', reviewed_by = ?, reviewed_at = ?
                WHERE id = ?
            ")->execute([$adminId, $now, $reportId]);
            $_SESSION['admin_success'] = 'Report dismissed.';
            break;

        // ── Review: acknowledge without banning ──
        case 'review':
            $pdo->prepare("
                UPDATE reports
                SET status = 'reviewed', reviewed_by = ?, reviewed_at = ?
                WHERE id = ?
            ")->execute([$adminId, $now, $reportId]);
            $_SESSION['admin_success'] = 'Report marked as reviewed.';
            break;

        // ── Ban: insert into bans + mark report reviewed ──
        case 'ban':
            $banReason = trim($_POST['ban_reason'] ?? '');

            // Check not already banned
            $alreadyBanned = (int)$pdo->prepare("SELECT COUNT(*) FROM bans WHERE user_id = ?")
                                       ->execute([$reportedId]) &&
                             $pdo->prepare("SELECT COUNT(*) FROM bans WHERE user_id = ?")
                                       ->fetchColumn();

            // Use a transaction so both writes succeed or neither does
            $pdo->beginTransaction();

            // Insert ban (UNIQUE on user_id — use INSERT IGNORE to be safe)
            $banStmt = $pdo->prepare("
                INSERT IGNORE INTO bans (user_id, banned_by, reason, banned_at, expires_at)
                VALUES (?, ?, ?, ?, NULL)
            ");
            $banStmt->execute([
                $reportedId,
                $adminId,
                $banReason !== '' ? $banReason : null,
                $now,
            ]);

            // Mark report as reviewed
            $pdo->prepare("
                UPDATE reports
                SET status = 'reviewed', reviewed_by = ?, reviewed_at = ?
                WHERE id = ?
            ")->execute([$adminId, $now, $reportId]);

            $pdo->commit();
            $_SESSION['admin_success'] = 'User has been permanently banned and report marked as reviewed.';
            break;

        // ── Unban ──
        case 'unban':
            $pdo->prepare("DELETE FROM bans WHERE user_id = ?")
                ->execute([$reportedId]);
            $_SESSION['admin_success'] = 'User ban has been removed.';
            break;

        // ── Re-open ──
        case 'reopen':
            $pdo->prepare("
                UPDATE reports
                SET status = 'pending', reviewed_by = NULL, reviewed_at = NULL
                WHERE id = ?
            ")->execute([$reportId]);
            $_SESSION['admin_success'] = 'Report re-opened and marked as pending.';
            break;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['admin_error'] = 'Something went wrong. Please try again.';
}

header("Location: report_view.php?id=$reportId");
exit();