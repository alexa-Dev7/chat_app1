<?php
// Start session and check user login
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Database connection (Render PostgreSQL setup)
$host = "dpg-cvf3tfjqf0us73flfkv0-a";
$dbname = "chat_app_ltof";
$user = "chat_app_ltof_user";
$password = "JtFCFOztPWwHSS6wV4gXbTSzlV6barfq";
$port = 5432;

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Get logged-in username
$username = $_SESSION['username'];

// Fetch all other users (excluding the logged-in user)
$stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
$stmt->execute(['username' => $username]);
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Load last chat user if available
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
        <div id="chatBody" class="chat-body">⚙️ Loading chat...</div>

        <!-- Message Input -->
        <form id="chatForm" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit">➤</button>
        </form>
    </div>

</div>

<script>
    let currentChatUser = '<?= $lastChatUser ? htmlspecialchars($lastChatUser) : '' ?>';

    // Open chat with a user
    function openChat(user) {
        currentChatUser = user;
        document.getElementById('chatWith').innerText = `Chat with ${user}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat();

        // Store the user in session for future reference
        fetch('update_last_chat_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user=${encodeURIComponent(user)}`
        });
    }

    // Load chat messages
    function loadChat() {
        if (currentChatUser !== '') {
            fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error("Chat Error:", data.error);
                        document.getElementById('chatBody').innerHTML = `<p class='error'>⚠️ ${data.error}</p>`;
                        return;
                    }
                    document.getElementById('chatBody').innerHTML = data.messages || "<p class='notice'>No messages yet. Start chatting!</p>";
                    document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
                })
                .catch(err => {
                    console.error('Error loading chat:', err);
                    document.getElementById('chatBody').innerHTML = "<p class='error'>⚠️ Failed to load messages. Please try again!</p>";
                });
        }
    }

    // Send message without reloading
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
                    console.error("Send Error:", data.error);
                    document.getElementById('chatBody').innerHTML += `<p class='error'>⚠️ ${data.error}</p>`;
                    return;
                }
                document.getElementById('messageInput').value = ''; // Clear input
                loadChat(); // Reload chat to show the new message
            })
            .catch(err => console.error('Error sending message:', err));
        }
    }

    // Auto-refresh chat every second
    setInterval(loadChat, 1000);
</script>

</body>
</html>
