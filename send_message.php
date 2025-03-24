<?php
session_start();
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

// Ensure file exists and is writable
$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// New message structure
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];

// Add new message to the list
$messages[] = $newMessage;
file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));

// Return success response
echo json_encode(["success" => true]);
?>
