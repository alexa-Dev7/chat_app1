<?php
// Ensure proper content type for JSON response
header('Content-Type: application/json');

// Start session
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Check if 'to' and 'message' are set
if (!isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing recipient or message.']);
    exit();
}

$to = $_POST['to'];
$message = $_POST['message'];

// Path to the JSON file
$messagesFile = 'chats/messages.json';

// Read the existing messages
if (file_exists($messagesFile)) {
    $messagesData = json_decode(file_get_contents($messagesFile), true);
    if ($messagesData === null) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to decode existing messages.']);
        exit();
    }
} else {
    // Initialize an empty array if the file doesn't exist
    $messagesData = [];
}

// Add the new message to the messages array
$messagesData[] = [
    'sender' => $username,
    'receiver' => $to,
    'text' => $message,
    'time' => date('Y-m-d H:i:s')
];

// Write the updated messages back to the JSON file
if (file_put_contents($messagesFile, json_encode($messagesData, JSON_PRETTY_PRINT))) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save the message.']);
}
?>
