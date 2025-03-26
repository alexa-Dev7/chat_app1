<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$username = $_SESSION['username'];

// Check if message data is provided
if (isset($_POST['to'], $_POST['message'])) {
    $to = trim($_POST['to']);
    $message = trim($_POST['message']);

    // Sanitize input
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Validate message input
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
        exit();
    }

    // Prepare the message data
    $newMessage = [
        'sender' => $username,
        'receiver' => $to,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // Read the current messages from the JSON file
    $messagesFile = 'chats/messages.json';
    if (file_exists($messagesFile)) {
        $messages = json_decode(file_get_contents($messagesFile), true);
    } else {
        $messages = [];
    }

    // Append the new message
    $messages[] = $newMessage;

    // Save the updated messages back to the file
    if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save message']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
