<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username'];
$chatUser = $_GET['user'] ?? null;

if (!$chatUser) {
    echo json_encode(['error' => 'No user selected']);
    exit();
}

$chatFile = "chats/messages.json";

if (!file_exists($chatFile)) {
    echo json_encode(['messages' => []]);
    exit();
}

// Load chat history
$chats = json_decode(file_get_contents($chatFile), true);

$filteredChats = array_filter($chats, function ($chat) use ($username, $chatUser) {
    return ($chat['sender'] === $username && $chat['recipient'] === $chatUser) ||
           ($chat['sender'] === $chatUser && $chat['recipient'] === $username);
});

echo json_encode(['messages' => array_values($filteredChats)]);
