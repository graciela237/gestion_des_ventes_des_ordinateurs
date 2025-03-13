<?php
// Start session to access user data
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../login.php");
    exit();
}

// Set page title
$pageTitle = "Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - TechPro Ecommerce</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard1.css">
    <script>
        function loadDashboardContent() {
            fetch("dashboard1.php")
                .then(response => response.text())
                .then(data => {
                    document.getElementById("dashboard-content").innerHTML = data;
                })
                .catch(error => console.error("Erreur de chargement du tableau de bord:", error));
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            loadDashboardContent();
        });
    </script>
</head>
<body>

    <!-- Include Sidebar -->
    <?php include "../includes/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div id="dashboard-content">
            <!-- Dashboard content will be loaded dynamically -->
            <p>Chargement du tableau de bord...</p>
        </div>
    </div>
<script src="../assets/js/sidebar.js"></script>
</body>
</html>
