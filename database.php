<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'assignment2db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logMessage('Database connection successful');
} catch (PDOException $e) {
    response(500, 'Database connection failed: ' . $e->getMessage());
    exit();
}

function logMessage($message) {
    file_put_contents('log.txt', $message . PHP_EOL, FILE_APPEND);
}

function response($status, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}