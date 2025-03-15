<?php  
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}  

// Get cart count from database for logged-in users
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    require_once 'DatabaseConnection/db_config.php';
    
    try {
        $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $cartCount = $row['cart_count'] ?: 0;
        }
    } catch (Exception $e) {
        error_log("Error fetching cart count: " . $e->getMessage());
    }
}

// Determine current page to add active class  
$currentPage = basename($_SERVER['PHP_SELF']);  
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Main Stylesheets -->
    <link rel="stylesheet" href="Styles/header.css">
    <link rel="stylesheet" href="Styles/footer.css">
    
    <!-- Page-specific title will be set by the including page -->
    <title><?php echo isset($pageTitle) ? $pageTitle : 'TechPro - Gestion des Ventes d\'Ordinateurs'; ?></title>
</head>
<body <?php if(isset($_SESSION['user_id'])) echo 'class="user-logged-in"'; ?>>
    <nav>
        <div class="nav-content">
            <div class="logo">
                <i class="fas fa-laptop"></i>
                TechPro
            </div>
            
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            
            <div class="nav-links">
                <a href="index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="produit.php" class="<?= $currentPage == 'produit.php' ? 'active' : '' ?>">
                    <i class="fas fa-store"></i> Produits
                </a>
                <a href="about.php" class="<?= $currentPage == 'index.php' && isset($_GET['section']) && $_GET['section'] == 'about' ? 'active' : '' ?>">
                    <i class="fas fa-info-circle"></i> À propos
                </a>
                <a href="contact.php" class="<?= $currentPage == 'index.php' && isset($_GET['section']) && $_GET['section'] == 'contact' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Contact
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="cart.php" class="cart-icon <?= $currentPage == 'cart.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="cart-count"><?= $cartCount ?></span>
                </a>
                <?php else: ?>
                <div class="cart-icon" onclick="showLoginNotification()">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="cart-count">0</span>
                </div>
                <?php endif; ?>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="login-btn <?= $currentPage == 'login.php' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i> Connexion
                    </a>
                    <a href="register.php" class="register-btn <?= $currentPage == 'register.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </a>
                <?php else: ?>
                    <div class="user-profile">
                        <a href="<?= isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'super_admin') ? 'admin/dashboard.php' : 'profile.php' ?>" class="profile-btn <?= $currentPage == 'profile.php' ? 'active' : '' ?>">
                            <i class="fas fa-user-circle"></i> <?= isset($_SESSION['user_name']) ? explode(' ', $_SESSION['user_name'])[0] : 'Utilisateur' ?>
                        </a>
                        <a href="logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Cart Notification -->
    <div id="cart-notification">
        <i class="fas fa-check-circle"></i> Produit ajouté au panier!
    </div>
    
    <!-- Login Notification -->
    <div id="login-notification">
        <i class="fas fa-exclamation-circle"></i> Veuillez vous connecter pour accéder au panier
    </div>
    <div class="main-content">
    <script>
    // Function to show login notification
    function showLoginNotification() {
        const notification = document.getElementById('login-notification');
        if (notification) {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    }
    
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                // Toggle between hamburger and close icon
                const icon = menuToggle.querySelector('i');
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
    });
    </script>