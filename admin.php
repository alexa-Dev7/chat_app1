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
    <style>
        /* Global Styles */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
        }
        .card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            background-color: #ffffff;
        }
        .card-header {
            background-color: #f5f7fa;
            border-bottom: 1px solid #e1e8ed;
            padding: 20px;
        }
        .card-footer {
            background-color: #f5f7fa;
            border-top: 1px solid #e1e8ed;
            padding: 15px;
            text-align: right;
        }
        .btn {
            padding: 12px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            opacity: 0.85;
        }
        .btn-red {
            background-color: #f56565;
            color: white;
        }
        .btn-red:hover {
            background-color: #e53e3e;
        }
        .btn-yellow {
            background-color: #ecc94b;
            color: white;
        }
        .btn-yellow:hover {
            background-color: #d69e2e;
        }
        .btn-blue {
            background-color: #3182ce;
            color: white;
        }
        .btn-blue:hover {
            background-color: #2b6cb0;
        }
        .table-header {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 600;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e1e8ed;
        }
        .table-row {
            background-color: #ffffff;
            transition: background-color 0.3s;
        }
        .table-row:hover {
            background-color: #f1f5f9;
        }
        .table-cell {
            padding: 12px;
            border-bottom: 1px solid #e1e8ed;
        }
        .table-cell:last-child {
            border-bottom: none;
        }
        .status {
            font-weight: bold;
            color: #2d3748;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex justify-center items-center">

    <div class="card max-w-6xl w-full p-8 mt-10">
        <div class="card-header">
            <div class="flex justify-between items-center">
                <h2 class="text-3xl font-semibold text-gray-800">Admin Panel</h2>
                <h3 class="text-lg text-gray-500">Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</h3>
            </div>
            <div class="mt-2 text-right">
                <a href="logout.php" class="text-red-600 hover:underline text-sm">Logout</a>
            </div>
        </div>

        <!-- Success Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-500 text-white p-4 rounded-md shadow-md text-center mt-4">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- User Table -->
        <div class="overflow-x-auto mt-6">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-header">
                        <th class="table-cell">ID</th>
                        <th class="table-cell">Username</th>
                        <th class="table-cell">Email</th>
                        <th class="table-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="table-row">
                            <td class="table-cell"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="table-cell">
                                <?php if ($user['username'] !== 'Trishit7'): ?>
                                    <a href="admin_actions.php?action=delete&id=<?= $user['id'] ?>" 
                                       class="btn btn-red mr-2">Delete</a>
                                    <a href="admin_actions.php?action=suspend&id=<?= $user['id'] ?>" 
                                       class="btn btn-yellow">Suspend</a>
                                <?php else: ?>
                                    <span class="status">Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <a href="admin_dashboard.php" class="btn btn-blue">Back to Dashboard</a>
        </div>
    </div>

</body>
</html>
