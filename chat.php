<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$receiver = $_GET['user'] ?? '';
$messages = json_decode(file_get_contents("messages.json"), true);

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <div class="chat-container">

        <!-- Left Side Inbox -->
        <div class="inbox">
            <h2>Your Chats</h2>
            <?php
            foreach ($messages as $chat) {
                if ($chat['sender'] == $username || $chat['receiver'] == $username) {
                    $friend = $chat['sender'] === $username ? $chat['receiver'] : $chat['sender'];
                    $unread = $chat['status'] === "unread" && $chat['receiver'] == $username ? "🔴" : "";
                    echo "<div class='user'><a href='chat.php?user=$friend'>$friend $unread</a></div>";
                }
            }
            ?>
        </div>

        <!-- Right Side Chatbox -->
        <div class="chatbox">
            <?php if ($receiver): ?>
                <h2>Chat with <?php echo $receiver; ?></h2>
                <div class="messages">
                    <?php
                    foreach ($messages as &$chat) {
                        if (($chat['sender'] == $username && $chat['receiver'] == $receiver) ||
                            ($chat['sender'] == $receiver && $chat['receiver'] == $username)) {

                            $align = $chat['sender'] == $username ? "right" : "left";
                            $status = $chat['status'] == "read" && $chat['sender'] == $username ? "✔✔" : "✔";
                            echo "<div class='message $align'>{$chat['message']} <span class='status'>$status</span></div>";

                            if ($chat['receiver'] == $username) {
                                $chat['status'] = 'read';
                            }
                        }
                    }
                    file_put_contents("messages.json", json_encode($messages));
                    ?>
                </div>

                <div id="typing-indicator"></div>
                <input type="text" id="message" placeholder="Type a message..." oninput="updateTyping(true)">
                <button onclick="sendMessage()">Send</button>

                <script>
                    const receiver = "<?php echo $receiver; ?>";

                    function updateTyping(typing) {
                        fetch("update_typing.php", {
                            method: "POST",
                            body: JSON.stringify({ receiver, typing })
                        });
                    }

                    function sendMessage() {
                        const message = document.getElementById("message").value;
                        fetch("send_message.php", {
                            method: "POST",
                            body: JSON.stringify({ receiver, message })
                        }).then(() => window.location.reload());
                    }

                    setInterval(() => {
                        fetch("check_status.php", {
                            method: "POST",
                            body: JSON.stringify({ receiver })
                        })
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById("typing-indicator").innerText = data.typing ? "Typing..." : "";
                        });
                    }, 1000);
                </script>
            <?php else: ?>
                <h2>Select a user to start chatting!</h2>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
