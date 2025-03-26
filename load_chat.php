<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$username = $_SESSION['username'];

// Check if the user parameter is provided
if (isset($_GET['user'])) {
    $user = trim($_GET['user']);

    // Read the messages from the JSON file
    $messagesFile = 'chats/messages.json';
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true);
    } else {
        echo json_encode(['error' => 'No messages found']);
        exit();
    }

    // Filter messages for the current user and the selected chat user
    $filteredMessages = array_filter($messages, function ($msg) use ($username, $user) {
        return ($msg['sender'] === $username && $msg['receiver'] === $user) ||
               ($msg['sender'] === $user && $msg['receiver'] === $username);
    });

    // Re-index the array
    $filteredMessages = array_values($filteredMessages);

    // Format the messages for JSON response
    $formattedMessages = [];
    foreach ($filteredMessages as $msg) {
        $formattedMessages[] = [
            'sender' => $msg['sender'],
            'text' => $msg['message'],
            'time' => $msg['timestamp']
        ];
    }

    echo json_encode(['messages' => $formattedMessages]);
} else {
    echo json_encode(['error' => 'User not specified']);
}
?>
