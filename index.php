<?php
session_start();

// Database connection setup for Render PostgreSQL
$host = "dpg-cvf3tfjqf0us73flfkv0-a";
$dbname = "chat_app_ltof";
$user = "chat_app_ltof_user";
$password = "JtFCFOztPWwHSS6wV4gXbTSzlV6barfq";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from the database
    $stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        header('Location: inbox.php');
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - Sender</title>
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

        <?php if (!empty($error)): ?>
          <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="index.php" method="post">
          <div class="mb-4">
            <input class="w-full p-2 border border-gray-300 rounded" type="text" name="username" placeholder="Username" required />
          </div>
          <div class="mb-4">
            <input class="w-full p-2 border border-gray-300 rounded" type="password" name="password" placeholder="Password" required />
          </div>
          <button class="w-full bg-blue-600 text-white p-2 rounded font-bold">
            Log In
          </button>
        </form>

        <div class="text-center mt-4">
          <a class="text-blue-600" href="#">Forgotten password?</a>
        </div>

        <div class="text-center mt-4">
          <hr class="my-4"/>
          <a href="register.php" class="w-full bg-green-600 text-white p-2 rounded font-bold block text-center">
            Create New Account
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
