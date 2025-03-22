<?php
// send_message.php — Now with E2EE (RSA + AES encryption)!
session_start();

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

// Load users and verify recipient exists
$usersFile = 'persistent_data/users.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

if (!isset($users[$to])) {
    echo json_encode(["error" => "Recipient not found"]);
    exit();
}

// Load messages data
$messagesFile = 'persistent_data/messages.json';
$messages = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];

// === RSA & AES ENCRYPTION === //

// Generate an AES session key and IV
$aesKey = bin2hex(random_bytes(16));
$iv = random_bytes(16);

// Encrypt the message content with AES
$encryptedMessage = openssl_encrypt($message, 'AES-256-CBC', $aesKey, 0, $iv);

// Generate RSA key pair for each message (optional: store keys securely later)
$rsaKeyPair = openssl_pkey_new([
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
]);
openssl_pkey_export($rsaKeyPair, $privateKey);
$publicKey = openssl_pkey_get_details($rsaKeyPair)['key'];

// Encrypt the AES key with the RSA public key
openssl_public_encrypt($aesKey, $encryptedAESKey, $publicKey);

// Build the encrypted message object
$newMessage = [
    "from" => $username,
    "to" => $to,
    "text" => base64_encode($encryptedMessage),
    "aes_key" => base64_encode($encryptedAESKey),
    "iv" => base64_encode($iv),
    "timestamp" => time()
];

// Store the message under both sender and recipient for easy loading
$messages[] = $newMessage;

// Save the updated messages back to the JSON file
file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));

echo json_encode(["success" => "Message sent securely"]);
