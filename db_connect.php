<?php
// Database credentials
$host = getenv('DB_HOST') ?: 'dpg-cvf3tfjqf0us73flfkv0-a';
$dbname = getenv('DB_NAME') ?: 'chat_app_ltof';
$user = getenv('DB_USER') ?: 'chat_app_ltof_user';
$password = getenv('DB_PASSWORD') ?: 'JtFCFOztPWwHSS6wV4gXbTSzlV6barfq';
$port = getenv('DB_PORT') ?: 5432;

// PostgreSQL connection
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

