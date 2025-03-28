<?php
session_start();
header('Content-Type: application/json');

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

$messagesFile = 'chats/messages.json';

// Check if file exists and is readable
if (!file_exists($messagesFile) || !is_readable($messagesFile)) {
    echo json_encode(['status' => 'error', 'message' => 'No messages found']);
    exit();
}

$messagesData = json_decode(file_get_contents($messagesFile), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($messagesData)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
    exit();
}

// Fetch chat messages
$chatMessages = $messagesData[$chatKey] ?? [];

echo json_encode(['status' => 'success', 'messages' => $chatMessages]);
?>
