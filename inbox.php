<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$users = ['Alex', 'Jamie', 'Taylor'];  // Example users
$lastChatUser = $_SESSION['last_chat_user'] ?? '';

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
            <?php foreach ($users as $user): ?>
                <div class="user">
                    <span><?= htmlspecialchars($user) ?></span>
                    <button class="message-btn" onclick="openChat('<?= htmlspecialchars($user) ?>')">Message</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window" id="chatWindow" style="display: <?= $lastChatUser ? 'block' : 'none' ?>;">
        <h3 id="chatWith">Chat with <?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?></h3>
        <div id="chatBody" class="chat-body"></div>

        <!-- Message Input -->
        <form id="chatForm">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit" id="sendButton">➤</button>
        </form>
    </div>
</div>

<script>
    let currentChatUser = '<?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?>';

    // Open chat window
    function openChat(user) {
        currentChatUser = user;
        document.getElementById('chatWith').innerText = `Chat with ${user}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat();
    }

    // Load chat messages
    function loadChat() {
        if (currentChatUser !== '') {
            fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
                .then(response => response.json())
                .then(data => {
                    const chatBody = document.getElementById('chatBody');
                    chatBody.innerHTML = data.messages.map(msg => `
                        <div class="message ${msg.sender === "<?= $username ?>" ? 'mine' : 'theirs'}">
                            <strong>${msg.sender}</strong>: ${msg.text}
                            <span class="timestamp">${msg.time}</span>
                        </div>
                    `).join('') || "<p>No messages yet!</p>";
                    chatBody.scrollTop = chatBody.scrollHeight;
                })
                .catch(err => console.error('Error loading chat:', err));
        }
    }

    // Send a message (Fixed)
    document.getElementById('chatForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();

        if (message !== '' && currentChatUser) {
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const chatBody = document.getElementById('chatBody');
                    const now = new Date();
                    const time = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`;

                    chatBody.innerHTML += `
                        <div class="message mine">
                            <strong><?= $username ?></strong>: ${message}
                            <span class="timestamp">${time}</span>
                        </div>`;
                    chatBody.scrollTop = chatBody.scrollHeight;

                    messageInput.value = '';
                } else {
                    alert("Failed to send message.");
                }
            })
            .catch(err => console.error('Error sending message:', err));
        }
    });

    // Auto-refresh chat every 3 seconds
    setInterval(loadChat, 3000);
</script>

</body>
</html>
