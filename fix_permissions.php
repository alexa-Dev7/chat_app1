<?php
// Ensure chat folder and files have correct permissions
if (!is_dir('chats')) mkdir('chats', 0777, true);
chmod('chats', 0777);

foreach (glob('chats/*.json') as $file) {
    chmod($file, 0666);
}

echo "✅ Permissions fixed!";


