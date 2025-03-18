<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_GET['user'] ?? 'Unknown User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/reset.css">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Chat with <?= htmlspecialchars($user) ?></title>
</head>

<body>
<div class="chat-container">

    <!-- Left Sidebar (User List) -->
    <div class="sidebar">
        <h2><?= $_SESSION['username'] ?></h2>
        <button class="new-convo">+ New Conversation</button>
        <input type="text" placeholder="Search" class="search-bar">
        <div class="user-list">
            <div class="user active">
                <img src="assets/avatar1.png" alt="User">
                <span>Jason Momoa</span>
                <small>Typing...</small>
            </div>
            <div class="user">
                <img src="assets/avatar2.png" alt="User">
                <span>Albert</span>
                <small>12 min ago</small>
            </div>
        </div>
    </div>

    <!-- Chat Section -->
    <div class="chat-window">
        <div class="chat-header">
            <h3><?= htmlspecialchars($user) ?></h3>
            <div class="chat-icons">
                📞 ⋮
            </div>
        </div>

        <div class="chat-body" id="chatBody">
            <div class="message outgoing">Did you mail me?</div>
            <div class="message incoming">Yes, I did...</div>
            <div class="message outgoing">Okay, let me check</div>
            <div class="message incoming">Please revert back ASAP</div>
        </div>

        <div class="chat-footer">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button onclick="sendMessage()">➤</button>
        </div>
    </div>
</div>

<script>
    function sendMessage() {
        const input = document.getElementById("messageInput");
        const message = input.value.trim();
        if (message !== "") {
            const chatBody = document.getElementById("chatBody");
            chatBody.innerHTML += `<div class='message outgoing'>${message}</div>`;
            input.value = "";
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }
</script>

</body>
</html>
