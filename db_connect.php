<?php
$host = "dpg-cvfu9ennoe9s73bkltpg-a";
$dbname = "pager_1n3k";
$user = "pager_1n3k_user";
$password = "XyB7njpb4E01Nl26iWWLJ30xMCDrlHux";
$port = 5432;

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
