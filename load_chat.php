<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = $_GET['user'] ?? '';

$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Filter messages between the current user and selected user
$filteredMessages = array_filter($messages, function ($msg) use ($username, $chatUser) {
    return ($msg['sender'] === $username && $msg['recipient'] === $chatUser) ||
           ($msg['sender'] === $chatUser && $msg['recipient'] === $username);
});

// Return messages as JSON
echo json_encode(["messages" => $filteredMessages]);
?>
