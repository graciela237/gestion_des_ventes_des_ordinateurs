<?php
require_once '../DatabaseConnection/db_config.php';
header('Content-Type: application/json'); // Ensure JSON response
session_start();

$response = ['success' => false, 'message' => 'Invalid request'];

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ADD USER
    if ($action === 'add') {
        if (isset($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'], $_POST['role_id'], $_POST['password'])) {
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $role_id = intval($_POST['role_id']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing

            // Check if email already exists
            $check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
            } else {
                // Insert new user
                $insert_sql = "INSERT INTO users (first_name, last_name, email, phone_number, role_id, password_hash) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('ssssis', $first_name, $last_name, $email, $phone, $role_id, $password);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Utilisateur ajouté avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'utilisateur.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
    }

    // EDIT USER (Fixed)
    elseif ($action === 'edit') {
        if (isset($_POST['user_id'], $_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'], $_POST['role_id'])) {
            $user_id = intval($_POST['user_id']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $role_id = intval($_POST['role_id']);

            // Check if email exists for another user
            $check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND user_id != ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('si', $email, $user_id);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur.'];
            } else {
                // Update user
                $update_sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone_number=?, role_id=? WHERE user_id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ssssii", $first_name, $last_name, $email, $phone, $role_id, $user_id);

                if ($stmt->execute()) {
                    // Check if a new password is provided
                    if (!empty($_POST['password'])) {
                        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $pw_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                        $pw_stmt = $conn->prepare($pw_sql);
                        $pw_stmt->bind_param('si', $password_hash, $user_id);
                        $pw_stmt->execute();
                    }

                    $response = ['success' => true, 'message' => 'Utilisateur mis à jour avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'utilisateur.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Paramètres manquants.'];
        }
    }

    // DELETE USER (Fixed)
   elseif ($action === 'delete' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // START TRANSACTION to ensure data integrity
    $conn->begin_transaction();

    try {
        // DELETE associated records first (adjust for other related tables if needed)
        $conn->query("DELETE FROM wishlists WHERE user_id = $user_id");
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");
        $conn->query("DELETE FROM orders WHERE user_id = $user_id");
        $conn->query("DELETE FROM notifications WHERE user_id = $user_id");
        $conn->query("DELETE FROM shipping_addresses WHERE user_id = $user_id");

        // DELETE USER
        $delete_sql = "DELETE FROM users WHERE user_id=?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Commit transaction
            $conn->commit();
            $response = ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
        } else {
            throw new Exception('Erreur lors de la suppression.');
        }

        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback if anything fails
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
}


    // GET USER DETAILS
    elseif ($action === 'get_user' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        $user_sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($user_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response = ['success' => true, 'user' => $result->fetch_assoc()];
        } else {
            $response = ['success' => false, 'message' => 'Utilisateur non trouvé.'];
        }
        $stmt->close();
    }
}

// Return JSON response
echo json_encode($response);
exit;
