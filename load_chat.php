<?php
session_start(); // Start session
header('Content-Type: application/json'); // Ensure the response is JSON

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
    exit();
}

// Check if the required parameters are provided
if (!isset($_GET['with'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing recipient.']);
    exit();
}

$with = trim($_GET['with']); // The other user in the chat
$username = $_SESSION['username']; // The logged-in user

// Path to the JSON file where messages are stored
$messageFile = 'chats/messages.json';

// Fetch existing messages (if any)
if (file_exists($messageFile)) {
    $messagesData = json_decode(file_get_contents($messageFile), true);
    
    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to decode messages: ' . json_last_error_msg()]);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Messages file not found.']);
    exit();
}

// Create a unique chat key based on the two users
$chatKey = $username . '-' . $with;

// Prepare the chat history
$chatHistory = isset($messagesData[$chatKey]) ? $messagesData[$chatKey] : [];

// Return the chat history as JSON
echo json_encode(['status' => 'success', 'chatHistory' => $chatHistory]);
exit();
?>
