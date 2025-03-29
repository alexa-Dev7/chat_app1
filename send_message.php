<?php
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

require 'db_connect.php';

$sender = $_SESSION['username'];
$recipient = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($recipient) || empty($message)) {
    echo json_encode(["status" => "error", "message" => "Recipient or message is empty."]);
    exit();
}

try {
    // Fetch sender and recipient user IDs
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $sender]);
    $sender_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $recipient]);
    $recipient_id = $stmt->fetchColumn();

    if (!$sender_id || !$recipient_id) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit();
    }

    // Insert the message into the database
    $stmt = $pdo->prepare("INSERT INTO messages (sender, recipient, text) VALUES (:sender, :recipient, :text)");
    $stmt->execute([
        'sender' => $sender_id,
        'recipient' => $recipient_id,
        'text' => $message,
    ]);

    echo json_encode(["status" => "success", "message" => "Message sent successfully."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error sending message: " . $e->getMessage()]);
}
?>
