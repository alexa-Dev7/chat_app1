<?php
// PostgreSQL Database Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check if the column 'recipient' exists before dropping
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'messages' AND column_name = 'recipient'");
    $columnExists = $stmt->fetch();

    if ($columnExists) {
        // Drop the 'recipient' column
        $pdo->exec("ALTER TABLE messages DROP COLUMN recipient;");
        echo "✅ Column 'recipient' was dropped successfully.";
    } else {
        echo "⚠️ Column 'recipient' does not exist, no action taken.";
    }

} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}
?>
