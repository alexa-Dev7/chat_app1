<?php
session_start();

// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    die(json_encode(["status" => "error", "message" => "User not logged in."]));
}

$sender = $_SESSION['username'];
$receiver = $_POST['to'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($receiver) || empty($message)) {
    die(json_encode(["status" => "error", "message" => "Receiver and message cannot be empty."]));
}

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Insert message into database
    $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver, text, timestamp) VALUES (:sender, :receiver, :text, NOW())");
    $stmt->execute([
        'sender' => $sender,
        'receiver' => $receiver,
        'text' => $message
    ]);

    // Get receiver's FCM token
    $stmt = $pdo->prepare("SELECT fcm_token FROM users WHERE username = :receiver");
    $stmt->execute(['receiver' => $receiver]);
    $receiverToken = $stmt->fetchColumn();

    if ($receiverToken) {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'title' => "New Message from $sender",
            'body' => $message,
            'icon' => 'icon.png',
            'click_action' => 'inbox.php'
        ];
        $fcmData = [
            'to' => $receiverToken,
            'notification' => $notification
        ];

        $headers = [
            'Authorization: key=YOUR_SERVER_KEY',
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmData));

        $result = curl_exec($ch);
        curl_close($ch);
    }

    echo json_encode(["status" => "success", "message" => "Message sent."]);

} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]));
}
?>
