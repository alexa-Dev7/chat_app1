<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents("users.json"), true);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    foreach ($users as $user) {
        if ($user['username'] == $username && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: users.php");
            exit();
        }
    }
    echo "Invalid login!";
}
?>

<form method="post">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
    <a href="register.php">Register</a>
</form>
