<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in and a user to chat with is provided
if (!isset($_SESSION['username'], $_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = trim($_GET['user']);

// Fetch user IDs for sender and recipient
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'];

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :chatUser");
$stmt->execute([':chatUser' => $chatUser]);
$chatUserId = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$chatUserId) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

$chatUserId = $chatUserId['id'];

// Fetch messages between the two users
try {
    $stmt = $pdo->prepare("
        SELECT sender, recipient, text, timestamp
        FROM messages
        WHERE (sender = :userId AND recipient = :chatUserId)
           OR (sender = :chatUserId AND recipient = :userId)
        ORDER BY timestamp ASC
    ");
    $stmt->execute([
        ':userId' => $userId,
        ':chatUserId' => $chatUserId
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chatHTML = "";
    foreach ($messages as $msg) {
        $isSender = ($msg['sender'] === $userId);
        $chatHTML .= "<div class='message " . ($isSender ? "sent" : "received") . "'>";
        $chatHTML .= "<p>" . htmlspecialchars($msg['text']) . "</p>";
        $chatHTML .= "<span>" . $msg['timestamp'] . "</span>";
        $chatHTML .= "</div>";
    }

    echo json_encode(["messages" => $chatHTML]);

} catch (PDOException $e) {
    error_log("❌ Load chat error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to load messages"]);
}
