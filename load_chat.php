<?php
session_start();
header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Check if 'messages' table exists
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('messages', $tables)) {
        echo json_encode(["status" => "error", "message" => "Messages table does not exist."]);
        exit();
    }

    $chatKey = $_GET['chatKey'] ?? '';
    if (empty($chatKey)) {
        echo json_encode(["status" => "error", "message" => "No chatKey provided."]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT sender, text, timestamp FROM messages WHERE receiver = :chatKey OR sender = :chatKey ORDER BY timestamp ASC");
    $stmt->execute(['chatKey' => $chatKey]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    exit();
}
?>
