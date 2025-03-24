<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$from = $_SESSION['username'];
$to = trim($_GET['user']);

// Define the chat file path
$chatFile = "chats/" . (strcmp($from, $to) < 0 ? "{$from}_{$to}" : "{$to}_{$from}") . ".json";

// Check if the file exists
if (file_exists($chatFile)) {
    $chatData = json_decode(file_get_contents($chatFile), true);
} else {
    $chatData = [];
}

// Build chat messages as bubbles
$output = "";
foreach ($chatData as $msg) {
    $isMine = ($msg['from'] === $from) ? "mine" : "theirs";
    $output .= "<div class='message-bubble $isMine'><strong>{$msg['from']}</strong>: {$msg['message']}<br><small>{$msg['timestamp']}</small></div>";
}

// Return messages or "No messages yet!"
echo json_encode(["messages" => $output ?: "<p class='empty-chat'>No messages yet!</p>"]);
