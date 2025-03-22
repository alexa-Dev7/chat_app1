<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in and a chat partner is provided
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access!"]);
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

try {
    // Ensure the user exists
    $checkUser = $pdo->prepare("SELECT username FROM users WHERE username = :user");
    $checkUser->execute([':user' => $currentChatUser]);

    if ($checkUser->rowCount() === 0) {
        echo json_encode(["error" => "Chat user not found"]);
        exit();
    }

    // Fetch messages between users
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

    // Build chat content
    $chatContent = '';
    if ($messages) {
        foreach ($messages as $msg) {
            $chatContent .= "<div class='message " . 
                ($msg['sender'] === $username ? 'outgoing' : 'incoming') . 
                "'><strong>" . htmlspecialchars($msg['sender']) . ":</strong> " . 
                htmlspecialchars($msg['text']) . "</div>";
        }
    } else {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    echo json_encode(["messages" => $chatContent]);

} catch (PDOException $e) {
    error_log("❌ Load chat error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to load messages"]);
}
