<?php
session_start();

// Ensure user is logged in and target user is provided
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = trim($_GET['user']);
$filename = 'chats/messages.json';

// Ensure the file exists and is readable
if (!file_exists($filename)) {
    echo json_encode(["error" => "No messages found"]);
    exit();
}

// Load and decode messages (log error if decoding fails)
$messages = json_decode(file_get_contents($filename), true);
if ($messages === null) {
    echo json_encode(["error" => "Failed to load messages"]);
    error_log("Failed to decode JSON from $filename");
    exit();
}

// Return the chat messages (or empty array if none exist)
if (isset($messages[$username][$chatUser])) {
    echo json_encode(["messages" => $messages[$username][$chatUser]]);
} else {
    echo json_encode(["messages" => []]);
}
