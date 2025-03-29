<?php 
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Connect to PostgreSQL
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all registered users from PostgreSQL
$users = [];
try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username != :username");
    $stmt->execute(['username' => $username]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox | Messenger</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="chat-container">
    <!-- Sidebar with users -->
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?> <a href="logout.php">Logout</a></h2>
        <h3>Inbox</h3>
        <div id="inbox">
            <!-- Inbox content will be dynamically loaded here -->
        </div>

        <h3>All Users</h3>
        <ul id="userList">
            <?php foreach ($users as $user): ?>
                <li class="user-item" data-username="<?= htmlspecialchars($user['username']) ?>">
                    <?= htmlspecialchars($user['username']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
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
    $(document).on('click', '.user-item', function() {
        currentChatUser = $(this).data('username');
        document.getElementById('chatWith').innerText = `Chat with ${currentChatUser}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat(currentChatUser);
    });

    // Load chat messages
    async function loadChat(chatKey) {
        try {
            const response = await fetch(`load_chat.php?chatKey=${encodeURIComponent(chatKey)}`);
            const data = await response.json();

            if (data.status === 'success') {
                const chatBody = document.getElementById('chatBody');
                chatBody.innerHTML = '';

                if (data.messages.length === 0) {
                    chatBody.innerHTML = "<p>No chats available. Start a new message!</p>";
                } else {
                    data.messages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message';
                        messageDiv.innerText = `${message.sender}: ${message.text} (${message.timestamp})`;
                        chatBody.appendChild(messageDiv);
                    });
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error loading chat:', error);
            alert("Error fetching chat messages.");
        }
    }

    // Send a message
    async function sendMessage(event) {
        event.preventDefault();

        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value;

        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`,
            });

            const data = await response.json();
            if (data.status === 'success') {
                messageInput.value = ''; // Clear the message input
                loadChat(currentChatUser); // Reload the chat after sending the message
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    // Load inbox (get chat list)
    async function loadInbox() {
        try {
            const response = await fetch('load_inbox.php');
            const data = await response.json();

            if (data.status === 'success') {
                const inboxContainer = document.getElementById('inbox');
                inboxContainer.innerHTML = '';

                if (data.inbox.length === 0) {
                    alert("No chats found.");
                }

                data.inbox.forEach(chat => {
                    const chatItem = document.createElement('div');
                    chatItem.className = 'chat-item';
                    chatItem.dataset.chatKey = chat.chatKey;
                    chatItem.innerHTML = `<strong>${chat.receiver}</strong>: ${chat.lastMessage} <br><small>${chat.timestamp}</small>`;
                    inboxContainer.appendChild(chatItem);
                });
            }
        } catch (error) {
            console.error('Error loading inbox:', error);
        }
    }

    // Auto-refresh inbox every 3 seconds
    setInterval(loadInbox, 3000);
</script>
</body>
</html>
