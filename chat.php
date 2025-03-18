<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    header("Location: users.php");
    exit();
}

$current_user = $_SESSION['username'];
$chat_user = htmlspecialchars($_GET['user']);
$messages = json_decode(file_get_contents('messages.json'), true);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/reset.css">
    <link rel="stylesheet" href="assets/styles.css">
    <title>Chat with <?php echo $chat_user; ?></title>
</head>
<body>
<div class="container">
    <h2>Chat with <?php echo $chat_user; ?></h2>

    <div class="chatbox">
        <?php
        $chat_key = $current_user . "_" . $chat_user;
        if (isset($messages[$chat_key])) {
            foreach ($messages[$chat_key] as $msg) {
                echo "<p><strong>{$msg['from']}:</strong> {$msg['text']}</p>";
            }
        }
        ?>
    </div>

    <form action="send_message.php" method="post">
        <input type="hidden" name="to" value="<?php echo $chat_user; ?>">
        <input type="text" name="message" placeholder="Type a message..." required>
        <button type="submit">Send</button>
    </form>

    <a href="users.php">Back to Users</a>
</div>
</body>
</html>
