<?php
session_start(); // Start session
header('Content-Type: application/json'); // Ensure the response is JSON

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
    exit();
}

// Check if the required parameters are provided
if (!isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing recipient or message.']);
    exit();
}

$from = $_SESSION['username']; // The logged-in user (sender)
$to = trim($_POST['to']); // The recipient user
$message = trim($_POST['message']); // The message content

// Validate the message content
if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
    exit();
}

// Path to the JSON file where messages are stored
$messageFile = 'chats/messages.json';

// Fetch existing messages (if any)
if (file_exists($messageFile)) {
    $messagesData = json_decode(file_get_contents($messageFile), true);
    
    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to decode existing messages: ' . json_last_error_msg()]);
        exit();
    }
} else {
    $messagesData = []; // Initialize an empty array if the file does not exist
}

// Create a unique chat key based on sender and receiver
 $chatKey = $from . '-' . $to;

// Prepare the new message data
$newMessage = [
    'sender' => $from,
    'receiver' => $to,
    'text' => $message,
    'time' => date('Y-m-d H:i:s') // Current timestamp
];

// Add the new message to the appropriate chat
if (!isset($messagesData[$chatKey])) {
    $messagesData[$chatKey] = []; // Initialize the chat if it doesn't exist
}
$messagesData[$chatKey][] = $newMessage; // Append the new message

// Save the updated messages back to the JSON file
if (file_put_contents($messageFile, json_encode($messagesData, JSON_PRETTY_PRINT))) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save message.']);
}
exit();
?>
