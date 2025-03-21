<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) exit();

$username = $_SESSION['username'];
$to = $_POST['to'];
$message = trim($_POST['message']);

if ($message !== '') {
    $messages = json_decode(file_get_contents('persistent_data/messages.json'), true) ?? [];

    $messages[] = [
        'from' => $username,
        'to' => $to,
        'text' => $message,
        'timestamp' => time()
    ];

    file_put_contents('persistent_data/messages.json', json_encode($messages, JSON_PRETTY_PRINT));
}
