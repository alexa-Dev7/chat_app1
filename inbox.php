<?php
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

// Connect to PostgreSQL database
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Get the logged-in user
$username = $_SESSION['username'];

// Fetch all users except the current user
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

<div class="chat-container">

    <!-- Sidebar User List -->
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
        <form id="chatForm" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
            <button type="submit">➤</button>
        </form>
    </div>

</div>
fetch('https://chat-app-1-zm1t.onrender.com/load_chat.php')

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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    document.getElementById('chatBody').innerHTML = `<p class='error'>⚠️ ${data.error}</p>`;
                    return;
                }
                document.getElementById('chatBody').innerHTML = data.messages;
                document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
            })
            .catch(err => {
                console.error('Error loading chat:', err);
                document.getElementById('chatBody').innerHTML = `<p class='error'>⚠️ Failed to load messages. Check your internet connection.</p>`;
            });
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
            .then(() => {
                document.getElementById('messageInput').value = '';
                loadChat();
            });
        }
    }

    setInterval(loadChat, 1000);
</script>
<script>
    let retryCount = 0;
const maxRetries = 5;

function loadChatWithRetry() {
    loadChat().catch(() => {
        retryCount++;
        if (retryCount <= maxRetries) {
            console.log(`Retrying... (${retryCount}/${maxRetries})`);
            setTimeout(loadChatWithRetry, 2000); // retry every 2 seconds
        } else {
            console.error("Max retries reached. Check server or internet.");
        }
    });
}

</script>

</body>
</html>
