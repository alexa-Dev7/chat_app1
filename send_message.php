<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$recipient = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent sending to self or sending empty messages
if ($recipient === $username || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Ensure the messages file exists
$filename = 'chats/messages.json';
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}

// Read current messages (log error if file is unreadable)
$messages = json_decode(file_get_contents($filename), true);
if ($messages === null) {
    echo json_encode(["error" => "Failed to load messages"]);
    error_log("Failed to decode JSON from $filename");
    exit();
}

// Ensure chat arrays exist
if (!isset($messages[$username][$recipient])) $messages[$username][$recipient] = [];
if (!isset($messages[$recipient][$username])) $messages[$recipient][$username] = [];

// Add the message to both sides (sender & recipient)
$newMessage = ['sender' => $username, 'text' => htmlspecialchars($message), 'time' => date("H:i")];
$messages[$username][$recipient][] = $newMessage;
$messages[$recipient][$username][] = $newMessage;

// Save messages back (log error if it fails)
if (!file_put_contents($filename, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["error" => "Failed to save message"]);
    error_log("Failed to write to $filename");
    exit();
}

echo json_encode(["success" => "Message sent!"]);
