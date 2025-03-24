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

// Navbar
echo '<nav class="bg-blue-500 shadow-lg"><div class="max-w-6xl mx-auto px-4"><h2 class="text-white text-lg py-4">MyApp</h2></div></nav>';
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
        <h3 id="chatWith"></h3>
        <div id="chatBody" class="chat-body"></div>

        <form id="chatForm" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit">➤</button>
        </form>
    </div>
</div>

<script>
let currentChatUser = '';

function openChat(user) {
    currentChatUser = user;
    document.getElementById('chatWith').innerText = `Chat with ${user}`;
    document.getElementById('chatWindow').style.display = 'block';
    loadChat();
}

// Load messages from JSON
function loadChat() {
    if (currentChatUser) {
        fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
            .then(response => response.json())
            .then(data => {
                const chatBody = document.getElementById('chatBody');
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
            if (data.error) {
                console.error('Send Error:', data.error);
                alert(`Error: ${data.error}`);
            } else {
                document.getElementById('messageInput').value = '';
                loadChat();
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            alert('Failed to send message!');
        });
    }
}


// Refresh messages every second
setInterval(loadChat, 1000);
</script>

</body>
</html>
