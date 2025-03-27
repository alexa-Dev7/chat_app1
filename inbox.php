<?php
session_start(); // Start session

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$username = $_SESSION['username']; // Get the logged-in username

// Path to the JSON file where messages are stored
$messageFile = 'chats/messages.json';

// Fetch existing messages (if any)
if (file_exists($messageFile)) {
    $messagesData = json_decode(file_get_contents($messageFile), true);
    
    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $messagesData = []; // If there's an error, initialize as empty
    }
} else {
    $messagesData = []; // Initialize an empty array if the file does not exist
}

// Prepare the inbox data
$inbox = [];
foreach ($messagesData as $chatKey => $messages) {
    // Check if the current user is involved in the conversation
    if (strpos($chatKey, $username) !== false) {
        $lastMessage = end($messages); // Get the last message in the conversation
        $inbox[] = [
            'chatKey' => $chatKey,
            'lastMessage' => $lastMessage['text'],
            'timestamp' => $lastMessage['time'],
            'receiver' => $lastMessage['receiver'],
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
    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
        }
        .chat-container {
            display: flex;
        }
        .sidebar {
            width: 200px;
            border-right: 1px solid #ccc;
            padding: 10px;
        }
        .chat-window {
            flex: 1;
            padding: 10px;
        }
        .chat-item {
            cursor: pointer;
            padding: 5px;
            border-bottom: 1px solid #ccc;
        }
        .chat-item:hover {
            background-color: #f0f0f0;
        }
        .message {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <!-- Sidebar with users -->
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?> <a href="logout.php">Logout</a></h2>
        <h3>Inbox</h3>
        <div id="inbox">
            <?php foreach ($inbox as $chat): ?>
                <div class="chat-item" data-chat-key="<?= htmlspecialchars($chat['chatKey']) ?>">
                    <strong><?= htmlspecialchars($chat['receiver']) ?></strong>: <?= htmlspecialchars($chat['lastMessage']) ?> <br>
                    <small><?= htmlspecialchars($chat['timestamp']) ?></small>
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
    let currentChatUser  = '';

    // Open a chat with a selected user
    $(document).on('click', '.chat-item', function() {
        currentChatUser  = $(this).data('chat-key').split('-')[1]; // Get the receiver from the chat key
        document.getElementById('chatWith').innerText = `Chat with ${currentChatUser }`;
        document.getElementById('chatWindow').style.display = 'block';
        loadChat($(this).data('chat-key'));
    });

    // Load messages from JSON file
    async function loadChat(chatKey) {
        try {
            const response = await fetch(`load_chat.php?chatKey=${chatKey}`);
            const data = await response.json();
            if (data.status === 'success') {
                const chatBody = document.getElementById('chatBody');
                chatBody.innerHTML = ''; // Clear previous messages
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
                body: `to=${currentChatUser }&message=${encodeURIComponent(message)}`,
            });
            const data = await response.json();
            if (data.status === 'success') {
                messageInput.value = ''; // Clear input
                loadChat(currentChatUser ); // Reload chat messages
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }
</script>

</body>
</html>
