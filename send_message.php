<?php
session_start();
header('Content-Type: application/json');

try {
    // Ensure user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception('Unauthorized');
    }

    // Validate inputs
    if (empty($_POST['to']) || empty($_POST['message'])) {
        throw new Exception('Recipient or message missing');
    }

    // Sanitize data
    $sender = htmlspecialchars($_SESSION['username']);
    $recipient = htmlspecialchars($_POST['to']);
    $message = htmlspecialchars(trim($_POST['message']));
    $time = date('H:i:s');

    // Ensure chat folder exists
    $chatDir = 'chats';
    if (!is_dir($chatDir) && !mkdir($chatDir, 0777, true)) {
        throw new Exception('Failed to create chat directory');
    }

    chmod($chatDir, 0777);

    // Define chat file
    $filePath = "$chatDir/{$sender}_{$recipient}.json";
    if (!file_exists($filePath)) {
        if (file_put_contents($filePath, json_encode([])) === false) {
            throw new Exception('Failed to create chat file');
        }
        chmod($filePath, 0666);
    }

    // Load current messages
    $messages = json_decode(file_get_contents($filePath), true) ?? [];

    // Append new message
    $messages[] = [
        "sender" => $sender,
        "text" => $message,
        "time" => $time
    ];

    // Save messages back
    if (file_put_contents($filePath, json_encode($messages)) === false) {
        throw new Exception('Failed to save message');
    }

    // ✅ Return success response
    echo json_encode(["status" => "success", "message" => "Message sent!"]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());  // Logs to 'chats/error_log.txt'
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
