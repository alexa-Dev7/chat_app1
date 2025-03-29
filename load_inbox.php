<?php
session_start();
require 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in"]);
    exit();
}

$username = $_SESSION['username'];  // Logged-in user

// Fetch user ID
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit();
    }

    $userId = $userData['id'];

    // Query to get the latest messages between the user and others
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN sender = :userId THEN recipient 
                ELSE sender 
            END AS chatUser,
            messages.text AS lastMessage,
            messages.timestamp 
        FROM messages 
        WHERE sender = :userId OR recipient = :userId
        ORDER BY messages.timestamp DESC
    ");
    $stmt->execute(['userId' => $userId]);

    $inbox = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'status' => 'success',
        'inbox' => []
    ];

    foreach ($inbox as $chat) {
        // Get the username of the chat partner
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $chat['chatUser']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $response['inbox'][] = [
            'chatKey' => $user['username'],
            'lastMessage' => $chat['lastMessage'],
            'timestamp' => $chat['timestamp']
        ];
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Failed to load inbox: " . $e->getMessage()]);
}
