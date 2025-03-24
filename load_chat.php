<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$to = trim($_GET['user']);

// Ensure chat file path is consistent
$usersSorted = [strtolower($username), strtolower($to)];
sort($usersSorted);
$chatFile = "chats/" . implode("_", $usersSorted) . ".json";

// Load messages from JSON file
if (file_exists($chatFile)) {
    $messages = json_decode(file_get_contents($chatFile), true);
    $output = '';
    foreach ($messages as $msg) {
        $sender = $msg['sender'] === $username ? 'You' : htmlspecialchars($msg['sender']);
        $output .= "<p><strong>$sender:</strong> " . htmlspecialchars($msg['text']) . " <small>[" . $msg['timestamp'] . "]</small></p>";
    }
    echo json_encode(["messages" => $output]);
} else {
    echo json_encode(["messages" => "<p>No messages yet!</p>"]);
}
