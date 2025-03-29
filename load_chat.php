<?php
// load_chat.php
header('Content-Type: application/json');

if (!isset($_GET['chatKey'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chat key not provided']);
    exit();
}

$chatKey = $_GET['chatKey'];
$messageFile = 'chats/messages.json';

$messagesData = [];
if (file_exists($messageFile)) {
    $messagesData = json_decode(file_get_contents($messageFile), true);
}

if (!isset($messagesData[$chatKey])) {
    echo json_encode(['status' => 'error', 'message' => 'Chat not found']);
    exit();
}

$messages = $messagesData[$chatKey];

echo json_encode(['status' => 'success', 'messages' => $messages]);
?>
