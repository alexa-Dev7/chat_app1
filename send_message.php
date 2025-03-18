<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = $_POST['to'];
    $message = trim($_POST['message']);
    $from = $_SESSION['username'];

    $messages = json_decode(file_get_contents('messages.json'), true);
    $chat_key = $from . "_" . $to;

    $messages[$chat_key][] = ['from' => $from, 'text' => $message];
    file_put_contents('messages.json', json_encode($messages));

    header("Location: chat.php?user=" . urlencode($to));
    exit();
}
