<?php
// Start session and include necessary files
session_start();
require_once 'DatabaseConnection/db_config.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$productId = intval($_GET['id']);

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, pc.category_name 
    FROM products p 
    JOIN product_categories pc ON p.category_id = pc.category_id 
    WHERE p.product_id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// If product not found, redirect to products page
if (!$product) {
    header('Location: products.php');
    exit;
}

// Fetch related products (same category)
$stmtRelated = $pdo->prepare("
    SELECT p.* 
    FROM products p 
    WHERE p.category_id = ? AND p.product_id != ? 
    LIMIT 4
");
$stmtRelated->execute([$product['category_id'], $productId]);
$relatedProducts = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);

// Ensure cart is initialized if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cartCount = count($_SESSION['cart']);

// Page-specific title
$pageTitle = "TechPro - " . htmlspecialchars($product['name']);

// Format price function
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Main Stylesheet -->
  <link rel="stylesheet" href="Styles/styles.css">
    
    <!-- Product Detail Specific Stylesheet -->
    <link rel="stylesheet" href="Styles/product-detail.css">
    
    <title><?= $pageTitle ?></title>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Cart Notification -->
    <div id="detail-cart-notification">
        <i class="fas fa-check-circle"></i> Produit ajouté au panier!
    </div>
<div class="main-content">
    <main>
        <div class="product-detail-container">
            <!-- <div class="product-detail-header">
                <a href="products.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour aux produits
                </a>
            </div> -->
            
            <div class="product-main">
                <div class="product-gallery">
                    <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-main-image">
                    
                    <?php if ($product['badge']): ?>
                        <span class="product-badge-large"><?= htmlspecialchars($product['badge']) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <h1 class="product-title-large"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="product-category-large">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                    </p>
                    
                    <div class="product-price-large">
                        <?php if ($product['original_price']): ?>
                            <span class="old-price-large"><?= formatPrice($product['original_price']) ?></span>
                        <?php endif; ?>
                        <span><?= formatPrice($product['price']) ?></span>
                    </div>
                    
                    <p class="availability">
                        <i class="fas fa-check-circle"></i> 
                        En stock - Livraison disponible
                    </p>
                    
                    <div class="product-actions-large">
                        <button class="btn btn-primary btn-large" onclick="addToCart(<?= $product['product_id'] ?>)">
                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                        </button>
                        <a href="#" class="btn btn-secondary btn-large">
                            <i class="fas fa-heart"></i> Ajouter aux favoris
                        </a>
                    </div>
                    
                    <div class="product-summary">
                        <h3>Aperçu</h3>
                        <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Description détaillée non disponible.')) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="product-details-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="specifications">Spécifications</button>
                    <button class="tab-button" data-tab="description">Description détaillée</button>
                    <button class="tab-button" data-tab="delivery">Livraison & Garantie</button>
                </div>
                
                <div class="tab-content">
                    <div id="specifications" class="tab-panel active">
                        <h3>Caractéristiques techniques</h3>
                        <?php
                        // Convert specifications to array for better display
                        $specs = explode("\n", $product['specifications']);
                        if (!empty($specs)): 
                        ?>
                            <ul class="specs-list">
                                <?php foreach ($specs as $spec): 
                                    if (trim($spec) === '') continue;
                                    $parts = explode(':', $spec, 2);
                                    $label = trim($parts[0] ?? '');
                                    $value = trim($parts[1] ?? '');
                                ?>
                                    <li>
                                        <strong><?= htmlspecialchars($label) ?>:</strong>
                                        <span><?= htmlspecialchars($value) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Spécifications détaillées non disponibles pour ce produit.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="description" class="tab-panel">
                        <h3>Description complète</h3>
                        <div class="full-description">
                            <?= nl2br(htmlspecialchars($product['description'] ?? 'Description détaillée non disponible.')) ?>
                        </div>
                    </div>
                    
                    <div id="delivery" class="tab-panel">
                        <h3>Livraison & Garantie</h3>
                        <div class="delivery-info">
                            <h4>Options de livraison</h4>
                            <ul>
                                <li><strong>Standard:</strong> Livraison en 3-5 jours ouvrables</li>
                                <li><strong>Express:</strong> Livraison en 24-48 heures (frais supplémentaires)</li>
                                <li><strong>Retrait en magasin:</strong> Disponible gratuitement sous 24 heures</li>
                            </ul>
                            
                            <h4>Garantie</h4>
                            <p>Tous nos produits sont couverts par une garantie de 24 mois contre les défauts de fabrication.
                            Pour plus d'informations sur notre politique de garantie, veuillez consulter notre page <a href="#">Garanties et Services</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($relatedProducts)): ?>
            <div class="related-products">
                <h2>Produits similaires</h2>
                <div class="related-grid">
                    <?php foreach ($relatedProducts as $related): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($related['image_path']) ?>" 
                             alt="<?= htmlspecialchars($related['name']) ?>" 
                             class="product-image">
                        <?php if ($related['badge']): ?>
                            <span class="product-badge"><?= htmlspecialchars($related['badge']) ?></span>
                        <?php endif; ?>
                        <div class="product-details">
                            <h3 class="product-title"><?= htmlspecialchars($related['name']) ?></h3>
                            <div class="product-price">
                                <?php if ($related['original_price']): ?>
                                    <span class="old-price"><?= formatPrice($related['original_price']) ?></span>
                                <?php endif; ?>
                                <?= formatPrice($related['price']) ?>
                            </div>
                            <div class="product-actions">
                                <a href="detail.php?id=<?= $related['product_id'] ?>" class="btn btn-secondary">
                                    <i class="fas fa-info-circle"></i> Détails
                                </a>
                                <button class="btn btn-primary" onclick="addToCart(<?= $related['product_id'] ?>)">
                                    <i class="fas fa-shopping-cart"></i> Acheter
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        // Cart functionality
       // Cart functionality
 function addToCart(productId) {
            // Create form data to send
            const formData = new FormData();
            formData.append('product_id', productId);

            // Send AJAX request
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success notification
                    const notification = document.getElementById('cart-notification');
                    notification.classList.add('show');
                    
                    // Update cart count in header
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cart_total;
                    }
                    
                    // Hide notification after 3 seconds
                    setTimeout(() => {
                        notification.classList.remove('show');
                    }, 3000);
                } else if (data.require_login) {
                    // Show login notification
                    const loginNotification = document.getElementById('login-notification');
                    loginNotification.classList.add('show');
                    
                    setTimeout(() => {
                        loginNotification.classList.remove('show');
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    }, 3000);
                } else {
                    // Show error message
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                alert('Une erreur est survenue. Veuillez réessayer plus tard.');
            });
        }

        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panels
                    document.querySelectorAll('.tab-button').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelectorAll('.tab-panel').forEach(panel => {
                        panel.classList.remove('active');
                    });
                    
                    // Add active class to clicked button and corresponding panel
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
    
</body>
</html>