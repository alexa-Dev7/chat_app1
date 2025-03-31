<?php
// Include database connection
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // ✅ Ensure last_active column exists before adding
    $columnExists = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'users' AND column_name = 'last_active'
    ")->fetchColumn();

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_active TIMESTAMP DEFAULT NOW();");
    }

    // Create or update the Messages table
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

    // ✅ Ensure read_status column exists before adding
    $columnExists = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'messages' AND column_name = 'read_status'
    ")->fetchColumn();

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN read_status BOOLEAN DEFAULT FALSE;");
    }

    // Create or update the Sessions table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "✅ Database setup and update complete!";
} catch (PDOException $e) {
    die("❌ Database setup/update failed: " . $e->getMessage());
}
?>
