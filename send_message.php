<?php
// Start session
session_start();

// Force JSON response
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Validate input
if (empty($_POST['to']) || empty($_POST['message'])) {
    echo json_encode(["status" => "error", "message" => "Recipient or message missing"]);
    exit();
}

// Sanitize data
$sender = htmlspecialchars($_SESSION['username']);
$recipient = htmlspecialchars($_POST['to']);
$message = htmlspecialchars(trim($_POST['message']));
$time = date('H:i:s');

// Ensure chat directory exists
$chatDir = 'chats';
if (!is_dir($chatDir) && !mkdir($chatDir, 0777, true)) {
    echo json_encode(["status" => "error", "message" => "Failed to create chat directory"]);
    exit();
}

// Ensure permissions on Render
chmod($chatDir, 0777);

// Define file path
$filePath = "$chatDir/{$sender}_{$recipient}.json";
if (!file_exists($filePath)) {
    if (file_put_contents($filePath, json_encode([])) === false) {
        echo json_encode(["status" => "error", "message" => "Failed to create chat file"]);
        exit();
    }
    chmod($filePath, 0666);
}

// Load existing messages
$messages = json_decode(file_get_contents($filePath), true) ?? [];

// Add new message
$messages[] = [
    "sender" => $sender,
    "text" => $message,
    "time" => $time
];

// Save updated messages
if (file_put_contents($filePath, json_encode($messages)) === false) {
    echo json_encode(["status" => "error", "message" => "Failed to save message"]);
    exit();
}

// Return success response
echo json_encode(["status" => "success", "message" => "Message sent!"]);
exit();
