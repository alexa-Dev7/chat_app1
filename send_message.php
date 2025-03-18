<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$to = $_POST['to'];
$message = trim($_POST['message']);
$from = $_SESSION['username'];

if ($message !== "") {
    $messages = json_decode(file_get_contents('messages.json'), true);
    $messages[] = [
        "from" => $from,
        "to" => $to,
        "text" => $message,
        "read" => false
    ];

    file_put_contents('messages.json', json_encode($messages));
}

header("Location: chat.php?user=" . urlencode($to));
exit();
