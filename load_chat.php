<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

require 'db_connect.php';

$chatKey = $_GET['chatKey'] ?? '';
if (empty($chatKey)) {
    echo json_encode(["status" => "error", "message" => "Chat key is required."]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $_SESSION['username']]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit();
    }

    // Fetch messages between user and the chatKey user
    $stmt = $pdo->prepare("
        SELECT m.text, m.timestamp, u.username as sender
        FROM messages m
        JOIN users u ON m.sender = u.id
        WHERE (m.sender = :userId AND m.recipient = :chatKeyId)
           OR (m.sender = :chatKeyId AND m.recipient = :userId)
        ORDER BY m.timestamp ASC
    ");
    $stmt->execute([
        'userId' => $userId,
        'chatKeyId' => $chatKey
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($messages) {
        echo json_encode(["status" => "success", "messages" => $messages]);
    } else {
        echo json_encode(["status" => "error", "message" => "No messages found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error fetching messages: " . $e->getMessage()]);
}
?>
