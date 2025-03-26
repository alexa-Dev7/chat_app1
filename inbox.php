<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and ensure user is logged in
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Get the logged-in user
$username = $_SESSION['username'];

// Fetch all other users
require 'db_connect.php';
$stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inbox | Messenger</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

<div class="chat-container">
    <!-- Sidebar with users -->
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
    function openChat(user) {
        currentChatUser = user;
        document.getElementById('chatWith').innerText = `Chat with ${user}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat();
    }

    
  // Load messages
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
                    chatBody.innerHTML = `<p class='error'>⚠️ ${data.error}</p>`;
                    return;
                }

                let messagesHTML = data.messages.map(msg => `
                    <div class="message ${msg.sender === '<?= $username ?>' ? 'mine' : 'theirs'}">
                        <strong>${msg.sender}</strong>: ${msg.text}
                        <span class="timestamp">${msg.time}</span>
                    </div>
                `).join('');

                chatBody.innerHTML = messagesHTML || "<p>No messages yet!</p>";
                chatBody.scrollTop = chatBody.scrollHeight;
            })
            .catch(err => {
                console.error('Error loading chat:', err);
                document.getElementById('chatBody').innerHTML = "<p class='error'>⚠️ An error occurred while loading the chat.</p>";
            });
    }
}

// Send message
function sendMessage(event) {
    event.preventDefault();
    const message = document.getElementById('messageInput').value.trim();

    if (message !== '') {
        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                document.getElementById('messageInput').value = '';
                loadChat();
            } else {
                alert(`❗ Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('❗ Error sending message.');
        });
    }
}

    // Auto-refresh chat every 3 seconds
    setInterval(loadChat, 3000);

</script>

</body>
</html>
