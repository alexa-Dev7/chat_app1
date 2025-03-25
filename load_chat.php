<?php
session_start();
header('Content-Type: application/json');

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
