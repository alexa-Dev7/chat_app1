<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username']; // Logged-in user
$messageFile = 'chats/messages.json';

// Fetch messages
$messagesData = [];
if (file_exists($messageFile)) {
    $jsonData = file_get_contents($messageFile);
    $messagesData = json_decode($jsonData, true) ?: [];
}

// Prepare inbox data
$inbox = [];
foreach ($messagesData as $chatKey => $messages) {
    if (strpos($chatKey, $username) !== false) {
        $lastMessage = end($messages);
        $inbox[] = [
            'chatKey' => $chatKey,
            'lastMessage' => $lastMessage['text'] ?? '',
            'timestamp' => $lastMessage['time'] ?? '',
            'receiver' => $lastMessage['receiver'] ?? '',
        ];
    }
}

// Return JSON response
echo json_encode(["status" => "success", "inbox" => $inbox]);
?>
