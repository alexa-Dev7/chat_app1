<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    exit("Unauthorized");
}

$username = $_SESSION['username'];
$currentChatUser = $_GET['user'];
$messages = json_decode(file_get_contents('messages.json'), true);

// Mark messages as read
foreach ($messages as &$msg) {
    if ($msg['to'] === $username && $msg['from'] === $currentChatUser) {
        $msg['read'] = true;
    }
}
file_put_contents('messages.json', json_encode($messages));

?>

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

<form onsubmit="event.preventDefault(); sendMessage();">
    <input type="text" id="message" placeholder="Type a message..." autocomplete="off">
    <button type="submit">➤</button>
</form>
