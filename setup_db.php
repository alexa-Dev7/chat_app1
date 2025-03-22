<?php
// Include DB connection
require 'db_connect.php';

try {
    // Ensure UTF8 encoding and clean schema handling
    $pdo->exec("SET NAMES 'utf8'");

    // Create Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
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

    // Create Sessions table — Fixed the typo!
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            session_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "✅ Database setup complete!";
} catch (PDOException $e) {
    die("❌ Database setup failed: " . $e->getMessage());
}
