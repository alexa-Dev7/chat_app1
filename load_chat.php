<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];
$to = $_GET['user'] ?? '';

$filename = "chats/messages.json";

// Ensure file exists
if (!file_exists($filename)) {
    echo json_encode(['messages' => []]);
    exit();
}

$chats = json_decode(file_get_contents($filename), true);

// Filter messages between these two users
$filteredMessages = array_filter($chats, fn($msg) =>
    ($msg['sender'] === $username && $msg['recipient'] === $to) ||
    ($msg['sender'] === $to && $msg['recipient'] === $username)
);

echo json_encode(['messages' => array_values($filteredMessages)]);
