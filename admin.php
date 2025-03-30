<?php
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin'])) {
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
try {
    $stmt = $pdo->query("SELECT id, username, email, status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-4">User Management</h1>
        <table class="min-w-full bg-white shadow-md rounded">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="py-2 px-4">ID</th>
                    <th class="py-2 px-4">Username</th>
                    <th class="py-2 px-4">Email</th>
                    <th class="py-2 px-4">Status</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="border-b">
                    <td class="py-2 px-4"><?= htmlspecialchars($user['id']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['status']) ?></td>
                    <td class="py-2 px-4">
                        <form action="manage_user.php" method="POST" class="inline">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button name="suspend" class="bg-yellow-500 text-white px-3 py-1 rounded">Suspend</button>
                            <button name="delete" class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
