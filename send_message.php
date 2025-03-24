<?php
session_start();
header('Content-Type: application/json');

// Show all errors (for debugging only!)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

// Capture data
$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    echo json_encode(["error" => "Recipient and message are required"]);
    exit();
}

// Debug output
error_log("✅ Sending From: $username | To: $to | Message: $message");

// Ensure the messages file exists
$messagesFile = "chats/messages.json";
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, json_encode([]));
}

// Load messages
$messages = json_decode(file_get_contents($messagesFile), true);
if (!is_array($messages)) {
    echo json_encode(["error" => "Failed to read messages file"]);
    exit();
}

// Add the new message
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];
$messages[] = $newMessage;

// Save the updated messages
if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => $newMessage]);
} else {
    echo json_encode(["error" => "Failed to save message"]);
}
