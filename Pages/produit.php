<?php
// Start session and include necessary files
session_start();
require_once 'DatabaseConnection/db_config.php';
require_once 'get_products.php';

// Fetch featured products
$products = getProducts(true);

// Fetch categories for category filter
$stmt = $pdo->query("SELECT * FROM product_categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ensure cart is initialized if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cartCount = count($_SESSION['cart']);

// Page-specific title
$pageTitle = "TechPro - Nos Produits";

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
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="Styles/styles.css">
    
    <title><?= $pageTitle ?></title>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Cart Notification -->
    <div id="cart-notification">
        <i class="fas fa-check-circle"></i> Produit ajouté au panier!
    </div>

    <main>
        <section class="filter-section">
            <div class="filter-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="product-search" placeholder="Rechercher un produit...">
                </div>
                
                <div class="category-filter">
                    <select id="category-select">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['category_name']) ?>">
                                <?= htmlspecialchars($category['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="price-filter">
                    <label>Prix max:</label>
                    <input type="number" id="price-max" value="5000000" step="10000" min="0">
                    <span>FCFA</span>
                </div>
                
                <button class="sort-button" id="sort-button">
                    <i class="fas fa-sort"></i>
                    Trier par prix
                </button>
            </div>
        </section>

        <section class="products-section" id="products">
            <div class="section-header">
                <h2>Nos Produits Vedettes</h2>
                <p>Découvrez notre sélection d'ordinateurs haut de gamme conçus pour répondre à tous vos besoins</p>
            </div>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" 
                     data-price="<?= $product['price'] ?>" 
                     data-category="<?= htmlspecialchars($product['category_name']) ?>">
                    <img src="<?= htmlspecialchars($product['image_path']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-image">
                    <span class="product-badge"><?= htmlspecialchars($product['badge']) ?></span>
                    <div class="product-details">
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-category"><?= htmlspecialchars($product['category_name']) ?></p>
                        <p class="product-specs">
                            <?= nl2br(htmlspecialchars($product['specifications'])) ?>
                        </p>
                        <div class="product-price">
                            <?php if ($product['original_price']): ?>
                                <span class="old-price"><?= formatPrice($product['original_price']) ?></span>
                            <?php endif; ?>
                            <?= formatPrice($product['price']) ?>
                        </div>
                        <div class="product-actions">
                            <a href="detail.php?id=<?= $product['product_id'] ?>" class="btn btn-secondary">
    <i class="fas fa-info-circle"></i> Détails
</a>
                            <button class="btn btn-primary" onclick="addToCart(<?= $product['product_id'] ?>)">
                                <i class="fas fa-shopping-cart"></i> Acheter
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

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

        // Product filtering and sorting
        document.addEventListener('DOMContentLoaded', function() {
            const productSearch = document.getElementById('product-search');
            const priceMax = document.getElementById('price-max');
            const categorySelect = document.getElementById('category-select');
            const sortButton = document.getElementById('sort-button');
            let sortAscending = true;

            function filterProducts() {
                const searchTerm = productSearch.value.toLowerCase();
                const maxPrice = parseInt(priceMax.value);
                const selectedCategory = categorySelect.value;
                const products = document.querySelectorAll('.product-card');

                products.forEach(product => {
                    const title = product.querySelector('.product-title').textContent.toLowerCase();
                    const category = product.dataset.category;
                    const price = parseInt(product.dataset.price);
                    
                    const matchesSearch = title.includes(searchTerm);
                    const matchesPrice = price <= maxPrice;
                    const matchesCategory = !selectedCategory || category === selectedCategory;

                    product.style.display = (matchesSearch && matchesPrice && matchesCategory) ? 'block' : 'none';
                });
            }

            function sortProducts() {
                const productsGrid = document.querySelector('.products-grid');
                const products = Array.from(document.querySelectorAll('.product-card'));
                
                products.sort((a, b) => {
                    const priceA = parseInt(a.dataset.price);
                    const priceB = parseInt(b.dataset.price);
                    
                    return sortAscending ? priceA - priceB : priceB - priceA;
                });

                // Clear and re-append sorted products
                products.forEach(product => productsGrid.appendChild(product));
                
                // Update sort button icon
                sortButton.querySelector('i').className = sortAscending ? 
                    'fas fa-sort-amount-up' : 'fas fa-sort-amount-down';
            }

            // Event listeners
            productSearch.addEventListener('input', filterProducts);
            priceMax.addEventListener('input', filterProducts);
            categorySelect.addEventListener('change', filterProducts);
            sortButton.addEventListener('click', () => {
                sortAscending = !sortAscending;
                sortProducts();
            });
        });
    </script>
</body>
</html>