<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to send a message"]);
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// Database connection details
$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Connect to PostgreSQL
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to']; // The recipient's username
    $message = $_POST['message']; // The message content

    // Retrieve sender's user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $sender_id = $stmt->fetchColumn();

    // Retrieve recipient's user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $to]);
    $recipient_id = $stmt->fetchColumn();

    // Check if both users exist in the database
    if ($sender_id && $recipient_id) {
        // Insert the message into the messages table
        $stmt = $pdo->prepare("INSERT INTO messages (sender, recipient, text) VALUES (:sender, :recipient, :text)");
        $stmt->execute(['sender' => $sender_id, 'recipient' => $recipient_id, 'text' => $message]);

        // Return a success response
        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
    } else {
        // If any user doesn't exist, return an error
        echo json_encode(["status" => "error", "message" => "Invalid user"]);
    }
}
