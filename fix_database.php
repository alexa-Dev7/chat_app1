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

    // Rename 'recipient' to 'receiver' if it exists
    if (in_array('recipient', $columns) && !in_array('receiver', $columns)) {
        $pdo->exec("ALTER TABLE messages RENAME COLUMN recipient TO receiver;");
        echo "✅ Column 'recipient' renamed to 'receiver'.\n";
    }

    // Add 'receiver' column if missing
    if (!in_array('receiver', $columns)) {
        $pdo->exec("ALTER TABLE messages ADD COLUMN receiver VARCHAR(255) NOT NULL;");
        echo "✅ Column 'receiver' added.\n";
    } else {
        echo "✅ Column 'receiver' already exists.\n";
    }

} catch (PDOException $e) {
    die("❌ Database error: " . $e->getMessage());
}
?>
