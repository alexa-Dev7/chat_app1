<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$username = $_SESSION['username'];

if (isset($_GET['user'])) {
    $user = trim($_GET['user']);

    // Fetch messages from database
    require 'db_connect.php';

    try {
        $stmt = $pdo->prepare("SELECT sender, message, timestamp FROM messages WHERE (sender = :username AND receiver = :user) OR (sender = :user AND receiver = :username) ORDER BY timestamp DESC");
        $stmt->execute(['username' => $username, 'user' => $user]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format messages for JSON response
        $formattedMessages = [];
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'sender' => $msg['sender'],
                'text' => $msg['message'],
                'time' => $msg['timestamp']
            ];
        }

        echo json_encode(['messages' => $formattedMessages]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'User not specified']);
}
?>
