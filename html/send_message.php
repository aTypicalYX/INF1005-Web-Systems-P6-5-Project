<?php
session_start();
require_once '/var/www/config/db.php';
require_once '/var/www/html/includes/profanity.php';

if (!isset($_SESSION['user_id'])) exit();

$userId = $_SESSION['user_id'];
$matchId = (int) $_POST['match_id'];
$message = trim($_POST['message']);

if (!$message) exit();

// Optional: validate match ownership here too
// Profanity check — block message if it contains foul language
if (containsProfanity($message, $profanityList, $wholeWordOnly)) {
    http_response_code(422);
    echo json_encode(['error' => 'Your message contains inappropriate language.']);
    exit();
}

$stmt = $pdo->prepare("
    INSERT INTO messages (match_id, sender_id, message_text)
    VALUES (?, ?, ?)
");
$stmt->execute([$matchId, $userId, $message]);
echo json_encode(['success' => true]);