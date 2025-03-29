<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to view chat"]);
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// Database connection details
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Connect to PostgreSQL
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Check if chatKey (recipient username) is provided
if (!isset($_GET['chatKey'])) {
    echo json_encode(["status" => "error", "message" => "Chat not found"]);
    exit();
}

$chatKey = $_GET['chatKey']; // Recipient username

// Retrieve sender's user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$sender_id = $stmt->fetchColumn();

// Retrieve recipient's user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute(['username' => $chatKey]);
$recipient_id = $stmt->fetchColumn();

// Check if both users exist in the database
if ($sender_id && $recipient_id) {
    // Fetch messages between the two users
    $stmt = $pdo->prepare("
        SELECT m.text, m.timestamp, u.username as sender 
        FROM messages m
        JOIN users u ON u.id = m.sender
        WHERE (m.sender = :sender_id AND m.recipient = :recipient_id)
           OR (m.sender = :recipient_id AND m.recipient = :sender_id)
        ORDER BY m.timestamp ASC
    ");
    $stmt->execute(['sender_id' => $sender_id, 'recipient_id' => $recipient_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the chat messages
    echo json_encode(["status" => "success", "messages" => $messages]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid chat"]);
}
