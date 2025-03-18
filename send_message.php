<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = $_SESSION['username'];
    $to = $_POST['to'] ?? null;
    $message = trim($_POST['message']);

    if ($to && !empty($message)) {
        $messages = json_decode(file_get_contents('messages.json'), true);

        $messages[] = [
            'from' => $from,
            'to' => $to,
            'text' => $message,
            'read' => false,
            'timestamp' => time()
        ];

        file_put_contents('messages.json', json_encode($messages, JSON_PRETTY_PRINT));

        // ✅ Redirect back to inbox with the selected user opened
        header("Location: inbox.php?user=" . urlencode($to));
        exit();
    }
}

// 🚨 If something goes wrong (empty message or no user selected)
header("Location: inbox.php");
exit();
