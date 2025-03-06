<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'techpro_ecommerce');

// Error Handling
error_reporting(E_ALL);
ini_set('display_errors', 1);  // Changed to 1 for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Establish MySQLi Connection
try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        throw new Exception("MySQLi Connection Error: " . mysqli_connect_error());
    }
    
    // Set charset to ensure proper character handling
    mysqli_set_charset($conn, 'utf8mb4');
} catch (Exception $e) {
    error_log($e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// PDO Connection (Optional but recommended)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}
?>