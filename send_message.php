<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username']; // Logged-in user
$message = $_POST['message'] ?? '';
$to = $_POST['to'] ?? '';

if (empty($message) || empty($to)) {
    echo json_encode(['status' => 'error', 'message' => 'Message and recipient cannot be empty']);
    exit();
}

// Path to the messages file
$messageFile = 'chats/messages.json';

// Fetch existing messages if the file exists
$messagesData = [];
if (file_exists($messageFile)) {
    $jsonData = file_get_contents($messageFile);
    $messagesData = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $messagesData = [];
    }
}

// Generate chat key
$chatKey = (strcmp($username, $to) < 0) ? $username . '-' . $to : $to . '-' . $username;

// Add new message to the appropriate chat
$timestamp = date('Y-m-d H:i:s');
$messagesData[$chatKey][] = [
    'sender' => $username,
    'receiver' => $to,
    'text' => $message,
    'time' => $timestamp,
];

// Attempt to write to the file
if (file_put_contents($messageFile, json_encode($messagesData, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to write message']);
    exit();
}

echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
?>
