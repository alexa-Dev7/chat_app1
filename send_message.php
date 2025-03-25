<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'error' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$to || !$message) {
    echo json_encode(['status' => 'error', 'error' => 'Invalid input']);
    exit();
}

$filename = "chats/messages.json";

if (!file_exists($filename)) file_put_contents($filename, json_encode([]));

$chats = json_decode(file_get_contents($filename), true);

$chats[] = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];

if (file_put_contents($filename, json_encode($chats, JSON_PRETTY_PRINT))) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'error' => 'Failed to save message']);
}
