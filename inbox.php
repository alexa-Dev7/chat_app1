<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch all other users
require 'db_connect.php';
$stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Remember the last chat user
$lastChatUser = $_SESSION['last_chat_user'] ?? null;
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
    <!-- Sidebar showing users -->
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

    <!-- Chat window -->
    <div class="chat-window" id="chatWindow" style="display: <?= $lastChatUser ? 'block' : 'none' ?>;">
        <h3 id="chatWith">Chat with <?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?></h3>
        <div id="chatBody" class="chat-body"></div>

        <!-- Message Input -->
        <form id="chatForm" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit">➤</button>
        </form>
    </div>
</div>

<script>
    let currentChatUser = '<?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?>';

    // Open a chat with a selected user
  // Open the chat window
function openChat(user) {
    currentChatUser = user;
    document.getElementById('chatWith').innerText = `Chat with ${user}`;
    document.getElementById('chatWindow').style.display = 'block';
    loadChat();
}

// Load messages with improved error handling
function loadChat() {
    if (currentChatUser !== '') {
        fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Expected JSON response');
                }
                return response.json();
            })
            .then(data => {
                const chatBody = document.getElementById('chatBody');
                if (data.error) {
                    chatBody.innerHTML = `<p class='error'>⚠️ ${data.error}</p>`;
                } else {
                    chatBody.innerHTML = data.messages.length
                        ? data.messages.map(msg => `
                            <div class="message ${msg.sender === '<?= $username ?>' ? 'mine' : 'theirs'}">
                                <strong>${msg.sender}</strong>: ${msg.text}
                                <span class="timestamp">${msg.time}</span>
                            </div>`).join('')
                        : "<p>No messages yet!</p>";
                }
                chatBody.scrollTop = chatBody.scrollHeight;
            })
            .catch(err => console.error('Error loading chat:', err.message));
    }
}

// Send message with better error handling
function sendMessage(event) {
    event.preventDefault();
    const message = document.getElementById('messageInput').value.trim();
    if (message) {
        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Failed to send message:', data.error);
            } else {
                document.getElementById('messageInput').value = '';
                loadChat();
            }
        })
        .catch(err => console.error('Error sending message:', err.message));
    }
}

// Auto-refresh chat every 3 seconds
setInterval(loadChat, 3000);

</script>

</body>
</html>
