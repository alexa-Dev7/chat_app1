<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// Path to the JSON file where messages are stored
$messageFile = 'chats/messages.json';

if (file_exists($messageFile)) {
    $jsonData = file_get_contents($messageFile);
    $messagesData = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Error reading chat data']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Chat data file not found']);
    exit();
}

// Prepare the inbox data
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

// Return the inbox data
echo json_encode(['status' => 'success', 'inbox' => $inbox]);
?>
