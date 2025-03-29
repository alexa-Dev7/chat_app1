<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// Get the chat key (either username1-username2 or username2-username1)
$chatKey = isset($_GET['chatKey']) ? $_GET['chatKey'] : '';

// Validate chat key
if (empty($chatKey)) {
    echo json_encode(['status' => 'error', 'message' => 'Chat key is required']);
    exit();
}

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

// Check if the chat exists
if (!isset($messagesData[$chatKey])) {
    echo json_encode(['status' => 'error', 'message' => 'Chat not found']);
    exit();
}

// Return the messages for the chat
echo json_encode(['status' => 'success', 'messages' => $messagesData[$chatKey]]);
?>
