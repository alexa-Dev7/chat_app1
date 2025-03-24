<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$recipient = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent empty messages
if ($recipient === $username || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Load current messages file
$filename = 'chats/messages.json';
$messages = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];

// Create a new chat entry if missing
if (!isset($messages[$username][$recipient])) $messages[$username][$recipient] = [];
if (!isset($messages[$recipient][$username])) $messages[$recipient][$username] = [];

// Save message for both sender and recipient
$newMessage = ['sender' => $username, 'text' => htmlspecialchars($message)];
$messages[$username][$recipient][] = $newMessage;
$messages[$recipient][$username][] = $newMessage;

// Write back to the JSON file
file_put_contents($filename, json_encode($messages, JSON_PRETTY_PRINT));

echo json_encode(["success" => "Message sent!"]);
