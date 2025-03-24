<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$from = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent sending to yourself or empty messages
if ($to === $from || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Define the chat file (sorted alphabetically for consistency)
$chatFile = "chats/" . (strcmp($from, $to) < 0 ? "{$from}_{$to}" : "{$to}_{$from}") . ".json";

// Load existing chat or create a new one
if (file_exists($chatFile)) {
    $chatData = json_decode(file_get_contents($chatFile), true);
} else {
    $chatData = [];
}

// Add the new message
$chatData[] = [
    "from" => $from,
    "to" => $to,
    "message" => htmlspecialchars($message),
    "timestamp" => date('Y-m-d H:i:s')
];

// Save back to the JSON file
if (file_put_contents($chatFile, json_encode($chatData, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => "Message sent!"]);
} else {
    echo json_encode(["error" => "Failed to send message"]);
}
