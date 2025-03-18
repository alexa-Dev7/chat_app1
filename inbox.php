<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$messages = json_decode(file_get_contents('messages.json'), true);

// Get the list of users the current user has chatted with
$chattedUsers = [];
foreach ($messages as $msg) {
    if ($msg['from'] === $username && !in_array($msg['to'], $chattedUsers)) {
        $chattedUsers[] = $msg['to'];
    }
    if ($msg['to'] === $username && !in_array($msg['from'], $chattedUsers)) {
        $chattedUsers[] = $msg['from'];
    }
}

// Check if a user is selected for chat
$currentChatUser = $_GET['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Inbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <div class="user <?= $user === $currentChatUser ? 'active' : '' ?>"
                         onclick="openChat('<?= urlencode($user) ?>')">
                        <?= htmlspecialchars($user) ?>

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
                            <span class="new-message-indicator"></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window">
        <?php if ($currentChatUser): ?>
            <h3>Chat with <?= htmlspecialchars($currentChatUser) ?></h3>
            <div class="chat-body" id="chatBody">
                <?php foreach ($messages as $msg): ?>
                    <?php if (($msg['from'] === $username && $msg['to'] === $currentChatUser) ||
                        ($msg['from'] === $currentChatUser && $msg['to'] === $username)): ?>
                        <div class="message <?= $msg['from'] === $username ? 'outgoing' : 'incoming' ?>">
                            <?= htmlspecialchars($msg['text']) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <form method="post" action="send_message.php">
                <input type="hidden" name="to" value="<?= htmlspecialchars($currentChatUser) ?>">
                <input type="text" name="message" placeholder="Type a message...">
                <button type="submit">➤</button>
            </form>
        <?php else: ?>
            <!-- Empty chat placeholder when no user is selected -->
            <div class="no-chat-selected">
                <h2>👋 Start a conversation!</h2>
                <p>Select a user from the inbox to chat.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    // Open chat without page reload
    function openChat(user) {
        window.location.href = 'inbox.php?user=' + user;
    }

    // Auto-scroll chat to the latest message
    const chatBody = document.getElementById('chatBody');
    if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;

    // Short polling every 1 second to fetch new messages
    setInterval(() => {
        if ('<?= $currentChatUser ?>' !== '') {
            fetch('load_chat.php?user=<?= urlencode($currentChatUser) ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('chatBody').innerHTML = data;
                    chatBody.scrollTop = chatBody.scrollHeight;
                });
        }
    }, 1000);
</script>

</body>
</html>
