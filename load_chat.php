<?php
// Secure load_chat.php
session_start();
require 'db_connect.php';  // Ensure database connection is loaded

// Ensure user is logged in
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo "Error: Unauthorized access!";
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

// Prevent users from chatting with themselves
if ($currentChatUser === $username) {
    echo "Error: You can't chat with yourself!";
    exit();
}

// === Load messages from PostgreSQL === //
try {
    $stmt = $pdo->prepare(
        "SELECT * FROM messages 
        WHERE (sender = :username AND receiver = :currentChatUser) 
        OR (sender = :currentChatUser AND receiver = :username) 
        ORDER BY timestamp ASC"
    );

    $stmt->execute([
        ':username' => $username,
        ':currentChatUser' => $currentChatUser
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === Build chat content dynamically === //
    $chatContent = '';
    foreach ($messages as $msg) {
        // Determine if message is outgoing or incoming
        $chatContent .= "<div class='message " . 
            ($msg['sender'] === $username ? 'outgoing' : 'incoming') . 
            "'>" . htmlspecialchars($msg['content']) . "</div>";
    }

    // If no messages exist between users, show a friendly message
    if (empty($chatContent)) {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    echo $chatContent;

} catch (PDOException $e) {
    error_log("❌ Error loading messages: " . $e->getMessage());
    echo "Error: Unable to load chat!";
}
