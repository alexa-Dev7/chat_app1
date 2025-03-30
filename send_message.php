<?php
session_start();
header('Content-Type: application/json');

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
$message = trim($_POST['message'] ?? '');

if (empty($receiver) || empty($message)) {
    die(json_encode(["status" => "error", "message" => "Receiver and message cannot be empty."]));
}

if ($sender === $receiver) {
    die(json_encode(["status" => "error", "message" => "You cannot message yourself."]));
}

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // ✅ Check if receiver exists
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :receiver");
    $stmt->execute(['receiver' => $receiver]);
    if (!$stmt->fetch()) {
        die(json_encode(["status" => "error", "message" => "Receiver does not exist."]));
    }

    // ✅ Insert message into database
    $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver, text, timestamp) VALUES (:sender, :receiver, :text, NOW())");
    $stmt->execute([
        'sender' => $sender,
        'receiver' => $receiver,
        'text' => $message
    ]);

    echo json_encode(["status" => "success", "message" => "Message sent successfully."]);

} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
}
?>
