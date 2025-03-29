<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$username = $_SESSION['username'];

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Ensure 'messages' table exists
try {
    $pdo->query("SELECT 1 FROM messages LIMIT 1");
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Messages table does not exist."]);
    exit();
}

// Check if message data is provided
if (!isset($_POST['to']) || empty($_POST['to']) || !isset($_POST['message']) || empty($_POST['message'])) {
    echo json_encode(["status" => "error", "message" => "Recipient and message cannot be empty."]);
    exit();
}

$receiver = $_POST['to'];
$message = trim($_POST['message']);

// Insert message into the database
try {
    $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver, message_text, timestamp) VALUES (:sender, :receiver, :message, NOW())");
    $stmt->execute([
        ':sender' => $username,
        ':receiver' => $receiver,
        ':message' => $message
    ]);

    echo json_encode(["status" => "success", "message" => "Message sent successfully."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error sending message: " . $e->getMessage()]);
}
