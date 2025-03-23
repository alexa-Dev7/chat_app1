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

// Ensure recipient exists
$stmt = $pdo->prepare("SELECT username FROM users WHERE username = :user");
$stmt->execute([':user' => $chatUser]);
if ($stmt->rowCount() === 0) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

// Fetch messages between the two users
try {
    $stmt = $pdo->prepare("
        SELECT sender, recipient, text, timestamp
        FROM messages
        WHERE (sender = :username AND recipient = :chatUser)
           OR (sender = :chatUser AND recipient = :username)
        ORDER BY timestamp ASC
    ");
    $stmt->execute([
        ':username' => $username,
        ':chatUser' => $chatUser
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chatHTML = "";
    foreach ($messages as $msg) {
        $isSender = ($msg['sender'] === $username);
        $chatHTML .= "<div class='message " . ($isSender ? "sent" : "received") . "'>";
        $chatHTML .= "<p>" . htmlspecialchars($msg['text']) . "</p>";
        $chatHTML .= "<span>" . date('Y-m-d H:i:s', strtotime($msg['timestamp'])) . "</span>";  // Format timestamp
        $chatHTML .= "</div>";
    }

    echo json_encode(["messages" => $chatHTML]);

} catch (PDOException $e) {
    error_log("❌ Load chat error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to load messages"]);
}
