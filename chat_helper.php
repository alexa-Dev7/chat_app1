<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$username = $_SESSION['username'];

$host = "dpg-cvgd5atrie7s73bog17g-a"; 
$dbname = "pager_sivs"; 
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? '';

if ($action === 'update_active') {
    updateActiveStatus($pdo, $username);
} elseif ($action === 'check_unread') {
    checkUnreadMessages($pdo, $username);
} elseif ($action === 'mark_seen') {
    markMessagesAsSeen($pdo, $username);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

function updateActiveStatus($pdo, $username) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET last_active = NOW() WHERE username = :username");
        $stmt->execute(['username' => $username]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update active status']);
    }
}

function checkUnreadMessages($pdo, $username) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS unread FROM messages WHERE receiver = :username AND seen = FALSE");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $unreadCount = $result['unread'] ?? 0;
        
        echo json_encode(['status' => 'success', 'unread' => $unreadCount]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to check unread messages']);
    }
}

function markMessagesAsSeen($pdo, $username) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['from'])) {
        $fromUser = $_POST['from'];

        try {
            $stmt = $pdo->prepare("UPDATE messages SET seen = TRUE WHERE receiver = :username AND sender = :fromUser");
            $stmt->execute(['username' => $username, 'fromUser' => $fromUser]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to mark messages as seen']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    }
}
?>
