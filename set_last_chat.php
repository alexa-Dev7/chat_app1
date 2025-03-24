<?php
session_start();
if (isset($_POST['user'])) {
    $_SESSION['last_chat_user'] = $_POST['user'];
}
