<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; 

require 'db_connect.php';

// ✅ Update last_active timestamp
try {
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE username = :username")
        ->execute(['username' => $username]);
} catch (PDOException $e) {
    error_log("Error updating last_active: " . $e->getMessage());
}

// ✅ Fetch all users except the logged-in one
$users = [];
try {
    $stmt = $pdo->prepare("SELECT username, last_active FROM users WHERE username != :username");
    $stmt->execute(['username' => $username]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox | Messenger</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">

<div class="flex h-screen">
    <div class="w-1/4 bg-white shadow-md p-4">
        <h2 class="text-lg font-bold">👤 <?= htmlspecialchars($username) ?> <a href="logout.php" class="text-red-500">Logout</a></h2>
        <h3 class="mt-4 font-semibold">Inbox</h3>
        <div id="inbox"></div>

        <h3 class="mt-4 font-semibold">All Users</h3>
        <ul id="userList" class="mt-2">
            <?php foreach ($users as $user): 
                $lastActive = strtotime($user['last_active']);
                $timeDiff = time() - $lastActive;
                $status = ($timeDiff <= 60) ? "<span class='text-green-500'>● Online</span>" : "<span class='text-gray-500'>Last Seen " . round($timeDiff / 60) . " min ago</span>";
            ?>
                <li class="user-item p-2 cursor-pointer hover:bg-gray-200 rounded" data-username="<?= htmlspecialchars($user['username']) ?>">
                    <?= htmlspecialchars($user['username']) ?> <?= $status ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="flex flex-col w-3/4 h-full bg-white shadow-md">
        <h3 id="chatWith" class="p-4 text-lg font-semibold bg-blue-500 text-white">Chat</h3>
        <div id="chatBody" class="flex flex-col flex-grow overflow-y-auto p-4 space-y-2 bg-gray-200"></div>
        <form id="chatForm" class="flex p-4 border-t" onsubmit="sendMessage(event)">
            <input type="text" id="messageInput" class="flex-grow p-2 border rounded-l-lg focus:outline-none" placeholder="Type a message..." required>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r-lg">➤</button>
        </form>
    </div>
</div>

<script>
    let currentChatUser = '';
    let unreadMessages = 0;

    $(document).on('click', '.user-item', function() {
        currentChatUser = $(this).data('username');
        document.getElementById('chatWith').innerText = `Chat with ${currentChatUser}`;
        document.getElementById('chatBody').innerHTML = ''; 
        loadChat(currentChatUser);
        markMessagesSeen(currentChatUser);
    });

    async function loadChat(chatKey) {
        try {
            const response = await fetch(`load_chat.php?chatKey=${encodeURIComponent(chatKey)}`);
            const data = await response.json();

            if (data.status === 'success') {
                const chatBody = document.getElementById('chatBody');
                chatBody.innerHTML = '';

                data.messages.forEach(msg => {
                    const isOwner = msg.sender === "<?= $username ?>"; 

                    const chatBubble = document.createElement('div');
                    chatBubble.className = `p-3 rounded-lg max-w-xs break-words ${
                        isOwner ? 'bg-green-500 text-white ml-auto' : 'bg-blue-500 text-white mr-auto'
                    } m-2 shadow-md`;

                    chatBubble.innerHTML = `<b>${msg.sender}:</b> ${msg.text} <i class="text-xs block opacity-75">${msg.timestamp}</i>
                    ${isOwner && msg.seen ? "<span class='text-gray-500 text-xs'>✔ Seen</span>" : ""}
                    `;

                    chatBody.appendChild(chatBubble);
                });

                chatBody.scrollTop = chatBody.scrollHeight;
                unreadMessages = 0;
                updateTabTitle();
            } else {
                alert('Error loading chat: ' + data.message);
            }
        } catch (error) {
            console.error('Error loading chat:', error);
        }
    }

    async function sendMessage(event) {
        event.preventDefault();
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value;

        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `to=${encodeURIComponent(currentChatUser)}&message=${encodeURIComponent(message)}`
            });

            const data = await response.json();
            if (data.status === 'success') {
                messageInput.value = '';
                loadChat(currentChatUser);
            } else {
                alert('Error sending message: ' + data.message);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    setInterval(() => {
        if (currentChatUser) loadChat(currentChatUser);
        fetch("chat_helper.php?action=update_active");
    }, 3000);

    setInterval(checkNewMessages, 1000);
</script>

</body>
</html>
