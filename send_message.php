<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// Get the receiver username and message
$to = isset($_POST['to']) ? $_POST['to'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';

// Ensure both fields are provided
if (empty($to) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Receiver and message are required']);
    exit();
}

// Path to the JSON file where messages are stored
$messageFile = 'chats/messages.json';

if (file_exists($messageFile)) {
    $jsonData = file_get_contents($messageFile);
    $messagesData = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $messagesData = [];
    }
} else {
    $messagesData = [];
}

// Create chat key (format: username1-username2 or username2-username1)
$chatKey1 = $username . '-' . $to;
$chatKey2 = $to . '-' . $username;

if (!isset($messagesData[$chatKey1]) && !isset($messagesData[$chatKey2])) {
    // If chat doesn't exist, create it
    $messagesData[$chatKey1] = [];
}

$timestamp = date('Y-m-d H:i:s');
$messageData = [
    'sender' => $username,
    'receiver' => $to,
    'message' => $message,
    'time' => $timestamp
];

// Add message to chat
$messagesData[$chatKey1][] = $messageData;

// Save updated chat data to JSON file
file_put_contents($messageFile, json_encode($messagesData));

echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
?>
