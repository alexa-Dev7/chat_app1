<?php
session_start();

// Ensure persistent_data directory exists
if (!file_exists('persistent_data')) {
    mkdir('persistent_data', 0777, true);
}

// Load users.json (or create if missing)
$usersFile = 'persistent_data/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Check if the username already exists
    if (isset($users[$username])) {
        $error = "Username already exists!";
    } else {
        // Save user data securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $users[$username] = [
            'password' => $hashedPassword,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Save data persistently to JSON
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

        // Start session for the new user
        $_SESSION['username'] = $username;

        // Redirect to inbox after signup
        header('Location: inbox.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Sender Sign Up</title>
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
          Create a new account
        </h2>
        <p class="text-center text-gray-600 mb-4">
          It's quick and easy.
        </p>

        <?php if (!empty($error)): ?>
          <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="register.php" method="post">
          <div class="flex space-x-2 mb-4">
            <input class="w-1/2 p-2 border border-gray-300 rounded" type="text" name="username" placeholder="Username" required/>
            <input class="w-1/2 p-2 border border-gray-300 rounded" type="password" name="password" placeholder="Password" required/>
          </div>

          <div class="mb-4">
            <input class="w-full p-2 border border-gray-300 rounded" type="email" name="email" placeholder="Email address" required/>
          </div>

          <p class="text-xs text-gray-600 mb-4">
            This is a private messaging programme. Developed by Trishit
            <a class="text-blue-600" href="#">Learn more.</a>
          </p>

          <p class="text-xs text-gray-600 mb-4">
            By clicking Sign Up, you agree to our
            <a class="text-blue-600" href="#">Terms</a>,
            <a class="text-blue-600" href="#">Privacy Policy</a>, and
            <a class="text-blue-600" href="#">Cookies Policy</a>.
            Lightweight messaging software. More effective than any other messaging software.
          </p>

          <button class="w-full bg-green-600 text-white p-2 rounded font-bold">
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


