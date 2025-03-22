<?php
session_start();
require 'db_connect.php'; 

// Ensure user is logged in and inputs are valid
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

// Block empty messages or sending to yourself
if ($to === $username || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// Ensure recipient exists in PostgreSQL
$recipientQuery = $pdo->prepare("SELECT username FROM users WHERE username = :to");
$recipientQuery->execute([':to' => $to]);

if ($recipientQuery->rowCount() === 0) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

// Save message to PostgreSQL
try {
    $stmt = $pdo->prepare(
        "INSERT INTO messages (sender, recipient, text) 
        VALUES (:sender, :recipient, :text)"
    );

    $stmt->execute([
        ':sender' => $username,
        ':recipient' => $to,
        ':text' => $message
    ]);

    echo json_encode(["success" => "Message sent!"]);

} catch (PDOException $e) {
    error_log("❌ Failed to send message: " . $e->getMessage());
    echo json_encode(["error" => "Failed to send message"]);
}
?>
