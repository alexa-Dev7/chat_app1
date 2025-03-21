<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $users = json_decode(file_get_contents('persistent_data/users.json'), true);

    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if (!isset($users[$username])) {
        $users[$username] = ['password' => $password];
        file_put_contents('persistent_data/users.json', json_encode($users));

        header("Location: index.php");
        exit();
    } else {
        $error = "Username already exists!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<div class="login-container">
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
        <p>Already have an account? <a href="index.php">Login here</a></p>
        <?= isset($error) ? "<p class='error'>$error</p>" : "" ?>
    </form>
</div>

</body>
</html>
