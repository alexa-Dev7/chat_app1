<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$from = $_SESSION['username'];
$to = $_POST['to'] ?? null;
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    echo json_encode(["status" => "error", "message" => "Recipient or message missing"]);
    exit();
}

// Set file path
$filename = "chats/{$from}_{$to}.json";
if (!file_exists($filename)) $filename = "chats/{$to}_{$from}.json";

$messageData = [
    "sender" => $from,
    "text" => $message,
    "time" => date('H:i:s')
];

// Ensure directory exists and is writable
if (!is_dir('chats')) mkdir('chats', 0777, true);

// Save message
$messages = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
$messages[] = $messageData;

if (file_put_contents($filename, json_encode($messages, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(["status" => "error", "message" => "Failed to save message"]);
    exit();
}

echo json_encode(["status" => "success", "message" => "Message sent!"]);
