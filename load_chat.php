<?php
$chatKey = $_GET['chatKey'] ?? '';
$messageFile = 'chats/messages.json';

if (!$chatKey || !file_exists($messageFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid chat key or messages file not found.']);
    exit();
}

// Load messages
$messagesData = json_decode(file_get_contents($messageFile), true);
if (isset($messagesData[$chatKey])) {
    echo json_encode(['status' => 'success', 'messages' => $messagesData[$chatKey]]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Chat not found.']);
}
