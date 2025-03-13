<?php
session_start();
require_once '../DatabaseConnection/db_config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>Erreur : Vous devez être connecté.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get updated data from the form
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$phone_number = $_POST['phone_number'];
$country = $_POST['country'];
$state = $_POST['state'];
$quarter = $_POST['quarter'];

// Update user profile in the database
$query = "UPDATE users SET first_name=?, last_name=?, email=?, phone_number=?, country=?, state=?, quarter=? WHERE user_id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone_number, $country, $state, $quarter, $user_id);

if ($stmt->execute()) {
    echo "<p style='color: green;'>Profil mis à jour avec succès.</p>";
} else {
    echo "<p style='color: red;'>Erreur lors de la mise à jour du profil.</p>";
}

// Close DB connection
$stmt->close();
$conn->close();
?>
