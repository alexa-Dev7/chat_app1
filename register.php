<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents("users.json"), true);
    $username = htmlspecialchars($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $users[] = ['username' => $username, 'password' => $password];
    file_put_contents("users.json", json_encode($users));

    $_SESSION['username'] = $username;
    header("Location: users.php");
    exit();
}
?>

<form method="post">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Register</button>
</form>
