<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$username = $_SESSION['username'];
$receiver = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';
$messageFile = 'chats/messages.json';

if (empty($receiver) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message or receiver cannot be empty.']);
    exit();
}

// Read current messages
$messagesData = [];
if (file_exists($messageFile)) {
    $jsonData = file_get_contents($messageFile);
    $messagesData = json_decode($jsonData, true);
}

// Prepare chat key
$chatKey = (strpos($receiver, $username) < strpos($username, $receiver)) 
    ? "$username-$receiver" 
    : "$receiver-$username";

// Add the new message
$messagesData[$chatKey][] = [
    'sender' => $username,
    'receiver' => $receiver,
    'text' => $message,
    'time' => date('Y-m-d H:i:s')
];

// Save back to messages.json
if (file_put_contents($messageFile, json_encode($messagesData, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to write to messages.json']);
    exit();
}

echo json_encode(['status' => 'success']);
