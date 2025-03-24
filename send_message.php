<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$sender = $_SESSION['username'];
$recipient = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent sending empty messages or to yourself
if ($recipient === $sender || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Ensure "chats" folder exists
if (!file_exists('chats')) {
    mkdir('chats', 0777, true);
}

// Generate filename in alphabetical order
$chatFile = 'chats/' . (strcmp($sender, $recipient) < 0 
    ? "{$sender}_{$recipient}.json" 
    : "{$recipient}_{$sender}.json");

// Load existing messages or create new array
$messages = file_exists($chatFile) 
    ? json_decode(file_get_contents($chatFile), true) 
    : [];

// Add the new message
$messages[] = [
    'sender' => $sender,
    'text' => htmlspecialchars($message),
    'timestamp' => date('Y-m-d H:i:s')
];

// Save back to JSON file
file_put_contents($chatFile, json_encode($messages, JSON_PRETTY_PRINT));

echo json_encode(["success" => "Message sent!"]);
