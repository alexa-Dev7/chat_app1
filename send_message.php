<?php
// send_message.php
header('Content-Type: application/json');

// Get the logged-in user and message to send
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$from = $_SESSION['username'];
$to = $_POST['to'];
$message = $_POST['message'];

// Simulating saving the message to a file (or database)
$messagesFile = 'chats/messages.json';
if (file_exists($messagesFile)) {
    $messagesData = json_decode(file_get_contents($messagesFile), true);
} else {
    $messagesData = [];
}

// Create a new chat entry if it doesn't exist
$chatKey = $from . '-' . $to;
if (!isset($messagesData[$chatKey])) {
    $messagesData[$chatKey] = [];
}

// Add the new message
$messagesData[$chatKey][] = [
    'sender' => $from,
    'receiver' => $to,
    'text' => $message,
    'time' => date('Y-m-d H:i:s'),
];

// Save the updated messages back to the file
file_put_contents($messagesFile, json_encode($messagesData));

echo json_encode(['status' => 'success', 'message' => 'Message sent successfully']);
?>
