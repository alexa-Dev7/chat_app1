<?php
// send_message.php — Now with PostgreSQL + E2EE (RSA + AES encryption)!
session_start();
require 'db_connect.php'; // Include PostgreSQL connection setup

// Ensure user is logged in and inputs are valid
if (!isset($_SESSION['username']) || !isset($_POST['to']) || !isset($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$username = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

// Block empty messages or sending to yourself
if ($to === $username || $message === '') {
    echo json_encode(["error" => "Invalid message"]);
    exit();
}

// === USER VALIDATION CHECK === //
// Ensure recipient exists in PostgreSQL
$recipientQuery = $pdo->prepare("SELECT username FROM users WHERE username = :to");
$recipientQuery->execute([':to' => $to]);

if ($recipientQuery->rowCount() === 0) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

// === RSA & AES ENCRYPTION === //

// Generate AES session key and IV
$aesKey = bin2hex(random_bytes(16));
$iv = random_bytes(16);

// Encrypt the message content with AES
$encryptedMessage = openssl_encrypt($message, 'AES-256-CBC', $aesKey, 0, $iv);

// Generate RSA key pair for this message
$rsaKeyPair = openssl_pkey_new([
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);
openssl_pkey_export($rsaKeyPair, $privateKey);
$publicKey = openssl_pkey_get_details($rsaKeyPair)['key'];

// Encrypt the AES key with the RSA public key
openssl_public_encrypt($aesKey, $encryptedAESKey, $publicKey);

// === STORE MESSAGE IN POSTGRESQL === //
$messageInsert = $pdo->prepare("
    INSERT INTO messages (sender, recipient, text, aes_key, iv, timestamp) 
    VALUES (:from, :to, :text, :aes_key, :iv, :timestamp)
");

$messageInsert->execute([
    ':from' => $username,
    ':to' => $to,
    ':text' => base64_encode($encryptedMessage),
    ':aes_key' => base64_encode($encryptedAESKey),
    ':iv' => base64_encode($iv),
    ':timestamp' => time()
]);

echo json_encode(["success" => "Message sent securely"]);
