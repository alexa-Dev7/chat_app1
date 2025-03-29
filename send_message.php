<?php
// send_message.php
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$from = $_SESSION['username'];
$to = $_POST['to'];
$message = $_POST['message'];

$messageFile = 'chats/messages.json';

if (file_exists($messageFile)) {
    $messagesData = json_decode(file_get_contents($messageFile), true);
} else {
    $messagesData = [];
}

$chatKey = $from . '-' . $to;

if (!isset($messagesData[$chatKey])) {
    $messagesData[$chatKey] = [];
}

$messagesData[$chatKey][] = [
    'sender' => $from,
    'receiver' => $to,
    'text' => $message,
    'time' => date('Y-m-d H:i:s'),
];

file_put_contents($messageFile, json_encode($messagesData));

echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
?>
