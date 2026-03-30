<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

require_once '/var/www/config/db.php';

$data      = json_decode(file_get_contents('php://input'), true);
// Verify the AJAX CSRF Token sent from the JavaScript
if (!isset($data['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $data['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid security token.']);
    exit();
}
$swipedId  = isset($data['swiped_id']) ? (int) $data['swiped_id'] : 0;
$direction = isset($data['direction']) ? trim($data['direction'])  : '';
$swiperId  = (int) $_SESSION['user_id'];

if (!$swipedId || !in_array($direction, ['like', 'pass'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

if ($swipedId === $swiperId) {
    http_response_code(400);
    echo json_encode(['error' => 'Cannot swipe yourself']);
    exit();
}

try {
    // Record the swipe — INSERT IGNORE skips silently if already exists
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO swipes (swiper_id, swiped_id, direction)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$swiperId, $swipedId, $direction]);

    $match = false;

    if ($direction === 'like') {
        // Check if the other person already liked us back
        $check = $pdo->prepare("
            SELECT id FROM swipes
            WHERE swiper_id = ? AND swiped_id = ? AND direction = 'like'
            LIMIT 1
        ");
        $check->execute([$swipedId, $swiperId]);

        if ($check->fetch()) {
            $match = true;

            // Store match — always put lower ID as user_1 to avoid duplicates
            $u1 = min($swiperId, $swipedId);
            $u2 = max($swiperId, $swipedId);

            $matchStmt = $pdo->prepare("
                INSERT IGNORE INTO matches (user_1_id, user_2_id)
                VALUES (?, ?)
            ");
            $matchStmt->execute([$u1, $u2]);
        }
    }

    echo json_encode(['success' => true, 'match' => $match]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}