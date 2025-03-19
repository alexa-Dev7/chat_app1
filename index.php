<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $users = json_decode(file_get_contents('users.json'), true);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        $_SESSION['username'] = $username;
        file_put_contents('sessions.json', json_encode(array_merge(json_decode(file_get_contents('sessions.json'), true), [$username => time()])));
        header("Location: users.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/reset.css">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Login - Chat App</title>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</body>
</html>
