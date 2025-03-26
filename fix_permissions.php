<?php
chmod('chats', 0777);
foreach (glob('chats/*.json') as $file) {
    chmod($file, 0666);
}
echo "Permissions fixed!";
