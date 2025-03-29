<?php
session_start();
require 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in"]);
    exit();
}

$sender = $_SESSION['username'];  // Logged-in user
$recipient = $_POST['to'];        // The recipient username
$message = $_POST['message'];     // The message content

// Ensure the recipient exists
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $recipient]);
    $recipientData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$recipientData) {
        echo json_encode(["status" => "error", "message" => "Recipient not found"]);
        exit();
    }

    // Get the sender's ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $sender]);
    $senderData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$senderData) {
        echo json_encode(["status" => "error", "message" => "Sender not found"]);
        exit();
    }

    // Insert the message into the database
    $stmt = $pdo->prepare("INSERT INTO messages (sender, recipient, text) VALUES (:sender, :recipient, :text)");
    $stmt->execute([
        'sender' => $senderData['id'],
        'recipient' => $recipientData['id'],
        'text' => $message
    ]);

    echo json_encode(["status" => "success", "message" => "Message sent"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Failed to send message: " . $e->getMessage()]);
}
