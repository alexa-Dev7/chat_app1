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

    // Ensure messages table exists
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('messages', $tables)) {
        echo json_encode(["status" => "error", "message" => "Messages table does not exist."]);
        exit();
    }

    // Verify column names
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'messages'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sender_col = in_array('sender', $columns) ? 'sender' : (in_array('from_user', $columns) ? 'from_user' : '');
    $receiver_col = in_array('receiver', $columns) ? 'receiver' : (in_array('to_user', $columns) ? 'to_user' : '');
    $text_col = in_array('text', $columns) ? 'text' : (in_array('message', $columns) ? 'message' : '');
    $timestamp_col = in_array('timestamp', $columns) ? 'timestamp' : (in_array('sent_at', $columns) ? 'sent_at' : '');

    if (!$sender_col || !$receiver_col || !$text_col || !$timestamp_col) {
        echo json_encode(["status" => "error", "message" => "Missing columns in messages table"]);
        exit();
    }

    $current_user = $_SESSION['username']; // Get logged-in user
    $chat_partner = $_GET['chat_partner'] ?? '';

    if (empty($chat_partner)) {
        echo json_encode(["status" => "error", "message" => "No chat partner provided."]);
        exit();
    }

    // ✅ Only fetch messages where the logged-in user is sender OR receiver
    $query = "SELECT $sender_col AS sender, $receiver_col AS receiver, $text_col AS text, $timestamp_col AS timestamp 
              FROM messages 
              WHERE 
                  ($sender_col = :current_user AND $receiver_col = :chat_partner) 
                  OR 
                  ($sender_col = :chat_partner AND $receiver_col = :current_user)
              ORDER BY $timestamp_col ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'current_user' => $current_user,
        'chat_partner' => $chat_partner
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    exit();
}
?>
