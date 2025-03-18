<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$users = json_decode(file_get_contents("users.json"), true);
$messages = json_decode(file_get_contents("messages.json"), true);

echo "<h2>Welcome, $username!</h2>";

foreach ($users as $user) {
    if ($user['username'] !== $username) {
        $unread = false;

        foreach ($messages as $msg) {
            if ($msg['receiver'] == $username && $msg['sender'] == $user['username'] && $msg['status'] === "unread") {
                $unread = true;
                break;
            }
        }

        $notification = $unread ? "🔴" : "";
        echo "<div><strong>{$user['username']}</strong> $notification <a href='chat.php?user={$user['username']}'>Message</a></div>";
    }
}

echo "<br><a href='logout.php'>Logout</a>";
