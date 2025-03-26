<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$from = $_SESSION['username'];
$to = $_GET['user'] ?? '';

if (!$to) {
    echo json_encode(["error" => "User not found"]);
    exit();
}

$filename = "chats/{$from}_{$to}.json";
if (!file_exists($filename)) $filename = "chats/{$to}_{$from}.json";

// Load messages
$messages = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
echo json_encode(["messages" => $messages]);
