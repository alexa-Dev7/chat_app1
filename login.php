<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection setup
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Handle admin login
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_username = trim($_POST['username']);
    $admin_password = trim($_POST['password']);

    try {
        // Admin credentials (Manually set, no signup required)
        $stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = :username AND username = 'Trishit7'");
        $stmt->execute(['username' => $admin_username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($admin_password, $admin['password'])) {
            $_SESSION['admin_username'] = $admin_username;
            session_regenerate_id(true); // Secure session
            header('Location: admin.php'); // Redirect to admin panel
            exit();
        } else {
            $error = "❌ Invalid admin credentials!";
        }
    } catch (PDOException $e) {
        $error = "❌ Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Admin Login - Sender</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>

    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
</head>

<body class="bg-gray-100">
<div class="flex justify-center items-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-center mb-4">Admin Login</h2>

            <!-- Error Display -->
            <?php if (!empty($error)): ?>
                <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="post">
                <div class="mb-4">
                    <input class="w-full p-2 border border-gray-300 rounded" type="text" name="username"
                           placeholder="Admin Username" required/>
                </div>
                <div class="mb-4">
                    <input class="w-full p-2 border border-gray-300 rounded" type="password" name="password"
                           placeholder="Admin Password" required/>
                </div>
                <button class="w-full bg-blue-600 text-white p-2 rounded font-bold hover:bg-blue-700 transition">
                    Log In
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
