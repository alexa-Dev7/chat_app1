<?php
session_start();
header('Content-Type: application/json');

// Database Connection (Ensure this matches your actual DB details)
$host = 'dpg-cvgd5atrie7s73bog17g-a';
$dbname = 'pager_sivs';
$user = 'pager_sivs_user';
$password = 'L2iAd4DVlM30bVErrE8UVTelFpcP9uf8';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$sender = $_SESSION['username'];
$receiver = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';

// Validate input
if (empty($receiver) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message or recipient is missing']);
    exit();
}

// Define file path
$messagesDir = 'chats';
$messagesFile = $messagesDir . '/messages.json';

// Ensure chats directory exists and set permissions
if (!is_dir($messagesDir)) {
    mkdir($messagesDir, 0777, true); // Create directory with full permissions
}

// Ensure messages.json file exists and is writable
if (!file_exists($messagesFile)) {
    file_put_contents($messagesFile, '{}'); // Create an empty JSON object
}

// Set file permissions to make it writable
chmod($messagesFile, 0666);

if (!is_writable($messagesFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied: Cannot write to messages.json']);
    exit();
}

try {
    // Load existing messages
    $messagesData = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        $messagesData = []; // Reset if corrupted
    }

    // Create a unique chat key
    $chatKey = $sender . '-' . $receiver;
    if (!isset($messagesData[$chatKey])) {
        $messagesData[$chatKey] = [];
    }

    // Append new message
    $messagesData[$chatKey][] = [
        'sender' => $sender,
        'receiver' => $receiver,
        'text' => $message,
        'time' => date('Y-m-d H:i:s')
    ];

    // Save back to JSON
    file_put_contents($messagesFile, json_encode($messagesData, JSON_PRETTY_PRINT));

    echo json_encode(['status' => 'success', 'message' => 'Message sent!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
}
?>
