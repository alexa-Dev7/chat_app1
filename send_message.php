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

?>
