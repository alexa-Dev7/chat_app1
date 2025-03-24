<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$from = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

if ($to === $from || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Define JSON filename (sorted alphabetically)
$chatFile = "chats/" . (strcmp($from, $to) < 0 ? "{$from}_{$to}" : "{$to}_{$from}") . ".json";

// Debug: Check file path & permissions
if (!is_writable('chats/')) {
    die(json_encode(["error" => "Chat folder is not writable!"]));
}

// Ensure file exists
if (!file_exists($chatFile)) {
    file_put_contents($chatFile, json_encode([]));
}

// Load or initialize chat data
$chatData = json_decode(file_get_contents($chatFile), true);
if (!is_array($chatData)) $chatData = [];

// Add new message
$chatData[] = [
    "from" => $from,
    "to" => $to,
    "message" => htmlspecialchars($message),
    "timestamp" => date('Y-m-d H:i:s')
];

// Save data back to JSON file
if (file_put_contents($chatFile, json_encode($chatData, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => "Message sent!"]);
} else {
    echo json_encode(["error" => "Failed to save message"]);
}
