<?php
session_start();
$username = $_SESSION['username'] ?? 'Guest'; // Default to "Guest" if no login

// Get the recipient and message content
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    die(json_encode(["error" => "Recipient and message are required"]));
}

// Ensure the messages folder exists
if (!is_dir('chats')) {
    mkdir('chats', 0777, true);
}

// Load existing messages
$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Create a new message entry
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => htmlspecialchars($message),
    "time" => date("H:i")
];

// Add the new message
$messages[] = $newMessage;

// Save messages back to the JSON file
if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to send message"]);
}
