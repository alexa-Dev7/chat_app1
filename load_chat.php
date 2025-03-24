<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = $_GET['user'] ?? '';

if (!$chatUser) {
    echo json_encode(["error" => "Chat user not specified"]);
    exit();
}

$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

$chatMessages = array_filter($messages, function ($msg) use ($username, $chatUser) {
    return ($msg['sender'] === $username && $msg['recipient'] === $chatUser) ||
           ($msg['sender'] === $chatUser && $msg['recipient'] === $username);
});

echo json_encode(["messages" => array_values($chatMessages)]);
?>
