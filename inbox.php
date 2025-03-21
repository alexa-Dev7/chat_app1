<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$users = json_decode(file_get_contents('persistent_data/users.json'), true) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox | Messenger</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="chat-container">

    <!-- Sidebar with user list -->
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?> <a href="logout.php">Logout</a></h2>
        <h3>All Users</h3>
        <div class="user-list">
            <?php foreach ($users as $user => $data): ?>
                <?php if ($user !== $username): ?>
                    <div class="user">
                        <span><?= htmlspecialchars($user) ?></span>
                        <button class="message-btn" onclick="openChat('<?= htmlspecialchars($user) ?>')">Message</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat Window (Initially Hidden) -->
    <div class="chat-window" id="chatWindow" style="display: none;">
        <h3 id="chatWith">Chat with </h3>
        <div id="chatBody" class="chat-body"></div>

        <!-- Message Input -->
        <form id="chatForm" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit">➤</button>
        </form>
    </div>

</div>

<script>
    let currentChatUser = '';

    // Open chat window immediately when clicking the button
    function openChat(user) {
        currentChatUser = user;
        document.getElementById('chatWith').innerText = `Chat with ${user}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat();  // Load existing messages
    }

    // Load chat messages (polls every second)
    function loadChat() {
        if (currentChatUser !== '') {
            fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('chatBody').innerHTML = data;
                    document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
                });
        }
    }

    // Send a message without page reload
    function sendMessage(event) {
        event.preventDefault();
        const message = document.getElementById('messageInput').value;
        if (message.trim() !== '') {
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
            })
            .then(() => {
                document.getElementById('messageInput').value = '';
                loadChat();
            });
        }
    }

    // Auto-refresh chat every second
    setInterval(loadChat, 1000);
</script>

</body>
</html>
