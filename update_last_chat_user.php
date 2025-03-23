<?php
session_start();

if (isset($_POST['user'])) {
    $_SESSION['last_chat_user'] = $_POST['user'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'No user specified']);
}
