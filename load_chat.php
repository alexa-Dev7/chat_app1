<?php
// Secure load_chat.php
session_start();
require 'db_connect.php'; // Ensure database connection is loaded

// Ensure user is logged in
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

// === Load messages from PostgreSQL === //
try {
    $stmt = $pdo->prepare(
        "SELECT sender, text, timestamp FROM messages 
        WHERE (sender = :username AND recipient = :currentChatUser) 
        OR (sender = :currentChatUser AND recipient = :username) 
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
        $isOutgoing = ($msg['sender'] === $username) ? 'outgoing' : 'incoming';

        $chatContent .= "<div class='message $isOutgoing'>
                            <span class='chat-text'>" . htmlspecialchars($msg['text']) . "</span>
                            <span class='chat-time'>" . date('H:i', strtotime($msg['timestamp'])) . "</span>
                         </div>";
    }

    // If no messages exist between users, show a friendly message
    if (empty($chatContent)) {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    echo json_encode(["messages" => $chatContent]);

} catch (PDOException $e) {
    error_log("❌ Unable to load chat: " . $e->getMessage());
    echo json_encode(["error" => "Unable to load chat"]);
}
?>
