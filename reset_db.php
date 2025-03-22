<?php
// Database connection setup
$host = "dpg-cvf3tfjqf0us73flfkv0-a";
$dbname = "chat_app_ltof";
$user = "chat_app_ltof_user";
$password = "JtFCFOztPWwHSS6wV4gXbTSzlV6barfq";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop old tables if needed
    $pdo->exec("DROP TABLE IF EXISTS messages, sessions, users CASCADE");

    // Create Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create Messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id SERIAL PRIMARY KEY,
            sender VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            receiver VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            message TEXT NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create Sessions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            session_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "✅ Database reset and setup complete!";
} catch (PDOException $e) {
    die("❌ Database setup failed: " . $e->getMessage());
}
?>
