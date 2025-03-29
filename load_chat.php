<?php
session_start();
require 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in"]);
    exit();
}

$username = $_SESSION['username'];  // Logged-in user
$chatKey = $_GET['chatKey'];        // The chatKey is the username of the person we are chatting with

// Fetch sender and recipient IDs from the database
try {
    // Get sender's ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $senderData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$senderData) {
        echo json_encode(["status" => "error", "message" => "Sender not found"]);
        exit();
    }

    // Get recipient's ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $chatKey]);
    $recipientData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipientData) {
        echo json_encode(["status" => "error", "message" => "Recipient not found"]);
        exit();
    }

    // Query for all messages between sender and recipient
    $stmt = $pdo->prepare("SELECT messages.text, messages.timestamp, users.username AS sender 
                           FROM messages 
                           JOIN users ON messages.sender = users.id 
                           WHERE (messages.sender = :senderId AND messages.recipient = :recipientId) 
                              OR (messages.sender = :recipientId AND messages.recipient = :senderId) 
                           ORDER BY messages.timestamp ASC");
    $stmt->execute([
        'senderId' => $senderData['id'],
        'recipientId' => $recipientData['id']
    ]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'status' => 'success',
        'messages' => []
    ];

    foreach ($messages as $message) {
        $response['messages'][] = [
            'sender' => $message['sender'],
            'text' => $message['text'],
            'time' => $message['timestamp']
        ];
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Failed to load messages: " . $e->getMessage()]);
}
