<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    echo json_encode(["error" => "Recipient and message are required"]);
    exit();
}

// Debugging output
error_log("From: $username | To: $to | Message: $message");

// Ensure messages file exists
$messagesFile = "chats/messages.json";
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, json_encode([]));
}

// Load messages
$messages = json_decode(file_get_contents($messagesFile), true);
if (!is_array($messages)) {
    echo json_encode(["error" => "Failed to read messages"]);
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

// Save the message
if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => $newMessage]);
} else {
    echo json_encode(["error" => "Failed to save message"]);
}
?>
