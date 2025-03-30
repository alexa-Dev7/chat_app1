<?php
session_start();

// Ensure only "Trishit7" can perform admin actions
if (!isset($_SESSION['admin_username']) || $_SESSION['admin_username'] !== 'Trishit7') {
    die("❌ Unauthorized access.");
}

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Ensure valid action and user ID
if (!isset($_GET['action'], $_GET['id']) || !in_array($_GET['action'], ['delete', 'suspend'])) {
    die("❌ Invalid request.");
}

$action = $_GET['action'];
$user_id = intval($_GET['id']);

// Prevent deleting the admin account
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ User not found.");
}

if ($user['username'] === 'Trishit7') {
    die("❌ Cannot delete or suspend the admin.");
}

// Perform action
if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    echo "✅ User deleted successfully.";
} elseif ($action === 'suspend') {
    $stmt = $pdo->prepare("UPDATE users SET suspended = TRUE WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    echo "✅ User suspended successfully.";
}

// Redirect back to admin panel
header("Location: admin.php");
exit();
?>
