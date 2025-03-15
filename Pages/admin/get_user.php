<?php
session_start();
require_once '../DatabaseConnection/db_config.php';

if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $user = getUserById($conn, $userId);

    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}

function getUserById($conn, $id) {
    $query = "SELECT u.*, r.role_name FROM users u 
              JOIN roles r ON u.role_id = r.role_id 
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}
?>