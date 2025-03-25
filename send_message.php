<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit();
}

if (!isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["status" => "error", "message" => "Recipient or message missing"]);
    exit();
}

$sender = $_SESSION['username'];
$recipient = $_POST['to'];
$message = trim($_POST['message']);
$time = date('H:i:s');

// Ensure the chats directory and file exist
if (!is_dir('chats')) {
    mkdir('chats', 0777, true);
}
$filePath = "chats/{$sender}_{$recipient}.json";
if (!file_exists($filePath)) {
    file_put_contents($filePath, json_encode([]));
    chmod($filePath, 0666);
}

try {
    // Read existing messages
    $messages = json_decode(file_get_contents($filePath), true);
    if (!$messages) $messages = [];

    // Add the new message
    $messages[] = [
        "sender" => $sender,
        "text" => $message,
        "time" => $time
    ];

    // Save the updated messages
    if (file_put_contents($filePath, json_encode($messages)) === false) {
        throw new Exception("Failed to save message");
    }

    echo json_encode(["status" => "success", "message" => "Message sent"]);
} catch (Exception $e) {
    http_response_code(500); // Set server error status
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
