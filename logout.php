<?php
session_start();

// Database connection setup
$host = "dpg-cvf3tfjqf0us73flfkv0-a";
$dbname = "chat_app_ltof";
$user = "chat_app_ltof_user";
$password = "JtFCFOztPWwHSS6wV4gXbTSzlV6barfq";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Remove the user's session from the database
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    try {
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE username = :username");
        $stmt->execute(['username' => $username]);
    } catch (PDOException $e) {
        die("❌ Failed to clear session from database: " . $e->getMessage());
    }
}

// Destroy session and redirect to login
session_unset();
session_destroy();

header("Location: index.php");
exit();
