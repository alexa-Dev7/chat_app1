<?php
session_start();

// Remove the user's session from session tracking
$sessions = json_decode(file_get_contents('persistent_data/sessions.json'), true);
unset($sessions[$_SESSION['username']]);
file_put_contents('persistent_data/sessions.json', json_encode($sessions));

session_destroy();
header("Location: index.php");
exit();
