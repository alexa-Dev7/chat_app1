<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$username = $_SESSION['username'];
$messageFile = 'chats/messages.json';

if (!file_exists($messageFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Messages file not found.']);
    exit();
}

// Load messages
$messagesData = json_decode(file_get_contents($messageFile), true);
$inbox = [];

foreach ($messagesData as $chatKey => $messages) {
    if (strpos($chatKey, $username) !== false) {
        $lastMessage = end($messages);
        $inbox[] = [
            'chatKey' => $chatKey,
            'lastMessage' => $lastMessage['text'] ?? '',
            'timestamp' => $lastMessage['time'] ?? '',
            'receiver' => $lastMessage['receiver'] ?? '',
        ];
    }
}

echo json_encode(['status' => 'success', 'inbox' => $inbox]);
