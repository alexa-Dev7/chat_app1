<?php
session_start();

// Database connection setup
require 'db_connect.php';

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    try {
        // Remove user session from the database
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE username = :username");
        $stmt->execute(['username' => $username]);

    } catch (PDOException $e) {
        echo "❌ Failed to clear session from database: " . $e->getMessage();
    }
}

// Destroy PHP session
session_unset();
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
