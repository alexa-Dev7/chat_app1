<?php
// Secure load_chat.php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo "Error: Unauthorized access!";
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

// Ensure User A can only chat with User B (not snoop on other users)
if ($currentChatUser === $username) {
    echo "Error: You can't chat with yourself!";
    exit();
}

// Load messages data
$messages = json_decode(file_get_contents('persistent_data/messages.json'), true) ?? [];

// Build chat content — only allow chats where User A is part of the conversation
$chatContent = '';
foreach ($messages as $msg) {
    if (
        ($msg['from'] === $username && $msg['to'] === $currentChatUser) ||
        ($msg['from'] === $currentChatUser && $msg['to'] === $username)
    ) {
        $chatContent .= "<div class='message " . 
            ($msg['from'] === $username ? 'outgoing' : 'incoming') . 
            "'>" . htmlspecialchars($msg['text']) . "</div>";
    }
}

// If no messages exist between users, show a friendly message
if (empty($chatContent)) {
    $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
}

echo $chatContent;
