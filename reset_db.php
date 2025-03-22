<?php
require 'db_connect.php';

try {
    echo "🚨 Resetting database...\n";

    // Drop existing tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS messages CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS sessions CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS users CASCADE");

    echo "✅ Old tables dropped!\n";

    // Recreate Users table
    $pdo->exec("
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Users table recreated!\n";

    // Recreate Messages table
    $pdo->exec("
        CREATE TABLE messages (
            id SERIAL PRIMARY KEY,
            sender VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            receiver VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            message TEXT NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Messages table recreated!\n";

    // Recreate Sessions table
    $pdo->exec("
        CREATE TABLE sessions (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
            session_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Sessions table recreated!\n";

    echo "🎉 Database reset complete!";
} catch (PDOException $e) {
    die("❌ Reset failed: " . $e->getMessage());
}
