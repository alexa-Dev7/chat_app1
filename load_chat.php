<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to view the chat"]);
    exit();
}

$username = $_SESSION['username']; // Logged-in user
$chatKey = $_GET['chatKey']; // The username of the person we're chatting with

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Connect to PostgreSQL
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get user IDs
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$sender_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
$stmt->execute(['username' => $chatKey]);
$recipient_id = $stmt->fetchColumn();

if ($sender_id && $recipient_id) {
    // Fetch chat messages
    $stmt = $pdo->prepare("
        SELECT m.text, m.timestamp, u.username AS sender
        FROM messages m
        JOIN users u ON m.sender = u.id
        WHERE (m.sender = :sender_id AND m.recipient = :recipient_id)
        OR (m.sender = :recipient_id AND m.recipient = :sender_id)
        ORDER BY m.timestamp
    ");
    $stmt->execute(['sender_id' => $sender_id, 'recipient_id' => $recipient_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'messages' => $messages
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid chat"]);
}
?>
