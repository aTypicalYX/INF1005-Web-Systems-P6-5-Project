<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Only allow POST Methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profiles.php');
    exit();
}

$reporterId = (int) $_SESSION['user_id'];

// Collect and sanitise inputs from report form
$reportedId  = isset($_POST['reported_id'])  ? (int) $_POST['reported_id']  : 0;
$reason      = isset($_POST['reason'])       ? trim($_POST['reason'])       : '';
$description = isset($_POST['description'])  ? trim($_POST['description'])  : '';

// Validate the reported ID
if ($reportedId === 0 || $reportedId === $reporterId) {
    $_SESSION['report_error'] = 'Invalid report target.';
    header('Location: profiles.php');
    exit();
}

// Validate that the reason is within the allowed reasons
$allowedReasons = ['harassment', 'fake_profile', 'inappropriate_content', 'spam', 'other'];
if (!in_array($reason, $allowedReasons, true)) {
    $_SESSION['report_error'] = 'Please select a reason for your report.';
    header("Location: report.php?user_id=$reportedId");
    exit();
}

// Truncate the description to fit the 1000 character limit
if (mb_strlen($description) > 1000) {
    $description = mb_substr($description, 0, 1000);
}

// Verify the reported user exists
try {
    $userCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
    $userCheck->execute([$reportedId]);
    if (!$userCheck->fetch()) {
        $_SESSION['report_error'] = 'The profile you tried to report no longer exists.';
        header('Location: profiles.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['report_error'] = 'Something went wrong. Please try again later.';
    header("Location: report.php?user_id=$reportedId");
    exit();
}

// Check for any duplicate pending reports
try {
    $dupCheck = $pdo->prepare("
        SELECT id FROM reports
        WHERE reporter_id = ? AND reported_id = ? AND status = 'pending'
        LIMIT 1
    ");
    $dupCheck->execute([$reporterId, $reportedId]);
    if ($dupCheck->fetch()) {
        // Already reported – redirect to profile, report.php will show the banner
        header("Location: report.php?user_id=$reportedId");
        exit();
    }
} catch (Exception $e) {
    // If the duplicate check fails, allow submission to proceed
}

// Submit and insert the report into the table
try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, reported_id, reason, description, status)
        VALUES (:reporter_id, :reported_id, :reason, :description, 'pending')
    ");
    $stmt->execute([
        ':reporter_id' => $reporterId,
        ':reported_id' => $reportedId,
        ':reason'      => $reason,
        ':description' => $description !== '' ? $description : null,
    ]);

    // Set success flash and redirect to a neutral page
    $_SESSION['report_success'] = true;
    header("Location: report_success.php?user_id=$reportedId");
    exit();

} catch (Exception $e) {
    $_SESSION['report_error'] = 'Something went wrong while submitting your report. Please try again.';
    header("Location: report.php?user_id=$reportedId");
    exit();
}