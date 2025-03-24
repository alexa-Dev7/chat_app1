<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
require 'db_connect.php';

// Fetch users excluding the current user
$stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Load last chat user
$lastChatUser = $_SESSION['last_chat_user'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox | Messenger</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Basic styles for demo purposes */
        .chat-container {
            display: flex;
            height: 100vh;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            width: 25%;
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            overflow-y: auto;
        }
        .user-list .user {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background: #34495e;
            border-radius: 4px;
            cursor: pointer;
        }
        .message-btn {
            background: #16a085;
            border: none;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #ddd;
            background: #fff;
        }
        .chat-window h3 {
            background: #3498db;
            color: #fff;
            padding: 15px;
            margin: 0;
            text-align: center;
        }
        .chat-body {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
            background: #ecf0f1;
        }
        #chatForm {
            display: flex;
            border-top: 1px solid #ddd;
            padding: 10px;
            background: #fff;
        }
        #messageInput {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 5px;
        }
        #chatForm button {
            background: #1abc9c;
            border: none;
            color: #fff;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
<div class="chat-container">
    <!-- Sidebar with user list -->
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
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<script>
    let currentChatUser = '<?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?>';

    // Open chat window when a user is clicked
    function openChat(user) {
        currentChatUser = user;
        document.getElementById('chatWith').innerText = `Chat with ${user}`;
        document.getElementById('chatWindow').style.display = 'block';
        fetch('set_last_chat.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user=${encodeURIComponent(user)}`
        });
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
                        <div class="message ${msg.sender === '<?= $username ?>' ? 'mine' : 'theirs'}">
                            <strong>${msg.sender}</strong>: ${msg.text}
                            <span class="timestamp">${msg.time}</span>
                        </div>
                    `).join('') || "<p style='text-align:center; color:#555;'>No messages yet!</p>";
                    chatBody.scrollTop = chatBody.scrollHeight;
                })
                .catch(err => console.error('Error loading chat:', err));
        }
    }

    // Send a message
    function sendMessage(event) {
        event.preventDefault();
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        if (message !== '' && currentChatUser !== '') {
            fetch('send_messages.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadChat(); // Reload chat after sending
                        messageInput.value = '';
                    } else {
                        console.error("Message not sent:", data.error);
                    }
                })
                .catch(err => console.error('Error sending message:', err));
        }
    }

    // Auto-refresh chat every 3 seconds
    setInterval(loadChat, 1000);
</script>
</body>
</html>
