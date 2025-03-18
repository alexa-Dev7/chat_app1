<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$users = json_decode(file_get_contents('users.json'), true);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/reset.css">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Users - Chat App</title>
</head>
<body>
<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    <h3>Users List</h3>
    <ul>
        <?php
        foreach ($users as $user => $data) {
            if ($user != $_SESSION['username']) {
                echo "<li>$user <a href='inbox.php?user=" . urlencode($user) . "'>Message</a></li>";
            }
        }
        ?>
    </ul>
    <a href="logout.php">Logout</a>
</div>
</body>
</html>
