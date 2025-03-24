<?php
session_start();

// Ensure the session is running, but no auth checks
$username = $_SESSION['username'] ?? 'Guest';
$chatUser = $_GET['user'] ?? '';

if (!$chatUser) {
    die(json_encode(["error" => "Chat user not specified"]));
}

// Load the messages
$messagesFile = "chats/messages.json";
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// Get messages between current user and chat user
$chatMessages = array_filter($messages, function ($msg) use ($username, $chatUser) {
    return ($msg['sender'] === $username && $msg['recipient'] === $chatUser) ||
           ($msg['sender'] === $chatUser && $msg['recipient'] === $username);
});

// If no messages, show placeholder
if (!$chatMessages) {
    echo json_encode(["messages" => "<p style='text-align:center; color: #555;'>No messages yet!</p>"]);
    exit;
}

// Format messages into bubbles
$output = "";
foreach ($chatMessages as $msg) {
    $isMine = $msg['sender'] === $username ? 'mine' : 'theirs';
    $output .= "<div class='message $isMine'><strong>{$msg['sender']}</strong>: {$msg['text']} <span class='timestamp'>{$msg['time']}</span></div>";
}

echo json_encode(["messages" => $output]);
