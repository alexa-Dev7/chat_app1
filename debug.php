<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Write errors to a log file
ini_set('log_errors', 1);
ini_set('error_log', 'chats/error_log.txt');

echo json_encode(["status" => "debug", "message" => "Debugging active!"]);
  
