<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$currentUser = $_SESSION['username'];
$chatUser = $_GET['user'] ?? '';

if (empty($chatUser)) {
    echo json_encode(['error' => 'No chat user specified']);
    exit();
}

// Load messages
$filePath = 'chats/messages.json';
if (!file_exists($filePath)) {
    echo json_encode(['messages' => []]);
    exit();
}

$messages = json_decode(file_get_contents($filePath), true);

// Filter messages for this user pair only
$chatMessages = array_filter($messages, function ($msg) use ($currentUser, $chatUser) {
    return ($msg['sender'] === $currentUser && $msg['recipient'] === $chatUser) ||
           ($msg['sender'] === $chatUser && $msg['recipient'] === $currentUser);
});

echo json_encode(['messages' => array_values($chatMessages)]);
