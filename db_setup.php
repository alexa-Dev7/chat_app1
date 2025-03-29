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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create or update the Messages table (sender and recipient are foreign keys referencing users(id))
    $pdo->exec("
  CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    sender INT NOT NULL REFERENCES users(id),
    recipient INT NOT NULL REFERENCES users(id),
    text TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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

    // Optionally update tables to handle changes in case they already exist
    // Example: ALTER table columns if needed (e.g., add or modify fields).
    $pdo->exec("
        ALTER TABLE IF EXISTS users
        ADD COLUMN IF NOT EXISTS last_login TIMESTAMP;
    ");

    $pdo->exec("
        ALTER TABLE IF EXISTS messages
        ADD COLUMN IF NOT EXISTS read_status BOOLEAN DEFAULT FALSE;
    ");

    echo "✅ Database setup and update complete!";
} catch (PDOException $e) {
    die("❌ Database setup/update failed: " . $e->getMessage());
}
?>
