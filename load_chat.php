<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];
$to = $_GET['user'] ?? '';

$filename = "chats/messages.json";

if (!file_exists($filename)) {
    echo json_encode(['messages' => []]);
    exit();
}

$chats = json_decode(file_get_contents($filename), true);

$filteredMessages = array_filter($chats, fn($msg) =>
    ($msg['sender'] === $username && $msg['recipient'] === $to) ||
    ($msg['sender'] === $to && $msg['recipient'] === $username)
);

echo json_encode(['messages' => array_values($filteredMessages)]);
