<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit();
}

require_once '/var/www/config/db.php';

$currentUser = (int) $_SESSION['user_id'];
$matchId     = isset($_GET['match_id']) ? (int) $_GET['match_id'] : 0;

if ($matchId <= 0) {
    die("Invalid match");
}

// Ensure user is part of this match
$stmt = $pdo->prepare("
    SELECT m.*, 
           CASE 
               WHEN m.user_1_id = ? THEN m.user_2_id
               ELSE m.user_1_id
           END AS other_user_id,
           p.display_name,
           p.main_image
    FROM matches m
    JOIN profile p ON p.user_id = CASE 
        WHEN m.user_1_id = ? THEN m.user_2_id
        ELSE m.user_1_id
    END
    WHERE m.id = ?
    AND (m.user_1_id = ? OR m.user_2_id = ?)
");
$stmt->execute([$currentUser, $currentUser, $matchId, $currentUser, $currentUser]);
$match = $stmt->fetch();

if (!$match) {
    die("Unauthorized access");
}

$otherUserId = $match['other_user_id'];
$otherUserName = $match['display_name'];
$otherUserPic  = $match['main_image']
    ? '/images/' . $match['main_image']
    : 'https://ui-avatars.com/api/?name=' . urlencode($otherUserName);


$banCheck = $pdo->prepare("SELECT id FROM bans WHERE user_id = ? LIMIT 1");
$banCheck->execute([$otherUserId]);
if ($banCheck->fetch()) {
    header('Location: messages.php');
    exit();
}

// Load messages
$stmt = $pdo->prepare("
    SELECT * FROM messages
    WHERE match_id = ?
    ORDER BY created_at ASC
");
$stmt->execute([$matchId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<main class="container chat-container">
    <!-- Header -->
    <div class="chat-header">
        <a href="messages.php" class="chat-back-btn" title="Back to messages">⬅</a>
        <a href="profile.php?id=<?= $match['other_user_id'] ?>&from=chat" class="chat-header-profile" title="View profile">
            <img src="<?= htmlspecialchars($otherUserPic) ?>" alt="<?= htmlspecialchars($otherUserName) ?>">
            <strong><?= htmlspecialchars($otherUserName) ?></strong>
        </a>
    </div>

    <!-- Messages -->
    <div class="chat-box" id="chat-box" tabindex="0" aria-label="Chat messages">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= $msg['sender_id'] == $currentUser ? 'sent' : 'received' ?>"
                 data-id="<?= (int) $msg['id'] ?>">
                <?= htmlspecialchars($msg['message_text']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Input -->
    <form class="chat-input" id="chat-form">
        <input type="text" id="message" placeholder="Type a message..." required>
        <button type="submit">Send</button>
    </form>

</main>

<script>
    const matchId    = <?= $matchId ?>;
    const currentUser = <?= $currentUser ?>;

    // Auto scroll
    function scrollToBottom() {
        const box = document.getElementById('chat-box');
        box.scrollTop = box.scrollHeight;
    }
    scrollToBottom();

    // Send message
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const input = document.getElementById('message');
        const message = input.value.trim();

        if (!message) return;

        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `match_id=${matchId}&message=${encodeURIComponent(message)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                showChatError(data.error);
                return; // don't clear the input so they can edit their message
            }
            input.value = ''; // only clear on success
        })
        .catch(() => {
            showChatError('Failed to send message. Please try again.');
        });
    })

    function showChatError(msg) {
        // Remove any existing error first
        document.querySelectorAll('.chat-error-msg').forEach(e => e.remove());

        const chatBox = document.getElementById('chat-box');
        const err = document.createElement('div');
        err.className = 'chat-error-msg';
        err.style.cssText = 'background:#ffe0e0; color:#c00; text-align:center; font-size:0.85rem; border-radius:12px; padding:6px 12px; margin: 4px auto; max-width: 80%;';
        err.innerText = '⚠️ ' + msg;
        chatBox.appendChild(err);
        chatBox.scrollTop = chatBox.scrollHeight;

        // Auto-remove after 3 seconds
        setTimeout(() => err.remove(), 3000);
    }

    // Returns the highest message id currently in the DOM, or 0 if none
    function getLastMessageId() {
        const msgs = document.querySelectorAll('#chat-box .message[data-id]');
        if (!msgs.length) return 0;
        return parseInt(msgs[msgs.length - 1].dataset.id, 10) || 0;
    }

    // Poll for NEW messages only
    function loadMessages() {
        const after = getLastMessageId();
        const box   = document.getElementById('chat-box');
        const wasAtBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 60;

        fetch(`fetch_messages.php?match_id=${matchId}&after_id=${after}`)
            .then(res => res.text())
            .then(html => {
                const trimmed = html.trim();
                if (!trimmed) return;

                box.insertAdjacentHTML('beforeend', trimmed);

                if (wasAtBottom) scrollToBottom();
            });
    }

    // Poll every 2 seconds
    setInterval(loadMessages, 2000);
</script>

<?php require_once 'includes/footer.php'; ?>