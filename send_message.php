<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$username = $_SESSION['username'];
$to = htmlspecialchars($_POST['to']);
$message = htmlspecialchars($_POST['message']);

$file = __DIR__ . "/chats/messages.json";
if (!file_exists($file)) file_put_contents($file, '[]');

$messages = json_decode(file_get_contents($file), true);
$messages[] = [
    "sender" => $username,
    "recipient" => $to,
    "text" => $message,
    "time" => date("H:i")
];

if (file_put_contents($file, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to save message"]);
}
