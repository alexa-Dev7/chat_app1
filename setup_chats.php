<?php
$folder = 'chats';
$filePath = "$folder/messages.json";

// Ensure "chats" folder exists
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// Ensure messages.json exists
if (!file_exists($filePath)) {
    file_put_contents($filePath, json_encode([]));
}

echo "Setup complete — 'chats/messages.json' is ready!";
