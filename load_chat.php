<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

if (!isset($_GET['chatKey'])) {
    echo json_encode(["status" => "error", "message" => "No chat user specified"]);
    exit();
}

$loggedInUser = $_SESSION['username'];
$chatUser = $_GET['chatKey'];

// Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // ✅ Ensure the messages table exists
    $tableCheck = $pdo->query("SELECT 1 FROM public.messages LIMIT 1");
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Messages table does not exist or database connection failed."]);
    exit();
}

try {
    // ✅ Fetch messages between the logged-in user and the selected chat user
    $stmt = $pdo->prepare("
        SELECT sender, receiver, message, created_at 
        FROM public.messages 
        WHERE (sender = :loggedInUser AND receiver = :chatUser) 
           OR (sender = :chatUser AND receiver = :loggedInUser) 
        ORDER BY created_at ASC
    ");
    $stmt->execute([
        'loggedInUser' => $loggedInUser,
        'chatUser' => $chatUser
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "messages" => $messages]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error fetching chat messages."]);
}
?>
