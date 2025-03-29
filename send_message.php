<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

// Ensure required fields are set
if (!isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit();
}

$loggedInUser = $_SESSION['username'];
$receiver = $_POST['to'];
$message = trim($_POST['message']);

if ($message === "") {
    echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
    exit();
}

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
    // ✅ Insert the new message
    $stmt = $pdo->prepare("
        INSERT INTO public.messages (sender, receiver, message, created_at) 
        VALUES (:loggedInUser, :receiver, :message, NOW())
    ");
    $stmt->execute([
        'loggedInUser' => $loggedInUser,
        'receiver' => $receiver,
        'message' => $message
    ]);

    echo json_encode(["status" => "success", "message" => "Message sent"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Error sending message"]);
}
?>
