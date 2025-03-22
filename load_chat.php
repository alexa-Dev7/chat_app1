<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['username']) || !isset($_GET['user'])) {
    echo json_encode(["error" => "Unauthorized access!"]);
    exit();
}

$username = $_SESSION['username'];
$currentChatUser = trim($_GET['user']);

// Prevent self-chat
if ($currentChatUser === $username) {
    echo json_encode(["error" => "You can't chat with yourself!"]);
    exit();
}

// Load messages from PostgreSQL
try {
    $stmt = $pdo->prepare(
        "SELECT sender, text, timestamp FROM public.messages 
         WHERE (sender = :username AND recipient = :currentChatUser) 
         OR (sender = :currentChatUser AND recipient = :username) 
         ORDER BY timestamp ASC"
    );

    $stmt->execute([
        ':username' => $username,
        ':currentChatUser' => $currentChatUser
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build message output
    $chatContent = '';
    foreach ($messages as $msg) {
        $chatContent .= "<div class='message " .
            ($msg['sender'] === $username ? 'outgoing' : 'incoming') .
            "'><b>" . htmlspecialchars($msg['sender']) . ":</b> " . htmlspecialchars($msg['text']) . "</div>";
    }

    // If no messages exist, show a friendly message
    if (empty($chatContent)) {
        $chatContent = "<div class='message notice'>No messages yet. Start the conversation!</div>";
    }

    echo json_encode(["messages" => $chatContent]);

} catch (PDOException $e) {
    error_log("❌ SQL Error: " . $e->getMessage());
    echo json_encode(["error" => "Unable to load chat."]);
}
?>
