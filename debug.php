<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test the PHP environment
echo json_encode(["status" => "success", "message" => "Debug mode works!"]);
