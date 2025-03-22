<?php
// Secure load_chat.php — updated version
session_start();
require 'db_connect.php';  

// Ensure user is logged in and a recipient is provided
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access!"]);
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

// Prevent users from chatting with themselves
if ($currentChatUser === $username) {
    echo json_encode(["error" => "You can't chat with yourself!"]);
    exit();
}

try {
    // === Fetch messages between current user and chat partner === //
    $stmt = $pdo->prepare(
        "SELECT sender, receiver, content, timestamp 
         FROM messages 
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
        // Determine message direction (outgoing/incoming)
        $isOutgoing = $msg['sender'] === $username;

        $chatContent .= "<div class='message " . 
            ($isOutgoing ? 'outgoing' : 'incoming') . 
            "'>";

        $chatContent .= "<p>" . htmlspecialchars($msg['content']) . "</p>";

        // Format timestamp
        $chatContent .= "<span class='timestamp'>" . date('h:i A', strtotime($msg['timestamp'])) . "</span>";

        $chatContent .= "</div>";
    }

    // If no messages yet, show a friendly notice
    if (empty($chatContent)) {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    echo json_encode(["messages" => $chatContent]);

} catch (PDOException $e) {
    error_log("❌ Error loading messages: " . $e->getMessage());
    echo json_encode(["error" => "Unable to load chat!"]);
}
