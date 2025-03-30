<?php
session_start();

// Redirect non-admins
if (!isset($_SESSION['admin_username']) || $_SESSION['admin_username'] !== 'Trishit7') {
    header("Location: login.php");
    exit();
}

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Ensure no output before header()
ob_start();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $_SESSION['message'] = "✅ User deleted successfully.";
    } elseif ($action === 'suspend') {
        $stmt = $pdo->prepare("UPDATE users SET suspended = TRUE WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $_SESSION['message'] = "⚠️ User suspended successfully.";
    }
}

// Redirect back to admin panel
header("Location: admin.php");
exit();

ob_end_flush();
?>
