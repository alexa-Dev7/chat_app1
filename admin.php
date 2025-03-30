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
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">

    <div class="w-full max-w-4xl bg-white rounded-lg shadow-md mt-10 p-6">
        <h2 class="text-2xl font-bold text-gray-700 text-center">Admin Panel</h2>
        <h3 class="text-lg text-gray-500 text-center">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>! 
            <a href="logout.php" class="text-red-500 hover:underline">Logout</a>
        </h3>

        <!-- Success Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-md text-center mt-4">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <table class="w-full mt-6 border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-300">
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Username</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="text-center bg-gray-100">
                        <td class="border p-2"><?= htmlspecialchars($user['id']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="border p-2"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="border p-2">
                            <?php if ($user['username'] !== 'Trishit7'): ?>
                                <a href="admin_actions.php?action=delete&id=<?= $user['id'] ?>" 
                                   class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-700 transition">Delete</a>
                                <a href="admin_actions.php?action=suspend&id=<?= $user['id'] ?>" 
                                   class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-700 transition">Suspend</a>
                            <?php else: ?>
                                <span class="text-gray-500">Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
