<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username'];
$recipient = $_POST['to'] ?? null;
$message = $_POST['message'] ?? null;

if (!$recipient || !$message) {
    echo json_encode(['error' => 'Recipient or message missing']);
    exit();
}

$chatFile = "chats/messages.json";

// Ensure file exists
if (!file_exists($chatFile)) {
    file_put_contents($chatFile, json_encode([]));
}

// Load existing messages
$chats = json_decode(file_get_contents($chatFile), true);

// Add new message
$chats[] = [
    'sender' => $username,
    'recipient' => $recipient,
    'text' => $message,
    'time' => date('H:i:s')
];

// Save back to file
if (file_put_contents($chatFile, json_encode($chats, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to save message']);
}
