
<?php
session_start();
if (!isset($_SESSION['username'])) exit();

$username = $_SESSION['username'];
$currentChatUser = $_GET['user'] ?? null;
$messages = json_decode(file_get_contents('persistent_data/messages.json'), true);

if ($currentChatUser) {
    foreach ($messages as &$msg) {
        if ($msg['from'] === $currentChatUser && $msg['to'] === $username) {
            $msg['read'] = true;  // Mark messages as read
        }
    }
    file_put_contents('messages.json', json_encode($messages));

    foreach ($messages as $msg) {
        if (($msg['from'] === $username && $msg['to'] === $currentChatUser) ||
            ($msg['from'] === $currentChatUser && $msg['to'] === $username)) {
            echo '<div class="message ' . ($msg['from'] === $username ? 'outgoing' : 'incoming') . '">';
            echo htmlspecialchars($msg['text']);
            echo '</div>';
        }
    }
}
?>
