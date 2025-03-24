<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent sending empty messages or messages to yourself
if ($to === $username || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Ensure chat file path is consistent
$usersSorted = [strtolower($username), strtolower($to)];
sort($usersSorted);
$chatFile = "chats/" . implode("_", $usersSorted) . ".json";

// Load existing messages
$messages = file_exists($chatFile) ? json_decode(file_get_contents($chatFile), true) : [];

// Add the new message
$messages[] = [
    "sender" => $username,
    "text" => htmlspecialchars($message),
    "timestamp" => date("Y-m-d H:i:s")
];

// Save updated messages
file_put_contents($chatFile, json_encode($messages));

echo json_encode(["success" => "Message sent!"]);
