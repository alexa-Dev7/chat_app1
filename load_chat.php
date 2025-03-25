<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$currentUser = $_SESSION['username'];
$chatUser = $_GET['user'];

$file = "chats/messages.json";
if (!file_exists($file)) file_put_contents($file, json_encode([]));

$messages = json_decode(file_get_contents($file), true);

$chatMessages = array_filter($messages, function($msg) use ($currentUser, $chatUser) {
    return ($msg['sender'] === $currentUser && $msg['recipient'] === $chatUser) || 
           ($msg['sender'] === $chatUser && $msg['recipient'] === $currentUser);
});

echo json_encode(["messages" => array_values($chatMessages)]);
