// Drop existing tables if they exist
$pdo->exec("DROP TABLE IF EXISTS messages CASCADE");
$pdo->exec("DROP TABLE IF EXISTS sessions CASCADE");
$pdo->exec("DROP TABLE IF EXISTS users CASCADE");

// Create Users table first
$pdo->exec("
    CREATE TABLE users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password TEXT NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create Messages table
$pdo->exec("
    CREATE TABLE messages (
        id SERIAL PRIMARY KEY,
        sender VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
        receiver VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
        message TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create Sessions table
$pdo->exec("
    CREATE TABLE sessions (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL REFERENCES users(username) ON DELETE CASCADE,
        session_id VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
