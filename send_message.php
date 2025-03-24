<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$from = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (empty($to) || empty($message)) {
    echo json_encode(['error' => 'Empty message or recipient']);
    exit();
}

// Path to the messages file
$filePath = 'chats/messages.json';

// Ensure file exists
if (!file_exists($filePath)) {
    file_put_contents($filePath, json_encode([]));
}

// Load existing messages
$messages = json_decode(file_get_contents($filePath), true);

// Add new message to the file
$newMessage = [
    'sender' => $from,
    'recipient' => $to,
    'text' => htmlspecialchars($message),
    'time' => date('H:i:s')
];

$messages[] = $newMessage;
file_put_contents($filePath, json_encode($messages, JSON_PRETTY_PRINT));

echo json_encode(['success' => true]);
