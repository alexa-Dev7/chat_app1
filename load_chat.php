<?php
session_start();
if (!isset($_SESSION['username'])) {
    die(json_encode(["error" => "Unauthorized access"]));
}

$username = $_SESSION['username'];
$chatUser = $_GET['user'] ?? '';

if (!$chatUser) {
    die(json_encode(["error" => "Chat user not specified"]));
}

$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Filter messages between current user and selected user
$chatMessages = array_filter($messages, function ($msg) use ($username, $chatUser) {
    return ($msg['sender'] === $username && $msg['recipient'] === $chatUser) ||
           ($msg['sender'] === $chatUser && $msg['recipient'] === $username);
});

echo json_encode(["messages" => array_values($chatMessages)]);
?>
