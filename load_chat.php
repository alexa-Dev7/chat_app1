<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = trim($_GET['user']);
$filename = 'chats/messages.json';

if (!file_exists($filename)) {
    echo json_encode(["error" => "No messages found"]);
    exit();
}

// Load and retrieve chat messages
$messages = json_decode(file_get_contents($filename), true);
if (isset($messages[$username][$chatUser])) {
    echo json_encode(["messages" => $messages[$username][$chatUser]]);
} else {
    echo json_encode(["messages" => []]);
}
