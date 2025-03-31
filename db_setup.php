<?php
// Include DB connection
require 'db_connect.php';

try {
    // Create or update the Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_active TIMESTAMP DEFAULT NOW()  -- Added last_active column
        )
    ");

    // Create or update the Messages table (sender and recipient are foreign keys referencing users(username))
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id SERIAL PRIMARY KEY,
            sender VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            receiver VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            text TEXT NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_status BOOLEAN DEFAULT FALSE
        )
    ");

    // Create or update the Sessions table (used for session management)
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

    // Alter tables to add missing columns if they don't exist
    $pdo->exec("
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS last_active TIMESTAMP DEFAULT NOW();
    ");

    $pdo->exec("
        ALTER TABLE messages 
        ADD COLUMN IF NOT EXISTS read_status BOOLEAN DEFAULT FALSE;
    ");

    echo "✅ Database setup and update complete!";
} catch (PDOException $e) {
    die("❌ Database setup/update failed: " . $e->getMessage());
}
?>
