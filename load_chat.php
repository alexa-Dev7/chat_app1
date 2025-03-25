<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = htmlspecialchars($_GET['user']);

$file = __DIR__ . "/chats/messages.json";
if (!file_exists($file)) {
    echo json_encode(["messages" => []]);
    exit();
}

$messages = json_decode(file_get_contents($file), true);
$chatMessages = array_filter($messages, function($msg) use ($username, $chatUser) {
    return ($msg['sender'] === $username && $msg['recipient'] === $chatUser) ||
           ($msg['sender'] === $chatUser && $msg['recipient'] === $username);
});

echo json_encode(["messages" => array_values($chatMessages)]);
