<?php
// Start session
// Force JSON response and error handling
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Catch any fatal error or warning as JSON
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: $message"]);
    exit;
});

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Get logged-in user
$username = $_SESSION['username'];

// Fetch all other users
require 'db_connect.php';
$stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Remember last chat user
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
        <h2>👤 Mac <a href="logout.php">Logout</a></h2>
        <h3>All Users</h3>
        <div class="user-list">
                            <div class="user">
                    <span>Alex</span>
                    <button class="message-btn" onclick="openChat('Alex')">Message</button>
                </div>
                    </div>
    </div>

    <!-- Chat window -->
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

    // Open a chat with a selected user
// Open a chat with a selected user
function openChat(user) {
    currentChatUser = user;
    document.getElementById('chatWith').innerText = `Chat with ${user}`;
    document.getElementById('chatWindow').style.display = 'block';
    loadChat();
}

// Load messages from JSON file
function loadChat() {
    if (currentChatUser !== '') {
        fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
            .then(response => {
                if (!response.ok) throw new Error('Server error');
                return response.json();
            })
            .then(data => {
                const chatBody = document.getElementById('chatBody');

                if (data.error) {
                    console.error("Chat Error:", data.error);
                    chatBody.innerHTML = `<p class='error'>⚠️ ${data.error}</p>`;
                    return;
                }

                let messagesHTML = "";
                data.messages.forEach(msg => {
                    const isMine = msg.sender === '<?= $_SESSION['username'] ?>';
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

// Send a message
function sendMessage(event) {
    event.preventDefault();
    const message = document.getElementById('messageInput').value.trim();

    if (message !== '') {
        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            if (data.status === "success") {
                document.getElementById('messageInput').value = '';
                loadChat();
            } else {
                console.error('Failed to send message:', data.message);
                alert(`❗ Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert(`⚠️ Error: ${error.message}`);
        });
    }
}

// Auto-refresh chat every 3 seconds
setInterval(loadChat, 3000);

</script>

</body>
</html>
