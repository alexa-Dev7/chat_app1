<?php
// Include DB connection
require 'db_connect.php';

try {
    // ✅ Create Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // ✅ Create Messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id SERIAL PRIMARY KEY,
            sender VARCHAR(50) NOT NULL,
            recipient VARCHAR(50) NOT NULL,
            text TEXT NOT NULL,
            aes_key TEXT DEFAULT '',
            iv TEXT DEFAULT '',
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender) REFERENCES users(username) ON DELETE CASCADE,
            FOREIGN KEY (recipient) REFERENCES users(username) ON DELETE CASCADE
        )
    ");

    // ✅ Create Sessions table (linked to users)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    echo "✅ Database setup complete!";
} catch (PDOException $e) {
    die("❌ Database setup failed: " . $e->getMessage());
}
