<?php
session_start();

// Only admins can perform actions
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    die("Unauthorized access.");
}

// Database connection
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

// Get action and user ID
if (isset($_GET['action'], $_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action === "delete") {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo "User deleted successfully.";
    } elseif ($action === "suspend") {
        $stmt = $pdo->prepare("UPDATE users SET role = 'suspended' WHERE id = ?");
        $stmt->execute([$id]);
        echo "User suspended successfully.";
    }
} else {
    echo "Invalid request.";
}
?>
