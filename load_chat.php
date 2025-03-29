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

// Check if chatKey is provided
if (!isset($_GET['chatKey']) || empty($_GET['chatKey'])) {
    echo json_encode(["status" => "error", "message" => "Chat key is missing."]);
    exit();
}

$chatKey = $_GET['chatKey'];

// Fetch chat messages
try {
    $stmt = $pdo->prepare("
        SELECT sender, receiver, message_text AS text, timestamp 
        FROM messages 
        WHERE (sender = :username AND receiver = :chatKey) 
           OR (sender = :chatKey AND receiver = :username) 
        ORDER BY timestamp ASC
    ");
    $stmt->execute([
        ':username' => $username,
        ':chatKey' => $chatKey
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error fetching chat messages: " . $e->getMessage()]);
}
