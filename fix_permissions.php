<?php
// Ensure the chats folder and each chat file has the right permissions
if (!is_dir('chats')) mkdir('chats', 0777, true);
chmod('chats', 0777);

foreach (glob('chats/*_*.json') as $file) {
    chmod($file, 0666);
}

echo "✅ Permissions fixed!";



