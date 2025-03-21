<?php
session_start();

$usersFile = 'persistent_data/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
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
    <title>Login - Red Pages</title>
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
          Log in to Red Pages
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
          <a class="text-blue-600" href="#">
            Forgotten password?
          </a>
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
