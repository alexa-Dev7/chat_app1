<?php
session_start();

// Secure Database Connection Setup for Render PostgreSQL
$host = getenv('DB_HOST') ?: 'dpg-cvf3tfjqf0us73flfkv0-a';
$dbname = getenv('DB_NAME') ?: 'chat_app_ltof';
$user = getenv('DB_USER') ?: 'chat_app_ltof_user';
$password = getenv('DB_PASSWORD') ?: 'JtFCFOztPWwHSS6wV4gXbTSzlV6barfq';
$port = getenv('DB_PORT') ?: 5432;

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Handle registration
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Basic form validation
    if (empty($username) || empty($password) || empty($email)) {
        $error = "❌ All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Invalid email format!";
    } else {
        try {
            // Check if the username already exists
            $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $error = "❌ Username already taken!";
            } else {
                // Hash the password securely
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert user data into the database
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, created_at) 
                                       VALUES (:username, :password, :email, NOW())");
                $stmt->execute([
                    'username' => $username,
                    'password' => $hashedPassword,
                    'email' => $email
                ]);

                // Start session for the new user
                $_SESSION['username'] = $username;

                // Redirect to inbox after successful signup
                header('Location: inbox.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "❌ Registration failed: " . $e->getMessage();
        }
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
