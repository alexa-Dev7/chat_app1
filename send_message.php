<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in and inputs are valid
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

// Ensure recipient exists
$stmt = $pdo->prepare("SELECT username FROM users WHERE username = :to");
$stmt->execute([':to' => $to]);
if ($stmt->rowCount() === 0) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

// Insert the message into DB
try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender, recipient, text, timestamp)
        VALUES (:from, :to, :text, NOW())  -- Adding timestamp
    ");
    $stmt->execute([
        ':from' => $username,
        ':to' => $to,
        ':text' => htmlspecialchars($message)  // Correctly escape special characters
    ]);

    // Update the last chat user in session
    $_SESSION['last_chat_user'] = $to; 

    echo json_encode(["success" => "Message sent!"]);

} catch (PDOException $e) {
    error_log("❌ Send message error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to send message"]);
}
