<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$recipient = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (empty($recipient) || empty($message)) {
    echo json_encode(["error" => "Recipient or message missing"]);
    exit();
}

// Define file path
$file = __DIR__ . '/chats/messages.json';

// Read existing messages
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

$messages = json_decode(file_get_contents($file), true);

if ($messages === null) {
    echo json_encode(["error" => "Failed to read message file"]);
    exit();
}

// Add the new message
$newMessage = [
    "sender" => $username,
    "recipient" => $recipient,
    "text" => $message,
    "time" => date('H:i:s')
];

$messages[] = $newMessage;

// Save back to file
if (file_put_contents($file, json_encode($messages, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(["error" => "Failed to save message"]);
    exit();
}

echo json_encode(["success" => "Message sent"]);
