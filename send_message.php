<?php
session_start();
header('Content-Type: application/json');

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

$messagesFile = "chats/messages.json";
if (!file_exists($messagesFile)) file_put_contents($messagesFile, json_encode([]));

$messages = json_decode(file_get_contents($messagesFile), true);
$newMessage = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];
$messages[] = $newMessage;

if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true, "message" => $newMessage]);
} else {
    echo json_encode(["error" => "Failed to save message"]);
}
