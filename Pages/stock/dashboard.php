<?php
// Démarrer la session pour accéder aux données utilisateur
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle de gestionnaire de stock
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté ou n'est pas un gestionnaire de stock
    header("Location: ../login.php");
    exit();
}

// Définir le titre de la page
$pageTitle = "Tableau de Bord Gestionnaire de Stock";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - TechPro Ecommerce</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Fichiers CSS personnalisés -->
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <!-- Inclure la barre latérale du gestionnaire de stock -->
    <?php include "../includes/sidebar.php"; ?>
    
    <!-- Contenu principal -->
    <div class="main-content">
        <!-- En-tête de la page -->
        <div class="page-header">
            <h1><?= $pageTitle ?></h1>
            <div class="breadcrumb">
                <a href="stock_manager_dashboard.php">Accueil</a> / <span>Tableau de Bord</span>
            </div>
        </div>
        
        <!-- Contenu du tableau de bord -->
        <div class="dashboard-content">
            <!-- Cartes Statistiques -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Total des Produits</h4>
                        <h2>124</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Stock Disponible</h4>
                        <h2>3,458</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Ruptures de Stock</h4>
                        <h2>12</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon red">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Commandes en Attente</h4>
                        <h2>45</h2>
                    </div>
                </div>
            </div>
            
            <!-- Section Activité Récente -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Activité Récente</h3>
                </div>
                <div class="section-content">
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon blue">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="activity-details">
                                <p>Produit ajouté: <strong>Ordinateur Portable Dell XPS</strong></p>
                                <span class="activity-time">Il y a 3 heures</span>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon green">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="activity-details">
                                <p>Nouveau réapprovisionnement: <strong>500 unités de SSD 1TB</strong></p>
                                <span class="activity-time">Aujourd'hui</span>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon orange">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="activity-details">
                                <p>Alerte stock: <strong>Smartphone Galaxy S21</strong> en faible quantité</p>
                                <span class="activity-time">Hier</span>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon red">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="activity-details">
                                <p>Nouveau fournisseur ajouté: <strong>TechParts Ltd</strong></p>
                                <span class="activity-time">Cette semaine</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenu supplémentaire du tableau de bord -->
        </div>
        
        <!-- Pied de page -->
        <footer>
            <p>&copy; <?= date('Y') ?> TechPro Ecommerce. Tous droits réservés.</p>
        </footer>
    </div>
    
    <!-- Fichiers JavaScript -->
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>
