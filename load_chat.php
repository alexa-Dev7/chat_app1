<?php
// load_chat.php

// Ensure no output before session starts
ob_start();
session_start();

// Check user session
if (!isset($_SESSION['username'])) {
    echo "Error: Not logged in!";
    exit();
}

// Load data
$username = $_SESSION['username'];
$currentChatUser = $_GET['user'] ?? null;

$messages = json_decode(file_get_contents('persistent_data/messages.json'), true) ?? [];

// Build chat content
$chatContent = '';
foreach ($messages as $msg) {
    if (($msg['from'] === $username && $msg['to'] === $currentChatUser) ||
        ($msg['from'] === $currentChatUser && $msg['to'] === $username)) {
        $chatContent .= "<div class='message " . 
            ($msg['from'] === $username ? 'outgoing' : 'incoming') . 
            "'>" . htmlspecialchars($msg['text']) . "</div>";
    }
}

echo $chatContent;

// Ensure no unwanted output messes with the response
ob_end_flush();
