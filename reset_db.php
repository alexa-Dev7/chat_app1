<?php
// Database connection setup
$host = "dpg-cvf3tfjqf0us73flfkv0-a";
$dbname = "chat_app_ltof";
$user = "chat_app_ltof_user";
$password = "JtFCFOztPWwHSS6wV4gXbTSzlV6barfq";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected to database successfully!<br>";

    // Drop existing table if it exists (fresh start)
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "✅ Existing 'users' table dropped!<br>";

    // Create the 'users' table from scratch
    $createTableQuery = "
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createTableQuery);
    echo "✅ New 'users' table created successfully!<br>";

    // (Optional) Insert a test user
    $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)")
        ->execute([
            'username' => 'admin',
            'password' => $hashedPassword,
            'email' => 'admin@example.com'
        ]);
    echo "✅ Test user 'admin' added!<br>";

    echo "🚀 Database reset complete!";
} catch (PDOException $e) {
    die("❌ Database reset failed: " . $e->getMessage());
}
?>
