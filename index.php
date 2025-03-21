<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: inbox.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents('persistent_data/users.json'), true);

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (isset($users[$username]) && password_verify($password, $users[$username]['password'])) {
        $_SESSION['username'] = $username;

        // Save persistent session data
        $sessions = json_decode(file_get_contents('persistent_data/sessions.json'), true);
        $sessions[$username] = session_id();
        file_put_contents('persistent_data/sessions.json', json_encode($sessions));

        header("Location: inbox.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        <?= isset($error) ? "<p class='error'>$error</p>" : "" ?>
    </form>
</div>

</body>
</html>
