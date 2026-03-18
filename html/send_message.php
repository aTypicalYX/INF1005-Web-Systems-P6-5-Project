<?php
session_start();
require_once '/var/www/config/db.php';

if (!isset($_SESSION['user_id'])) exit();

$userId = $_SESSION['user_id'];
$matchId = (int) $_POST['match_id'];
$message = trim($_POST['message']);

if (!$message) exit();

// Optional: validate match ownership here too

$stmt = $pdo->prepare("
    INSERT INTO messages (match_id, sender_id, message_text)
    VALUES (?, ?, ?)
");
$stmt->execute([$matchId, $userId, $message]);