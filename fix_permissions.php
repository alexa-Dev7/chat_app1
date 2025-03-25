<?php
// Set 'chats' folder to full read-write-execute permissions
chmod('chats', 0777);

// Loop through all JSON files in the 'chats' folder and set read-write permissions
foreach (glob('chats/*.json') as $file) {
    chmod($file, 0666);
}

echo "✅ Permissions fixed!";
