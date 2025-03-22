<?php
session_start();

// Database connection setup for Render PostgreSQL
$host = getenv('DB_HOST') ?: 'dpg-cvf3tfjqf0us73flfkv0-a';
$dbname = getenv('DB_NAME') ?: 'chat_app_ltof';
$user = getenv('DB_USER') ?: 'chat_app_ltof_user';
$password = getenv('DB_PASSWORD') ?: 'JtFCFOztPWwHSS6wV4gXbTSzlV6barfq';
$port = getenv('DB_PORT') ?: 5432;

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Handle login
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session and redirect to inbox
            $_SESSION['username'] = $username;
            header('Location: inbox.php');
            exit();
        } else {
            $error = "❌ Invalid username or password!";
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
    <title>Login - Sender</title>

    <!-- Tailwind CSS, FontAwesome & Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100">
<div class="flex justify-center items-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-center mb-2">
                Log in to Sender
            </h2>

            <!-- Error Display -->
            <?php if (!empty($error)): ?>
                <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="index.php" method="post">
                <div class="mb-4">
                    <input class="w-full p-2 border border-gray-300 rounded" type="text" name="username"
                           placeholder="Username" required/>
                </div>
                <div class="mb-4">
                    <input class="w-full p-2 border border-gray-300 rounded" type="password" name="password"
                           placeholder="Password" required/>
                </div>
                <button class="w-full bg-blue-600 text-white p-2 rounded font-bold hover:bg-blue-700 transition">
                    Log In
                </button>
            </form>

            <!-- Links -->
            <div class="text-center mt-4">
                <a class="text-blue-600 hover:underline" href="#">Forgotten password?</a>
            </div>

            <div class="text-center mt-4">
                <hr class="my-4"/>
                <a href="register.php"
                   class="w-full bg-green-600 text-white p-2 rounded font-bold block text-center hover:bg-green-700 transition">
                    Create New Account
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
