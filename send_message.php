<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in to send a message"]);
    exit();
}

$username = $_SESSION['username']; // Logged-in user

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Connect to PostgreSQL
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to']; // Username of the recipient
    $message = $_POST['message']; // The message text

    // Get the IDs for sender and recipient
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $sender_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $to]);
    $recipient_id = $stmt->fetchColumn();

    if ($sender_id && $recipient_id) {
        // Insert message into database
        $stmt = $pdo->prepare("INSERT INTO messages (sender, recipient, text) VALUES (:sender, :recipient, :text)");
        $stmt->execute(['sender' => $sender_id, 'recipient' => $recipient_id, 'text' => $message]);

        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid user"]);
    }
}
?>
