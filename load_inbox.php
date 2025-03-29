<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

require 'db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $_SESSION['username']]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit();
    }

    // Fetch the inbox (all chats for the logged-in user)
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            CASE WHEN sender = :userId THEN recipient ELSE sender END AS chatUser
        FROM messages 
        WHERE sender = :userId OR recipient = :userId
    ");
    $stmt->execute(['userId' => $userId]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the last message for each chat
    $inbox = [];
    foreach ($chats as $chat) {
        $chatUser = $chat['chatUser'];
        $stmt = $pdo->prepare("
            SELECT m.text as lastMessage, m.timestamp, u.username as receiver
            FROM messages m
            JOIN users u ON u.id = :chatUser
            WHERE (m.sender = :userId AND m.recipient = :chatUser)
                OR (m.sender = :chatUser AND m.recipient = :userId)
            ORDER BY m.timestamp DESC
            LIMIT 1
        ");
        $stmt->execute(['chatUser' => $chatUser, 'userId' => $userId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($message) {
            $inbox[] = [
                'chatKey' => $chatUser,
                'lastMessage' => $message['lastMessage'],
                'timestamp' => $message['timestamp'],
                'receiver' => $message['receiver']
            ];
        }
    }

    echo json_encode(['status' => 'success', 'inbox' => $inbox]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error loading inbox: " . $e->getMessage()]);
}
?>
