<?php
session_start();

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    die(json_encode(["status" => "error", "message" => "User not logged in."]));
}

$sender = $_SESSION['username'];
$receiver = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($receiver) || empty($message)) {
    die(json_encode(["status" => "error", "message" => "Receiver and message cannot be empty."]));
}

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if 'receiver' exists in users table
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :receiver");
    $stmt->execute(['receiver' => $receiver]);
    if (!$stmt->fetch()) {
        die(json_encode(["status" => "error", "message" => "Receiver does not exist in users table."]));
    }

    // Insert message
    $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver, text, timestamp) VALUES (:sender, :receiver, :text, NOW())");
    $stmt->execute([
        'sender' => $sender,
        'receiver' => $receiver,
        'text' => $message
    ]);

    echo json_encode(["status" => "success", "message" => "Message sent."]);

} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
}
?> and load_chat.php : <?php
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

    // ✅ Verify existing column names in 'messages' table
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'messages'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sender_col = in_array('sender', $columns) ? 'sender' : (in_array('from_user', $columns) ? 'from_user' : '');
    $receiver_col = in_array('receiver', $columns) ? 'receiver' : (in_array('to_user', $columns) ? 'to_user' : '');
    $text_col = in_array('text', $columns) ? 'text' : (in_array('message', $columns) ? 'message' : '');
    $timestamp_col = in_array('timestamp', $columns) ? 'timestamp' : (in_array('sent_at', $columns) ? 'sent_at' : '');

    if (!$sender_col || !$receiver_col || !$text_col || !$timestamp_col) {
        echo json_encode(["status" => "error", "message" => "Missing columns in messages table: " . implode(", ", array_diff(['sender', 'receiver', 'text', 'timestamp'], $columns))]);
        exit();
    }

    $chatKey = $_GET['chatKey'] ?? '';
    if (empty($chatKey)) {
        echo json_encode(["status" => "error", "message" => "No chatKey provided."]);
        exit();
    }

    // ✅ Use correct column names dynamically
    $query = "SELECT $sender_col AS sender, $text_col AS text, $timestamp_col AS timestamp 
              FROM messages 
              WHERE $receiver_col = :chatKey OR $sender_col = :chatKey 
              ORDER BY $timestamp_col ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['chatKey' => $chatKey]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    exit();
}
?>
