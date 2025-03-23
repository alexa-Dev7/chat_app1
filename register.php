<?php
session_start();

// Database connection setup
$host = "dpg-cvfu9ennoe9s73bkltpg-a";
$dbname = "pager_1n3k";
$user = "pager_1n3k_user";
$password = "XyB7njpb4E01Nl26iWWLJ30xMCDrlHux";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Handle registration
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    try {
        // Ensure users table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Check if username exists
        $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error = "⚠️ Username already exists!";
        } else {
            // Hash password and insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, created_at) 
                                   VALUES (:username, :password, :email, NOW())");
            $stmt->execute([
                'username' => $username,
                'password' => $hashedPassword,
                'email' => $email
            ]);

            // Start session and redirect
            $_SESSION['username'] = $username;
            header('Location: inbox.php');
            exit();
        }
    } catch (PDOException $e) {
        $error = "❌ Registration failed: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Sender Sign Up</title>

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
            <h2 class="text-2xl font-bold text-center mb-2">Create a new account</h2>
            <p class="text-center text-gray-600 mb-4">It's quick and easy.</p>

            <!-- Error Display -->
            <?php if (!empty($error)): ?>
                <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- Registration Form -->
            <form action="register.php" method="post">
                <div class="flex space-x-2 mb-4">
                    <input class="w-1/2 p-2 border border-gray-300 rounded" type="text" name="username"
                           placeholder="Username" required/>
                    <input class="w-1/2 p-2 border border-gray-300 rounded" type="password" name="password"
                           placeholder="Password" required/>
                </div>

                <div class="mb-4">
                    <input class="w-full p-2 border border-gray-300 rounded" type="email" name="email"
                           placeholder="Email address" required/>
                </div>

                <p class="text-xs text-gray-600 mb-4">
                    This is a private messaging program. Developed by Trishit
                    <a class="text-blue-600" href="#">Learn more.</a>
                </p>

                <p class="text-xs text-gray-600 mb-4">
                    By clicking Sign Up, you agree to our
                    <a class="text-blue-600" href="#">Terms</a>,
                    <a class="text-blue-600" href="#">Privacy Policy</a>, and
                    <a class="text-blue-600" href="#">Cookies Policy</a>.
                    Lightweight messaging software. More effective than any other messaging software.
                </p>

                <button class="w-full bg-green-600 text-white p-2 rounded font-bold hover:bg-green-700 transition">
                    Sign Up
                </button>
            </form>

            <div class="text-center mt-4">
                <a class="text-blue-600" href="index.php">Already have an account? Log In</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
