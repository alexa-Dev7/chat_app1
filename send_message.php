<?php
session_start();
require 'db_connect.php';

// Debugging: Check session
if (!isset($_SESSION['username'], $_POST['to'], $_POST['message'])) {
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

// Ensure recipient exists in the database
try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :to");
    $stmt->execute([':to' => $to]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "Recipient not found"]);
        exit();
    }
} catch (PDOException $e) {
    error_log("❌ SQL Error while checking recipient: " . $e->getMessage());
    echo json_encode(["error" => "Failed to check recipient"]);
    exit();
}

// Insert the message into the database
try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender, recipient, text)
        VALUES (:from, :to, :text)
    ");
    $stmt->execute([
        ':from' => $username,
        ':to' => $to,
        ':text' => htmlspecialchars($message)
    ]);

    // Debugging: Log successful message sending
    error_log("Message sent successfully from $username to $to");

    echo json_encode(["success" => "Message sent!"]);

} catch (PDOException $e) {
    error_log("❌ Send message error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to send message"]);
}
