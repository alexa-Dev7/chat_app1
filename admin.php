<?php
session_start();

// Redirect non-admins to login page
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
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

// Fetch all users
$stmt = $pdo->query("SELECT id, username, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <h2>Admin Panel</h2>
    <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>! <a href="logout.php">Logout</a></h3>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <?php if ($user['role'] !== 'admin'): ?>
                        <a href="admin_actions.php?action=delete&id=<?= $user['id'] ?>">Delete</a> |
                        <a href="admin_actions.php?action=suspend&id=<?= $user['id'] ?>">Suspend</a>
                    <?php else: ?>
                        Admin
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
