<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$chatWith = $_GET['user'] ?? '';

if (!$chatWith) {
    echo json_encode(["error" => "No user selected"]);
    exit();
}

$messagesFile = "chats/messages.json";
if (!file_exists($messagesFile)) file_put_contents($messagesFile, json_encode([]));

$messages = json_decode(file_get_contents($messagesFile), true);
$filteredMessages = array_filter($messages, function ($msg) use ($username, $chatWith) {
    return ($msg['sender'] === $username && $msg['recipient'] === $chatWith) ||
           ($msg['sender'] === $chatWith && $msg['recipient'] === $username);
});

echo json_encode(["messages" => array_values($filteredMessages)]);
