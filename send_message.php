<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($to) || empty($message)) {
    echo json_encode(["error" => "Recipient or message missing"]);
    exit();
}

// Define file path
$filename = "chats/" . $username . "_" . $to . ".json";
if (!file_exists($filename)) {
    $filename = "chats/" . $to . "_" . $username . ".json";
}

// Prepare message
$messageData = [
    "sender" => $username,
    "text" => htmlspecialchars($message),
    "time" => date("Y-m-d H:i:s")
];

// Read old messages and append new one
$messages = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
$messages[] = $messageData;

// Save to JSON file (error handling added)
if (file_put_contents($filename, json_encode($messages, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(["error" => "Failed to save message"]);
} else {
    echo json_encode(["status" => "success", "message" => "Message sent!"]);
}
