<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Requête invalide'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit();
    }

    // Récupérer l'ID de l'utilisateur connecté
    $user_id = $_SESSION['user_id'];

    // Nettoyer et récupérer les données du formulaire
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $quarter = trim($_POST['quarter'] ?? '');

    // Vérifier si l'email existe déjà pour un autre utilisateur
    $check_email_sql = "SELECT COUNT(*) AS count FROM users WHERE email = ? AND user_id != ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $email_check_result = $stmt->get_result()->fetch_assoc();

    if ($email_check_result['count'] > 0) {
        echo json_encode(['success' => false, 'message' => "Cet email est déjà utilisé par un autre utilisateur."]);
        exit();
    }

    // Mettre à jour les informations de l'utilisateur
    $sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone_number=?, country=?, state=?, quarter=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone_number, $country, $state, $quarter, $user_id);

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Profil mis à jour avec succès.'];
    } else {
        $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour du profil.'];
    }

    echo json_encode($response);
    exit();
}
?>
