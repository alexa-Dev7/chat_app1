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
$lastChatUser = $_SESSION['last_chat_user'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox | Messenger</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Chat Container */
        .chat-container {
            display: flex;
            height: 100vh;
            background: #f4f4f4;
            font-family: 'Arial', sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 25%;
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h2, .sidebar h3 {
            margin-bottom: 10px;
            border-bottom: 1px solid #555;
            padding-bottom: 5px;
        }

        .user-list .user {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background: #34495e;
            border-radius: 5px;
            cursor: pointer;
        }

        .user-list .user:hover {
            background: #1abc9c;
        }

        .user-list .user span {
            font-weight: bold;
        }

        .message-btn {
            background: #16a085;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .message-btn:hover {
            background: #1abc9c;
        }

        /* Chat Window */
        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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

        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .mine {
            background: #d1e7dd;
            text-align: right;
        }

        .theirs {
            background: #f8d7da;
            text-align: left;
        }

        .timestamp {
            font-size: 0.8em;
            color: #555;
        }

        /* Input field */
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
            border-radius: 5px;
            margin-right: 5px;
        }

        button[type="submit"] {
            background: #1abc9c;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: #16a085;
        }
    </style>
</head>

<body>

<div class="chat-container">

    <!-- Sidebar with user list -->
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?> <a href="logout.php" style="color: #e74c3c;">Logout</a></h2>
        <h3>All Users</h3>
        <div class="user-list">
            <?php foreach ($users as $user): ?>
                <div class="user" onclick="openChat('<?= htmlspecialchars($user) ?>')">
                    <span><?= htmlspecialchars($user) ?></span>
                    <button class="message-btn">Message</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat Window (Initially Hidden) -->
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

// Open chat window when a user is clicked
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
                    <div class="message ${msg.sender === '<?= $username ?>' ? 'mine' : 'theirs'}">
                        <strong>${msg.sender}</strong>: ${msg.text}
                        <span class="timestamp">${msg.time}</span>
                    </div>
                `).join('') || "<p>No messages yet!</p>";

                // Auto-scroll to the latest message
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

    if (message !== '') {
        fetch('send_messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Immediately show the message without waiting for loadChat()
                const chatBody = document.getElementById('chatBody');
                const now = new Date();
                const time = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`;

                chatBody.innerHTML += `
                    <div class="message mine">
                        <strong><?= $username ?></strong>: ${message}
                        <span class="timestamp">${time}</span>
                    </div>
                `;

                // Scroll to the latest message and clear input box
                chatBody.scrollTop = chatBody.scrollHeight;
                messageInput.value = '';
            } else {
                console.error("Failed to send message:", data.error);
            }
        })
        .catch(err => console.error('Error sending message:', err));
    }
}

// Auto-refresh chat every 1 seconds to pull new messages
setInterval(loadChat, 1000);

</script>

</body>
</html>
