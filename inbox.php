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

    // Load messages from JSON file
    async function loadChat() {
        if (currentChatUser !== '') {
            try {
                const response = await fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`);
                
                // Check if the response is valid JSON
                const contentType = response.headers.get("content-type");
                if (!response.ok || !contentType.includes("application/json")) {
                    throw new Error("Failed to load chat - Invalid response format.");
                }

                const data = await response.json();
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

            } catch (error) {
                console.error('Error loading chat:', error);
                document.getElementById('chatBody').innerHTML = `<p class='error'>⚠️ Unable to load chat. ${error.message}</p>`;
            }
        }
    }

    // Send a message
    async function sendMessage(event) {
        event.preventDefault();
        const message = document.getElementById('messageInput').value.trim();

        if (message !== '') {
            try {
                const response = await fetch('send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
                });

                // Ensure the response is valid JSON
                const contentType = response.headers.get("content-type");
                if (!response.ok || !contentType.includes("application/json")) {
                    throw new Error("Failed to send message - Invalid response format.");
                }

                const data = await response.json();
                
                if (data.status === "success") {
                    document.getElementById('messageInput').value = '';
                    loadChat();
                } else {
                    console.error('Failed to send message:', data.message);
                    alert(`❗ Error: ${data.message}`);
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert(`⚠️ Error: ${error.message}`);
            }
        }
    }

    // Auto-refresh chat every 3 seconds
    setInterval(loadChat, 3000);

</script>

</body>
</html>
