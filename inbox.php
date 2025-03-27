<?php 
session_start();

// Force error reporting for debugging
header('Content-Type: text/html; charset=UTF-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// Database connection (Update credentials)
$dbname = "dpg-cvgd5atrie7s73bog17g-a";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $db = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all registered users from PostgreSQL
$users = [];
try {
    $stmt = $db->query("SELECT username FROM users ORDER BY username ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Fetch inbox messages from JSON file
$messageFile = 'chats/messages.json';
$messagesData = [];

if (file_exists($messageFile)) {
    $jsonData = file_get_contents($messageFile);
    $messagesData = json_decode($jsonData, true) ?: [];
}

// Prepare inbox
$inbox = [];
foreach ($messagesData as $chatKey => $messages) {
    if (strpos($chatKey, $username) !== false) {
        $lastMessage = end($messages);
        $inbox[] = [
            'chatKey' => $chatKey,
            'lastMessage' => $lastMessage['text'] ?? '',
            'timestamp' => $lastMessage['time'] ?? '',
            'receiver' => $lastMessage['receiver'] ?? '',
        ];
    }
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
            <?php if (!empty($inbox)): ?>
                <?php foreach ($inbox as $chat): ?>
                    <div class="chat-item" data-chat-key="<?= htmlspecialchars($chat['chatKey']) ?>">
                        <strong><?= htmlspecialchars($chat['receiver']) ?></strong>: <?= htmlspecialchars($chat['lastMessage']) ?> <br>
                        <small><?= htmlspecialchars($chat['timestamp']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages yet.</p>
            <?php endif; ?>
        </div>

        <h3>All Users</h3>
        <ul id="userList">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <li class="user-item" data-username="<?= htmlspecialchars($user['username']) ?>">
                        <?= htmlspecialchars($user['username']) ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No users found.</li>
            <?php endif; ?>
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
    $(document).on('click', '.chat-item', function() {
        currentChatUser = $(this).data('chat-key').split('-')[1];
        document.getElementById('chatWith').innerText = `Chat with ${currentChatUser}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat($(this).data('chat-key'));
    });

    $(document).on('click', '.user-item', function() {
        currentChatUser = $(this).data('username');
        document.getElementById('chatWith').innerText = `Chat with ${currentChatUser}`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat(`${currentChatUser}`);
    });

    // Load chat messages
    async function loadChat(chatKey) {
        try {
            const response = await fetch(`load_chat.php?chatKey=${chatKey}`);
            const data = await response.json();
            if (data.status === 'success') {
                const chatBody = document.getElementById('chatBody');
                chatBody.innerHTML = '';
                data.messages.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message';
                    messageDiv.innerText = `${message.sender}: ${message.text} (${message.time})`;
                    chatBody.appendChild(messageDiv);
                });
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error loading chat:', error);
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

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    messageInput.value = '';
                    loadChat(currentChatUser);
                } else {
                    alert(data.message);
                }
            } catch (jsonError) {
                console.error("Invalid JSON response:", text);
                alert("Error: Invalid JSON response from the server.");
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    // Auto-refresh chat
    setInterval(loadInbox, 3000);

    async function loadInbox() {
        try {
            const response = await fetch('load_inbox.php');
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    const inboxContainer = document.getElementById('inbox');
                    inboxContainer.innerHTML = '';
                    data.inbox.forEach(chat => {
                        const chatItem = document.createElement('div');
                        chatItem.className = 'chat-item';
                        chatItem.dataset.chatKey = chat.chatKey;
                        chatItem.innerHTML = `<strong>${chat.receiver}</strong>: ${chat.lastMessage} <br><small>${chat.timestamp}</small>`;
                        inboxContainer.appendChild(chatItem);
                    });
                }
            } catch (jsonError) {
                console.error("Inbox JSON Error:", text);
            }
        } catch (error) {
            console.error('Error loading inbox:', error);
        }
    }
</script>

</body>
</html>
