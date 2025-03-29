<?php
// load_inbox.php
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$username = $_SESSION['username'];
$messageFile = 'chats/messages.json';

$messagesData = [];
if (file_exists($messageFile)) {
    $messagesData = json_decode(file_get_contents($messageFile), true);
}

$inbox = [];
foreach ($messagesData as $chatKey => $messages) {
    if (strpos($chatKey, $username) !== false) {
        $lastMessage = end($messages);
        $inbox[] = [
            'chatKey' => $chatKey,
            'lastMessage' => $lastMessage['text'],
            'timestamp' => $lastMessage['time'],
            'receiver' => $lastMessage['receiver'],
        ];
    }
}

echo json_encode(['status' => 'success', 'inbox' => $inbox]);
?>
