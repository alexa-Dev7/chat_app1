<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

require 'db_connect.php';
$stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

<!-- Navbar -->
<nav class="bg-blue-500 shadow-lg">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between">
            <a class="flex items-center py-4 px-2 bg-blue-500" href="#">
                <span class="font-semibold text-white text-lg">Red Pages Messenger</span>
            </a>
            <div class="hidden md:flex items-center space-x-3">
                <a class="py-2 px-2 font-medium text-white rounded hover:bg-blue-400" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<!-- Chat Container -->
<div class="chat-container">
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?></h2>
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
        <form id="chatForm" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit">➤</button>
        </form>
    </div>
</div>

<script>
let currentChatUser = '<?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?>';

function openChat(user) {
    currentChatUser = user;
    document.getElementById('chatWith').innerText = `Chat with ${user}`;
    document.getElementById('chatWindow').style.display = 'block';
    loadChat();
}

function loadChat() {
    if (currentChatUser !== '') {
        fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
            .then(response => response.json())
            .then(data => {
                const chatBody = document.getElementById('chatBody');
                if (data.error) {
                    console.error("Chat Error:", data.error);
                    chatBody.innerHTML = `<p class='error'>⚠️ ${data.error}</p>`;
                    return;
                }

                let messagesHTML = "";
                data.messages.forEach(msg => {
                    const isMine = msg.sender === '<?= $username ?>';
                    messagesHTML += `
                        <div class="message ${isMine ? 'mine' : 'theirs'}">
                            <strong>${msg.sender}</strong>: ${msg.text}
                            <span class="timestamp">${msg.time}</span>
                        </div>`;
                });

                chatBody.innerHTML = messagesHTML || "<p>No messages yet!</p>";
                chatBody.scrollTop = chatBody.scrollHeight;
            })
            .catch(err => console.error('Error loading chat:', err));
    }
}

function sendMessage(event) {
    event.preventDefault();
    const message = document.getElementById('messageInput').value;
    if (message.trim() !== '') {
        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('messageInput').value = '';
                loadChat();
            } else {
                alert("Failed to send message: " + data.error);
            }
        });
    }
}

setInterval(loadChat, 1000);
</script>

</body>
</html>
