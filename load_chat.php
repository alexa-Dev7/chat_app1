<?php
session_start();
require 'db_connect.php';

// Debug: Check if session username is set
if (!isset($_SESSION['username'], $_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$chatUser = trim($_GET['user']);

// Debugging: Check session and input values
// var_dump($_SESSION, $chatUser); // Uncomment for debugging

// Ensure recipient exists in the database
try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = :user");
    $stmt->execute([':user' => $chatUser]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "Recipient not found"]);
        exit();
    }
} catch (PDOException $e) {
    error_log("❌ SQL Error while checking recipient: " . $e->getMessage());
    echo json_encode(["error" => "Failed to check recipient"]);
    exit();
}

// Fetch messages between the two users
try {
    $stmt = $pdo->prepare("
        SELECT sender, recipient, text, timestamp
        FROM messages
        WHERE (sender = :username AND recipient = :chatUser)
           OR (sender = :chatUser AND recipient = :username)
        ORDER BY timestamp ASC
    ");
    $stmt->execute([
        ':username' => $username,
        ':chatUser' => $chatUser
    ]);

    // Check for any SQL errors
    if ($stmt->errorCode() != '00000') {
        error_log("❌ SQL Error during message fetching: " . implode(' ', $stmt->errorInfo()));
        echo json_encode(["error" => "Failed to load messages. SQL Error"]);
        exit();
    }

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate chat HTML
    $chatHTML = "";
    if (count($messages) === 0) {
        $chatHTML .= "<p class='notice'>No messages yet. Start chatting!</p>";
    } else {
        foreach ($messages as $msg) {
            $isSender = ($msg['sender'] === $username);
            $chatHTML .= "<div class='message " . ($isSender ? "sent" : "received") . "'>";
            $chatHTML .= "<p>" . htmlspecialchars($msg['text']) . "</p>";
            $chatHTML .= "<span>" . $msg['timestamp'] . "</span>";
            $chatHTML .= "</div>";
        }
    }

    echo json_encode(["messages" => $chatHTML]);

} catch (PDOException $e) {
    error_log("❌ Load chat error: " . $e->getMessage());
    echo json_encode(["error" => "Failed to load messages"]);
}
