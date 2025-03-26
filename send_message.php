<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$username = $_SESSION['username'];

if (isset($_POST['to'], $_POST['message'])) {
    $to = trim($_POST['to']);
    $message = trim($_POST['message']);

    // Sanitize input
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Validate input
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
        exit();
    }

    // Save message to database (example query)
    require 'db_connect.php';

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver, message, timestamp) VALUES (:sender, :receiver, :message, NOW())");
        $stmt->execute([
            'sender' => $username,
            'receiver' => $to,
            'message' => $message
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
