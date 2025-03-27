<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];
$chatKey = $_GET['chatKey'] ?? '';

if (empty($chatKey)) {
    echo json_encode(['status' => 'error', 'message' => 'Chat key is missing']);
    exit();
}

// Path to the messages file
$messageFile = 'chats/messages.json';

if (!file_exists($messageFile)) {
    echo json_encode(['status' => 'error', 'message' => 'No messages found for this chat']);
    exit();
}

// Load messages
$messagesData = json_decode(file_get_contents($messageFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
    exit();
}

// Retrieve messages for the given chat key
$messages = $messagesData[$chatKey] ?? [];

if (empty($messages)) {
    echo json_encode(['status' => 'error', 'message' => 'No messages found']);
    exit();
}

// Send JSON response
echo json_encode(['status' => 'success', 'messages' => $messages]);
?>
