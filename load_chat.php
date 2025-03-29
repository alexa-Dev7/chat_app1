<?php
session_start();
header('Content-Type: application/json'); // ✅ Ensure JSON response

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

// ✅ PostgreSQL Database Credentials
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

// ✅ Check if 'messages' table exists (force lowercase)
$tableCheck = $pdo->prepare("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'messages')");
$tableCheck->execute();
$tableExists = $tableCheck->fetchColumn();

if (!$tableExists) {
    echo json_encode(["status" => "error", "message" => "messages table does not exist."]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT sender, recipient, text, timestamp FROM messages WHERE sender = :username OR recipient = :username ORDER BY timestamp DESC");
    $stmt->execute(['username' => $_SESSION['username']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Failed to fetch messages: " . $e->getMessage()]);
}
?>
