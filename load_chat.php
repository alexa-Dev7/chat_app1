<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$loggedInUser = $_SESSION['username'];
$chatWith = trim($_GET['user']);

// Ensure the chat file exists in alphabetical order
$chatFile = 'chats/' . (strcmp($loggedInUser, $chatWith) < 0 
    ? "{$loggedInUser}_{$chatWith}.json" 
    : "{$chatWith}_{$loggedInUser}.json");

if (!file_exists($chatFile)) {
    echo json_encode(["messages" => "<p>No messages yet!</p>"]);
    exit();
}

// Load and format messages
$messages = json_decode(file_get_contents($chatFile), true);

$html = '';
foreach ($messages as $msg) {
    $isMine = ($msg['sender'] === $loggedInUser) ? 'my-message' : 'their-message';
    $html .= "<div class='{$isMine}'><strong>{$msg['sender']}:</strong> " 
        . htmlspecialchars($msg['text']) 
        . " <span class='timestamp'>{$msg['timestamp']}</span></div>";
}

echo json_encode(["messages" => $html]);
