<?php
// load_chat.php — Secure, clean, ready for encryption!

ob_start();
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

// Get current user and chat partner
$username = $_SESSION['username'];
$currentChatUser = $_GET['user'] ?? null;

if (!$currentChatUser || $currentChatUser === $username) {
    echo json_encode(["error" => "Invalid chat request"]);
    exit();
}

// Load messages
$messagesFile = 'persistent_data/messages.json';
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Check if conversation exists between these two users
if (
    !isset($messages[$username][$currentChatUser]) &&
    !isset($messages[$currentChatUser][$username])
) {
    echo json_encode(["error" => "Unauthorized chat"]);
    exit();
}

// Load the conversation
$conversation = $messages[$username][$currentChatUser] ?? $messages[$currentChatUser][$username] ?? [];

// Format chat messages (supports encryption placeholders)
$chatContent = '';
foreach ($conversation as $msg) {
    $isOwn = $msg['from'] === $username;
    $text = htmlspecialchars($msg['text']);

    // Placeholder for future decryption logic
    // $text = decryptMessage($msg['text']);

    $chatContent .= "<div class='message " . ($isOwn ? 'outgoing' : 'incoming') . "'>{$text}</div>";
}

// Output clean content (no accidental output errors)
echo $chatContent;
ob_end_flush();
