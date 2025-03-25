<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    echo json_encode(["error" => "Recipient and message are required"]);
    exit();
}

// Define message file path
$messagesFile = "chats/messages.json";

// Ensure 'chats/' folder exists
if (!is_dir("chats")) {
    mkdir("chats", 0777, true); // Create folder with full permissions
}

// Ensure messages.json exists and has correct permissions
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, json_encode([]));
}

// Force permissions to be writable
chmod($messagesFile, 0666); 

// Read messages or reset if corrupted
$messages = json_decode(file_get_contents($messagesFile), true);
if (!is_array($messages)) $messages = []; 

// Add new message
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];
$messages[] = $newMessage;

// Save back to JSON file
if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => $newMessage]);
} else {
    echo json_encode(["error" => "Failed to save message"]);
}
