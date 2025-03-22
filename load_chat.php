<?php
// load_chat.php — Secure chat loader with PostgreSQL and improved error handling
session_start();
require 'db_connect.php';  // Ensure database connection works

// Ensure user is logged in and a chat target is provided
if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

// 🚫 Prevent users from chatting with themselves
if ($currentChatUser === $username) {
    echo json_encode(["error" => "You can't chat with yourself!"]);
    exit();
}

try {
    // 🛠️ Fetch messages from PostgreSQL (ensure table names match your DB)
    $stmt = $pdo->prepare(
        "SELECT sender, recipient, text, timestamp 
        FROM messages 
        WHERE (sender = :username AND recipient = :currentChatUser) 
        OR (sender = :currentChatUser AND recipient = :username) 
        ORDER BY timestamp ASC"
    );

    $stmt->execute([
        ':username' => $username,
        ':currentChatUser' => $currentChatUser
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔧 Build the chat content (with message bubbles)
    $chatContent = '';
    foreach ($messages as $msg) {
        $isOutgoing = $msg['sender'] === $username ? 'outgoing' : 'incoming';

        // 🛠️ Format timestamps nicely
        $formattedTime = date("h:i A", strtotime($msg['timestamp']));

        // 📲 Construct message bubbles with timestamps
        $chatContent .= "
            <div class='message-bubble $isOutgoing'>
                <div class='message-text'>" . htmlspecialchars($msg['text']) . "</div>
                <div class='message-time'>$formattedTime</div>
            </div>
        ";
    }

    // If no messages exist between users, show a friendly placeholder
    if (empty($chatContent)) {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    // 🎉 Return the chat content to inbox.php as JSON
    echo json_encode(["messages" => $chatContent]);

} catch (PDOException $e) {
    // 🔥 Error logging for backend + visible error for frontend (TEMP for debugging)
    error_log("❌ Error loading messages: " . $e->getMessage());
    echo json_encode(["error" => "Unable to load chat: " . $e->getMessage()]);
}
