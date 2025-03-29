<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$username = $_SESSION['username'];

// PostgreSQL Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit();
}

// 🛠️ Check if 'messages' table exists before querying
$tableCheck = $pdo->query("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'messages')");
$tableExists = $tableCheck->fetchColumn();

if (!$tableExists) {
    echo json_encode(["status" => "error", "message" => "Messages table does not exist."]);
    exit();
}

// 🛠️ Validate and sanitize input
if (!isset($_GET['chatKey']) || empty($_GET['chatKey'])) {
    echo json_encode(["status" => "error", "message" => "Chat key is missing."]);
    exit();
}

$chatKey = $_GET['chatKey'];

try {
    $stmt = $pdo->prepare("SELECT text, timestamp, sender FROM messages WHERE 
        (sender = :username AND recipient = :chatKey) OR 
        (sender = :chatKey AND recipient = :username) 
        ORDER BY timestamp ASC");

    $stmt->execute(['username' => $username, 'chatKey' => $chatKey]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database query failed: " . $e->getMessage()]);
}
?>
