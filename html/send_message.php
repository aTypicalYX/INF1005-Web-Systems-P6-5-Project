<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Using __DIR__ makes paths more reliable
require_once __DIR__ . '/includes/profanity.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$matchId = (int) $_POST['match_id'];
$message = trim($_POST['message']);

if (!$message || !$matchId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

// Profanity check
if (containsProfanity($message, $profanityList, $wholeWordOnly)) {
    http_response_code(422);
    echo json_encode(['error' => 'Your message contains inappropriate language.']);
    exit();
}

try {
    // Verify the current user is actually part of this match
    $checkMatch = $pdo->prepare("
        SELECT id FROM matches 
        WHERE id = ? AND (user_1_id = ? OR user_2_id = ?)
        LIMIT 1
    ");
    $checkMatch->execute([$matchId, $userId, $userId]);
    
    if (!$checkMatch->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'You do not have permission to message this match.']);
        exit();
    }

    // Insert the message if verification passes
    $stmt = $pdo->prepare("
        INSERT INTO messages (match_id, sender_id, message_text)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$matchId, $userId, $message]);
    
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}