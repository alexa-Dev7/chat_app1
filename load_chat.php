<?php
session_start();
header('Content-Type: application/json');
// Force JSON response and error handling
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Catch any fatal error or warning as JSON
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: $message"]);
    exit;
});

try {
    if (!isset($_SESSION['username'])) {
        throw new Exception('Unauthorized');
    }

    $sender = $_SESSION['username'];
    $recipient = htmlspecialchars($_GET['user'] ?? '');

    if (empty($recipient)) {
        throw new Exception('Recipient missing');
    }

    $filePath = "chats/{$sender}_{$recipient}.json";

    if (!file_exists($filePath)) {
        echo json_encode(["messages" => []]);
        exit();
    }

    $messages = json_decode(file_get_contents($filePath), true);

    if (!$messages) {
        throw new Exception('Failed to load chat');
    }

    echo json_encode(["messages" => $messages]);

} catch (Exception $e) {
    error_log("Error loading chat: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
