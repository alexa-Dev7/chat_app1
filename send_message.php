<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if (!isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["status" => "error", "message" => "Recipient or message missing"]);
    exit();
}

$sender = $_SESSION['username'];
$recipient = $_POST['to'];
$message = trim($_POST['message']);

if ($message === "") {
    echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
    exit();
}

// Ensure chats folder and file exist
if (!file_exists('chats')) mkdir('chats');
$file = "chats/messages.json";
if (!file_exists($file)) file_put_contents($file, json_encode([]));

// Load existing messages
$messages = json_decode(file_get_contents($file), true);

$messages[] = [
    "sender" => $sender,
    "recipient" => $recipient,
    "text" => $message,
    "time" => date('Y-m-d H:i:s')
];

// Save messages back to file
if (file_put_contents($file, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["status" => "success", "message" => "Message sent"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save message"]);
}
