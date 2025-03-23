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

// Fetch recipient's user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :to");
$stmt->execute([':to' => $to]);
$recipient = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$recipient) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}
$recipientId = $recipient['id'];

// Insert the message into DB
try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender, recipient, text)
        VALUES (:from, :to, :text)
    ");
    $stmt->execute([
        ':from' => $username, // the sender is the logged-in user
        ':to' => $recipientId, // recipient's ID
        ':text' => htmlspecialchars($message)
    ]);

    echo json_encode(["success" => "Message sent!"]);

} catch (PDOException $e) {
    error_log("❌ Send message error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to send message"]);
}
