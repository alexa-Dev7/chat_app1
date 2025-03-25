<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

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

// Ensure file exists
if (!file_exists($filename)) file_put_contents($filename, json_encode([]));

// Read messages file
$chats = json_decode(file_get_contents($filename), true);

// Append new message
$chats[] = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];

// Save messages back to file
if (file_put_contents($filename, json_encode($chats, JSON_PRETTY_PRINT))) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'error' => 'Failed to save message']);
}
