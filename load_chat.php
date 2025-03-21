<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) exit();

$username = $_SESSION['username'];
$chatWith = $_GET['user'];
$messages = json_decode(file_get_contents('persistent_data/messages.json'), true) ?? [];

foreach ($messages as $msg) {
    if (($msg['from'] === $username && $msg['to'] === $chatWith) || 
        ($msg['from'] === $chatWith && $msg['to'] === $username)) {
        $class = ($msg['from'] === $username) ? 'outgoing' : 'incoming';
        echo "<div class='message $class'>" . htmlspecialchars($msg['text']) . "</div>";
    }
}
