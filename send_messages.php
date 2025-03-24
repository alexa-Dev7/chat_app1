<?php
session_start();
$username = $_SESSION['username'] ?? 'Guest'; // Fallback to 'Guest' if not logged in

// Get the recipient and message content
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    die(json_encode(["error" => "Recipient and message are required"]));
}

// Load the existing messages
$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Format the new message
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => htmlspecialchars($message),
    "time" => date("H:i")
];

// Add the message to the array
$messages[] = $newMessage;

// Save back to messages.json
if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to send message"]);
}
