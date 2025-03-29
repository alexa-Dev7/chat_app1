<?php
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'messages'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "📌 Current columns in 'messages':<br>";
    foreach ($columns as $column) {
        echo "➡️ " . htmlspecialchars($column) . "<br>";
    }

} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}
?>
