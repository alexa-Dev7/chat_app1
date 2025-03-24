<?php
session_start();
if (!isset($_SESSION['username'])) {
    die(json_encode(["error" => "Unauthorized access"]));
}

$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    die(json_encode(["error" => "Recipient and message are required"]));
}

// Load or initialize the messages file
$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Create and save the new message
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];
$messages[] = $newMessage;

file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));
echo json_encode(["success" => true]);
?>
