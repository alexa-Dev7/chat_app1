<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access!"]);
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

$stmt = $pdo->prepare(
    "SELECT sender, text, timestamp 
     FROM messages 
     WHERE (sender = :username AND recipient = :currentChatUser) 
     OR (sender = :currentChatUser AND recipient = :username) 
     ORDER BY timestamp ASC"
);

$stmt->execute([
    ':username' => $username,
    ':currentChatUser' => $currentChatUser
]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chatContent = '';
foreach ($messages as $msg) {
    $chatContent .= "<div class='message " . 
        ($msg['sender'] === $username ? 'outgoing' : 'incoming') . 
        "'>" . htmlspecialchars($msg['text']) . "</div>";
}

echo json_encode(["messages" => $chatContent]);
