<?php
header('Content-Type: application/json'); // Ensure the response is JSON
session_start(); // Start session

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

// Check if the required parameters are provided
if (!isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing recipient or message.']);
    exit();
}

$from = $_SESSION['username']; // The logged-in user (sender)
$to = $_POST['to']; // The recipient user
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
} else {
    $messagesData = [];
}

// Create a unique chat key for the conversation
$chatKey = $from < $to ? $from . '-' . $to : $to . '-' . $from; // Lexicographical order to avoid duplicates

// Initialize the chat if not already present
if (!isset($messagesData[$chatKey])) {
    $messagesData[$chatKey] = [];
}

// Add the new message to the conversation
$newMessage = [
    'sender' => $from,
    'receiver' => $to,
    'text' => $message,
    'time' => date('Y-m-d H:i:s') // Timestamp
];

// Append the new message to the chat
$messagesData[$chatKey][] = $newMessage;

// Save the updated messages to the JSON file
if (file_put_contents($messageFile, json_encode($messagesData, JSON_PRETTY_PRINT))) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save the message.']);
}

exit();
?>
