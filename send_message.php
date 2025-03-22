<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent sending empty messages or messages to yourself
if ($to === $username || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Ensure recipient exists
$stmt = $pdo->prepare("SELECT username FROM users WHERE username = :to");
$stmt->execute([':to' => $to]);

if ($stmt->rowCount() === 0) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

try {
    // Save the message
    $stmt = $pdo->prepare(
        "INSERT INTO messages (sender, recipient, text, aes_key, iv) 
        VALUES (:from, :to, :text, '', '')"
    );

    $stmt->execute([
        ':from' => $username,
        ':to' => $to,
        ':text' => htmlspecialchars($message)
    ]);

    echo json_encode(["success" => "Message sent!"]);

} catch (PDOException $e) {
    error_log("❌ Send message error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to send message"]);
}
