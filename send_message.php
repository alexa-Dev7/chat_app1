<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$to = $_POST['to'] ?? '';
$message = trim($_POST['message'] ?? '');

if (empty($to) || empty($message)) {
    echo json_encode(["error" => "Recipient or message missing"]);
    exit();
}

$filename = "chats/" . $username . "_" . $to . ".json";
if (!file_exists($filename)) {
    $filename = "chats/" . $to . "_" . $username . ".json";
}

$messageData = [
    "sender" => $username,
    "text" => htmlspecialchars($message),
    "time" => date("Y-m-d H:i:s")
];

$messages = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
$messages[] = $messageData;

if (file_put_contents($filename, json_encode($messages, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(["error" => "Failed to save message — check file permissions!"]);
} else {
    echo json_encode(["status" => "success", "message" => "Message sent!"]);
}
