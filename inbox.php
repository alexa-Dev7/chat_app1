<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$users = json_decode(file_get_contents('persistent_data/users.json'), true) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox | Messenger</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset and basics */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }

        a {
            color: #007aff;
            text-decoration: none;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 450px;
            border: 1px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            background: #fff;
        }

        /* Sidebar */
        .sidebar {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            background: #f4f4f4;
            text-align: center;
        }

        .sidebar h2 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .user-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .user {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #eaeaea;
            cursor: pointer;
            transition: 0.3s;
        }

        .user:hover {
            background: #ddd;
        }

        .message-btn {
            background: #007aff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .message-btn:hover {
            background: #005bb5;
        }

        /* Chat window */
        .chat-window {
            display: none;
            flex-direction: column;
            height: 400px;
            overflow: hidden;
        }

        .chat-window h3 {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            background: #f4f4f4;
            font-size: 16px;
        }

        .chat-body {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
            background: #fff;
        }

        .message {
            margin: 5px 0;
            padding: 8px 10px;
            border-radius: 10px;
            max-width: 70%;
            word-break: break-word;
        }

        .incoming {
            background: #e5e5ea;
            text-align: left;
        }

        .outgoing {
            background: #007aff;
            color: #fff;
            text-align: right;
            margin-left: auto;
        }

        /* Message input form */
        #chatForm {
            display: flex;
            border-top: 1px solid #ddd;
        }

        #messageInput {
            flex: 1;
            padding: 10px;
            border: none;
            outline: none;
            font-size: 16px;
        }

        #chatForm button {
            background: #007aff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
        }

        #chatForm button:hover {
            background: #005bb5;
        }

        /* Responsive adjustments */
        @media (max-width: 450px) {
            .chat-container {
                height: 100%;
                border-radius: 0;
            }

            .sidebar h2 {
                font-size: 16px;
            }

            .message-btn {
                padding: 3px 7px;
            }

            .chat-window h3 {
                font-size: 14px;
            }

            #messageInput {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

<div class="chat-container">

    <!-- Sidebar with user list -->
    <div class="sidebar">
        <h2>👤 <?= htmlspecialchars($username) ?> <a href="logout.php">Logout</a></h2>
        <h3>All Users</h3>
        <div class="user-list">
            <?php foreach ($users as $user => $data): ?>
                <?php if ($user !== $username): ?>
                    <div class="user">
                        <span><?= htmlspecialchars($user) ?></span>
                        <button class="message-btn" onclick="openChat('<?= htmlspecialchars($user) ?>')">Message</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat Window (Initially Hidden) -->
    <div class="chat-window" id="chatWindow">
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

    // Open chat window immediately when clicking the button
    function openChat(user) {
        currentChatUser = user;
        document.getElementById('chatWith').innerText = `Chat with ${user}`;
        document.getElementById('chatWindow').style.display = 'flex';
        loadChat();
    }

    // Load chat messages (polls every second)
    function loadChat() {
        if (currentChatUser !== '') {
            fetch(`load_chat.php?user=${encodeURIComponent(currentChatUser)}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('chatBody').innerHTML = data;
                    document.getElementById('chatBody').scrollTop = document.getElementById('chatBody').scrollHeight;
                });
        }
    }

    // Send a message without page reload
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

    // Auto-refresh chat every second
    setInterval(loadChat, 1000);
</script>

</body>
</html>
