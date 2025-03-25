<?php
// Ensure chat folder permissions
if (!is_dir('chats')) mkdir('chats', 0777, true);
chmod('chats', 0777);

// Ensure all chat files are writable
foreach (glob('chats/*.json') as $file) {
    chmod($file, 0666);
}

echo "✅ Permissions fixed!";
