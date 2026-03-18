<?php
session_start();
require_once '/var/www/config/db.php';
if (!isset($_SESSION['user_id'])) exit();

$userId  = (int) $_SESSION['user_id'];
$matchId = (int) $_GET['match_id'];
$afterId = isset($_GET['after_id']) ? (int) $_GET['after_id'] : 0;

$stmt = $pdo->prepare("
    SELECT * FROM messages
    WHERE match_id = ?
    AND id > ?
    ORDER BY created_at ASC
");
$stmt->execute([$matchId, $afterId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as $msg) {
    $class = $msg['sender_id'] == $userId ? 'sent' : 'received';
    echo "<div class='message $class' data-id='" . (int) $msg['id'] . "'>"
        . htmlspecialchars($msg['message_text'])
        . "</div>";
}