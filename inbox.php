<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$messages = json_decode(file_get_contents('messages.json'), true);

// Get users the current user has chatted with
$chattedUsers = [];
foreach ($messages as $msg) {
    if ($msg['from'] === $username && !in_array($msg['to'], $chattedUsers)) {
        $chattedUsers[] = $msg['to'];
    }
    if ($msg['to'] === $username && !in_array($msg['from'], $chattedUsers)) {
        $chattedUsers[] = $msg['from'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox</title>
</head>
<body>

<div class="chat-container">

    <!-- Inbox Sidebar -->
    <div class="sidebar">
        <h2><?= htmlspecialchars($username) ?></h2>

        <!-- User List (Inbox) -->
        <div class="user-list">
            <?php if (empty($chattedUsers)): ?>
                <p>No chats yet! Start a conversation.</p>
            <?php else: ?>
                <?php foreach ($chattedUsers as $user): ?>
                    <div class="user" onclick="selectUser('<?= urlencode($user) ?>')">
                        <strong><?= htmlspecialchars($user) ?></strong>

                        <!-- New message notification -->
                        <?php
                        $hasNewMessage = false;
                        foreach ($messages as $msg) {
                            if ($msg['to'] === $username && $msg['from'] === $user && !$msg['read']) {
                                $hasNewMessage = true;
                                break;
                            }
                        }
                        if ($hasNewMessage): ?>
                            <span class="new-message-indicator">🔴</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window">
        <div id="chat-content">
            <!-- Initially Empty -->
            <div class="no-chat-selected">
                <h2>👋 Start a conversation!</h2>
                <p>Select a user from the inbox to chat.</p>
            </div>
        </div>
    </div>

</div>

<script>
    let currentChatUser = null;

    function selectUser(user) {
        currentChatUser = user;
        loadChat(user);
    }

    function loadChat(user) {
        fetch(`load_chat.php?user=${user}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('chat-content').innerHTML = data;
                document.querySelectorAll('.new-message-indicator').forEach(indicator => indicator.remove());
            });
    }

    function sendMessage() {
        const message = document.getElementById("message").value;
        if (!message.trim()) return;

        fetch("send_message.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ to: currentChatUser, message })
        }).then(() => loadChat(currentChatUser));
    }

    // Real-time polling (1-second refresh)
    setInterval(() => {
        if (currentChatUser) loadChat(currentChatUser);
    }, 1000);
</script>

</body>
</html>
