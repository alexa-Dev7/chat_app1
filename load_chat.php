<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access!"]);
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

if ($currentChatUser === $username) {
    echo json_encode(["error" => "You can't chat with yourself!"]);
    exit();
}

try {
    $stmt = $pdo->prepare(
        "SELECT sender, recipient, text, timestamp 
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
        $messageClass = ($msg['sender'] === $username) ? 'outgoing' : 'incoming';
        $time = date('H:i', $msg['timestamp']);
        $chatContent .= "<div class='message {$messageClass}'>
                            <p>" . htmlspecialchars($msg['text']) . "</p>
                            <small>$time</small>
                        </div>";
    }

    if (empty($chatContent)) {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    echo json_encode(["messages" => $chatContent]);

} catch (PDOException $e) {
    error_log("❌ Error loading messages: " . $e->getMessage());
    echo json_encode(["error" => "Unable to load chat!"]);
}
