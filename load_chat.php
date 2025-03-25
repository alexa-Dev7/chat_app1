<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON response

if (!isset($_SESSION['username'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$username = $_SESSION['username'];
$user = $_GET['user'] ?? '';

if (empty($user)) {
    echo json_encode(["error" => "No user specified"]);
    exit();
}

// Determine the correct file
$filename = "chats/" . $username . "_" . $user . ".json";
if (!file_exists($filename)) {
    $filename = "chats/" . $user . "_" . $username . ".json";
}

// Load messages or return empty array
if (file_exists($filename)) {
    $messages = json_decode(file_get_contents($filename), true);
    echo json_encode(["messages" => $messages ?: []]);
} else {
    echo json_encode(["messages" => []]);
}
