<?php
session_start();

// Redirect non-admins to login page
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
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, email FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #f4f4f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6 bg-gray-100">

<div class="w-full max-w-4xl bg-white shadow-lg rounded-3xl p-6">
    <!-- Header -->
    <div class="flex justify-between items-center border-b pb-4">
        <h2 class="text-2xl font-bold text-gray-800">Admin Panel</h2>
        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
            Logout
        </a>
    </div>

    <h3 class="text-lg font-semibold text-gray-700 mt-4">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?> 👑</h3>

    <!-- User List -->
    <div class="mt-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b text-gray-700">
                    <th class="py-2">ID</th>
                    <th class="py-2">Username</th>
                    <th class="py-2">Email</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="border-b bg-gray-50 hover:bg-gray-100 transition">
                    <td class="py-3 px-2"><?= htmlspecialchars($user['id']) ?></td>
                    <td class="py-3 px-2 font-medium"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="py-3 px-2"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="py-3 px-2">
                        <?php if ($user['username'] !== 'Trishit7'): ?>
                            <a href="admin_actions.php?action=delete&id=<?= $user['id'] ?>" 
                               class="text-red-500 hover:underline mr-2">Delete</a>
                            <a href="admin_actions.php?action=suspend&id=<?= $user['id'] ?>" 
                               class="text-yellow-500 hover:underline">Suspend</a>
                        <?php else: ?>
                            <span class="text-green-600 font-semibold">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
