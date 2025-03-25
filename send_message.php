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

    // Sanitize inputs
    $sender = htmlspecialchars($_SESSION['username']);
    $recipient = htmlspecialchars($_POST['to']);
    $message = htmlspecialchars(trim($_POST['message']));
    $time = date('H:i:s');

    // Define the chat file as "username_recipient.json"
    $filePath = "chats/{$sender}_{$recipient}.json";

    // Ensure the chats directory exists
    if (!is_dir('chats')) {
        if (!mkdir('chats', 0777, true)) {
            throw new Exception('Failed to create chat directory');
        }
    }

    // Ensure the chat file exists and is writable
    if (!file_exists($filePath)) {
        if (file_put_contents($filePath, json_encode([])) === false) {
            throw new Exception('Failed to create chat file');
        }
        chmod($filePath, 0666);
    }

    if (!is_writable($filePath)) {
        throw new Exception('Chat file is not writable');
    }

    // Load existing messages
    $messages = json_decode(file_get_contents($filePath), true) ?? [];

    // Append the new message
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
    error_log("Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
