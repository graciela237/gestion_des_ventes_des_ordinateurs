<?php
require_once 'DatabaseConnection/db_config.php';

// Test MySQLi Connection
if ($conn) {
    echo "MySQLi Connection Successful!<br>";
    
    // Attempt a simple query
    $result = mysqli_query($conn, "SHOW TABLES FROM " . DB_NAME);
    if ($result) {
        echo "Tables in database:<br>";
        while ($row = mysqli_fetch_array($result)) {
            echo $row[0] . "<br>";
        }
    } else {
        echo "Query failed: " . mysqli_error($conn);
    }
}

// Test PDO Connection
try {
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "PDO Connection Successful! MySQL Version: " . $version;
} catch (PDOException $e) {
    echo "PDO Connection Failed: " . $e->getMessage();
}
?>