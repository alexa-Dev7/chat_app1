<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$messages = json_decode(file_get_contents('persistent_data/messages.json'), true);
$users = json_decode(file_get_contents('persistent_data/users.json'), true);

// Get all users except the logged-in one
$allUsers = array_filter($users, fn($u) => $u !== $username);

$currentChatUser = $_GET['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="chat-container">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?></h2>

        <!-- User List (Inbox) -->
        <div class="user-list">
            <?php if (empty($allUsers)): ?>
                <p>No users available!</p>
            <?php else: ?>
                <?php foreach ($allUsers as $user): ?>
                    <div class="user <?= $user === $currentChatUser ? 'active' : '' ?>" 
                         onclick="openChat('<?= urlencode($user) ?>')">
                        <?= htmlspecialchars($user) ?>

                        <!-- New message notification -->
                        <?php
                        $hasNewMessage = false;
                        foreach ($messages as $msg) {
                            if ($msg['to'] === $username && $msg['from'] === $user && !$msg['read']) {
                                $hasNewMessage = true;
                                break;
                            }
                        }
                        if ($hasNewMessage): ?>
                            <span class="new-message-indicator">●</span>
                        <?php endif; ?>
                        <button class="message-btn" onclick="openChat('<?= urlencode($user) ?>')">Message</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window">
        <?php if ($currentChatUser): ?>
            <h3>Chat with <?= htmlspecialchars($currentChatUser) ?></h3>
            <div class="chat-body" id="chatBody">
                <?php foreach ($messages as $msg): ?>
                    <?php if (($msg['from'] === $username && $msg['to'] === $currentChatUser) ||
                        ($msg['from'] === $currentChatUser && $msg['to'] === $username)): ?>
                        <div class="message <?= $msg['from'] === $username ? 'outgoing' : 'incoming' ?>">
                            <?= htmlspecialchars($msg['text']) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Typing Indicator -->
            <div id="typingIndicator" style="display: none; font-style: italic; padding: 5px 10px;">🔧 <?= htmlspecialchars($currentChatUser) ?> is typing...</div>

            <form method="post" action="send_message.php">
                <input type="hidden" name="to" value="<?= htmlspecialchars($currentChatUser) ?>">
                <input type="text" name="message" id="messageInput" placeholder="Type a message..." oninput="notifyTyping()">
                <button type="submit">➤</button>
            </form>
        <?php else: ?>
            <div class="no-chat-selected">
                <h2>👋 Start a conversation!</h2>
                <p>Select a user from the inbox to chat.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function openChat(user) {
        window.location.href = 'inbox.php?user=' + user;
    }

    const chatBody = document.getElementById('chatBody');
    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;

    setInterval(() => {
        if ('<?= $currentChatUser ?>' !== '') {
            fetch('load_chat.php?user=<?= urlencode($currentChatUser) ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('chatBody').innerHTML = data;
                    chatBody.scrollTop = chatBody.scrollHeight;
                });
        }
    }, 1000);

    let typingTimer;
    function notifyTyping() {
        clearTimeout(typingTimer);
        fetch('typing_status.php?user=<?= urlencode($currentChatUser) ?>&typing=1');

        typingTimer = setTimeout(() => {
            fetch('typing_status.php?user=<?= urlencode($currentChatUser) ?>&typing=0');
        }, 2000);
    }

    setInterval(() => {
        fetch('typing_status.php?user=<?= urlencode($currentChatUser) ?>')
            .then(response => response.json())
            .then(data => {
                const indicator = document.getElementById('typingIndicator');
                if (data.typing) {
                    indicator.style.display = 'block';
                } else {
                    indicator.style.display = 'none';
                }
            });
    }, 1000);
</script>

</body>
</html>
