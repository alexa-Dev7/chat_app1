<?php
session_start();

if (!isset($_SESSION['admin'])) {
    die("Unauthorized access");
}

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_POST['user_id'];

    if (isset($_POST['suspend'])) {
        // Suspend user (update status)
        $stmt = $pdo->prepare("UPDATE users SET status = 'Suspended' WHERE id = ?");
        $stmt->execute([$userId]);
        header("Location: admin.php");
        exit();
    }

    if (isset($_POST['delete'])) {
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        header("Location: admin.php");
        exit();
    }
}

die("Invalid request");
?>
