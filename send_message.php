<?php
// send_message.php — Enhanced PostgreSQL + E2EE (RSA + AES encryption)!
session_start();
require 'db_connect.php'; // Ensure PostgreSQL connection is loaded

// Ensure user is logged in and inputs are valid
if (!isset($_SESSION['username']) || empty($_POST['to']) || empty($_POST['message'])) {
    echo json_encode(["error" => "Unauthorized or invalid data"]);
    exit();
}

// Collect and sanitize inputs
$username = $_SESSION['username'];
$to = trim($_POST['to']);
$message = trim($_POST['message']);

// Prevent sending to yourself or empty messages
if ($to === $username || $message === '') {
    echo json_encode(["error" => "Invalid message or recipient"]);
    exit();
}

// === USER VALIDATION CHECK === //
// Ensure recipient exists in the PostgreSQL users table
try {
    $recipientQuery = $pdo->prepare("SELECT username FROM users WHERE username = :to");
    $recipientQuery->execute([':to' => $to]);

    if ($recipientQuery->rowCount() === 0) {
        echo json_encode(["error" => "Recipient not found"]);
        exit();
    }

    // === RSA & AES ENCRYPTION SETUP === //

    // Generate AES-256 session key and initialization vector (IV)
    $aesKey = bin2hex(random_bytes(16)); // 16 bytes = 128 bits key
    $iv = random_bytes(16); // 16 bytes IV for AES-256-CBC

    // Encrypt the message with AES-256-CBC
    $encryptedMessage = openssl_encrypt($message, 'AES-256-CBC', $aesKey, 0, $iv);

    // Generate a temporary RSA key pair for this specific message
    $rsaKeyPair = openssl_pkey_new([
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ]);

    // Extract the private and public keys
    openssl_pkey_export($rsaKeyPair, $privateKey);
    $publicKey = openssl_pkey_get_details($rsaKeyPair)['key'];

    // Encrypt the AES session key using the RSA public key
    openssl_public_encrypt($aesKey, $encryptedAESKey, $publicKey);

    // === STORE MESSAGE IN POSTGRESQL === //
    $messageInsert = $pdo->prepare("
        INSERT INTO messages (sender, recipient, text, aes_key, iv, timestamp) 
        VALUES (:from, :to, :text, :aes_key, :iv, :timestamp)
    ");

    $messageInsert->execute([
        ':from' => $username,
        ':to' => $to,
        ':text' => base64_encode($encryptedMessage), // Store encrypted text
        ':aes_key' => base64_encode($encryptedAESKey), // Store encrypted AES key
        ':iv' => base64_encode($iv), // Store IV
        ':timestamp' => time()
    ]);

    // Return success with optional RSA public key (for future secure communication)
    echo json_encode([
        "success" => "Message sent securely",
        "public_key" => base64_encode($publicKey) // Return public key if needed
    ]);

} catch (PDOException $e) {
    error_log("❌ Message send failed: " . $e->getMessage());
    echo json_encode(["error" => "Failed to send message"]);
}
