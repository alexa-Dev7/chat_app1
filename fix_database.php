<?php
// PostgreSQL Credentials
$host = "dpg-cvgd5atrie7s73bog17g-a";
$dbname = "pager_sivs";
$user = "pager_sivs_user";
$password = "L2iAd4DVlM30bVErrE8UVTelFpcP9uf8";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Check existing columns
    $columns = [];
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'messages'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['column_name'];
    }

    // If both recipient and receiver exist, drop recipient
    if (in_array('recipient', $columns) && in_array('receiver', $columns)) {
        $pdo->exec("ALTER TABLE messages DROP COLUMN recipient;");
        echo "✅ Column 'recipient' was dropped (duplicate of 'receiver').\n";
    } elseif (!in_array('receiver', $columns)) {
        echo "❌ Error: Column 'receiver' does not exist. Fix manually.\n";
    } else {
        echo "✅ Database is already correct.\n";
    }

} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}
?>
