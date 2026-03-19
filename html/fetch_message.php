<?php
session_start();
require_once '/var/www/config/db.php';

if (!isset($_SESSION['user_id'])) exit();

$userId = $_SESSION['user_id'];
$matchId = (int) $_GET['match_id'];

$stmt = $pdo->prepare("
    SELECT * FROM messages
    WHERE match_id = ?
    ORDER BY created_at ASC
");
$stmt->execute([$matchId]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as $msg) {
    $class = $msg['sender_id'] == $userId ? 'sent' : 'received';
    echo "<div class='message $class'>" . htmlspecialchars($msg['message_text']) . "</div>";
}