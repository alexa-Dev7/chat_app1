<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$messages = json_decode(file_get_contents('messages.json'), true);

// Get list of users the current user has chatted with
$chattedUsers = [];
foreach ($messages as $msg) {
    if ($msg['from'] === $username && !in_array($msg['to'], $chattedUsers)) {
        $chattedUsers[] = $msg['to'];
    }
    if ($msg['to'] === $username && !in_array($msg['from'], $chattedUsers)) {
        $chattedUsers[] = $msg['from'];
    }
}

// Load conversation with selected user
$currentChatUser = $_GET['user'] ?? $chattedUsers[0] ?? null;

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
            <?php foreach ($chattedUsers as $user): ?>
                <div class="user <?= $user === $currentChatUser ? 'active' : '' ?>">
                    <a href="chat.php?user=<?= urlencode($user) ?>">
                        <?= htmlspecialchars($user) ?>
                    </a>

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
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window">
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
    </div>
</div>

</body>
</html>
