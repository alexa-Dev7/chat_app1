<?php
session_start(); // Start session
header('Content-Type: application/json'); // Ensure the response is JSON

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User  not logged in.']);
    exit();
}

$chatKey = isset($_GET['chatKey']) ? trim($_GET['chatKey']) : ''; // Get the chat key from the request

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

// Check if the chat key exists
if (isset($messagesData[$chatKey])) {
    echo json_encode(['status' => 'success', 'messages' => $messagesData[$chatKey]]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No messages found for this chat.']);
}
?>
