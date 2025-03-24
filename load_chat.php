<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$from = $_SESSION['username'];
$to = trim($_GET['user']);

// Define chat file path
$chatFile = "chats/" . (strcmp($from, $to) < 0 ? "{$from}_{$to}" : "{$to}_{$from}") . ".json";

// Debug: Check if file exists and is readable
if (!file_exists($chatFile)) {
    echo json_encode(["error" => "Chat file not found"]);
    exit();
}
if (!is_readable($chatFile)) {
    echo json_encode(["error" => "Chat file not readable"]);
    exit();
}

// Load chat data
$chatData = json_decode(file_get_contents($chatFile), true);
if (!is_array($chatData)) {
    echo json_encode(["error" => "Failed to parse chat data"]);
    exit();
}

// Build message bubbles
$output = "";
foreach ($chatData as $msg) {
    $isMine = ($msg['from'] === $from) ? "mine" : "theirs";
    $output .= "<div class='message-bubble $isMine'><strong>{$msg['from']}</strong>: {$msg['message']}<br><small>{$msg['timestamp']}</small></div>";
}

// Return messages or "No messages yet!"
echo json_encode(["messages" => $output ?: "<p class='empty-chat'>No messages yet!</p>"]);
