<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechPro - Gestion des Ventes d'Ordinateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary-color: #1a237e;
            --secondary-color: #303f9f;
            --accent-color: #ff4081;
            --light-gray: #f5f6fa;
            --dark-gray: #333;
            --success-color: #4CAF50;
        }

        body {
            background-color: var(--light-gray);
        }

        nav {
            background-color: var(--primary-color);
            padding: 1rem;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .nav-links a:hover {
            background-color: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cart-icon:hover {
            transform: scale(1.1);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .login-btn {
            background-color: var(--accent-color);
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .login-btn:hover {
            background-color: #f50057;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .hero {
            height: 80vh;
            background: linear-gradient(rgba(26,35,126,0.9), rgba(26,35,126,0.7)), url('/api/placeholder/1920/1080') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 1rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: url('/api/placeholder/1920/1080') center/cover;
            opacity: 0.1;
            animation: zoom 20s infinite alternate;
        }

        @keyframes zoom {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .hero-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .hero-btn:hover {
            background-color: #f50057;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .products-section {
            padding: 6rem 2rem 4rem;
            background-color: var(--light-gray);
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background-color: var(--accent-color);
            border-radius: 2px;
        }

        .section-header p {
            color: var(--dark-gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 1rem auto;
        }

        .products-grid {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: all 0.3s;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .product-details {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.3rem;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .product-specs {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .product-price {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .old-price {
            font-size: 1.2rem;
            color: #999;
            text-decoration: line-through;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            font-size: 0.9rem;
            flex: 1;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .btn-secondary {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        .add-to-cart {
            background-color: var(--success-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-to-cart:hover {
            background-color: #388E3C;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        #cart-notification {
            position: fixed;
            top: 80px;
            right: 20px;
            background-color: var(--success-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            display: none;
            animation: slideIn 0.3s ease-out;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        footer {
            background-color: var(--dark-gray);
            color: white;
            padding: 4rem 2rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent-color);
        }

        .footer-section p {
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-section ul li a:hover {
            color: var(--accent-color);
            padding-left: 5px;
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .product-card {
                margin: 0 auto;
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-content">
            <div class="logo">
                <i class="fas fa-laptop"></i>
                TechPro
            </div>
            <div class="nav-links">
                <a href="#products"><i class="fas fa-store"></i> Produits</a>
                <a href="#about"><i class="fas fa-info-circle"></i> À propos</a>
                <a href="#contact"><i class="fas fa-envelope"></i> Contact</a>
                <div class="cart-icon">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span class="cart-count">0</span>
                </div>
                <a href="login.php" class="login-btn">
                    <i class="fas fa-user"></i> Connexion
                </a>
            </div>
        </div>
    </nav>

    <div id="cart-notification">
        <i class="fas fa-check-circle"></i> Produit ajouté au panier!
    </div>

    <section class="hero">
        <div class="hero-content">
            <h1>Découvrez Notre Collection d'Ordinateurs</h1>
            <p>Des performances exceptionnelles pour tous vos besoins professionnels et personnels</p>
            <a href="#products" class="hero-btn">
    <i class="fas fa-shopping-bag"></i> Découvrir nos produits
</a>
        </div>
    </section>



    
    <section class="products-section" id="products">
        <div class="section-header">
            <h2>Nos Produits Vedettes</h2>
            <p>Découvrez notre sélection d'ordinateurs haut de gamme conçus pour répondre à tous vos besoins</p>
        </div>
        <div class="products-grid">
            <!-- Product Card 1 -->
            <div class="product-card">
                <img src="images/2.jpeg" alt="Laptop Pro" class="product-image">
                <span class="product-badge">Nouveau</span>
                <div class="product-details">
                    <h3 class="product-title">Laptop Pro X1</h3>
                    <p class="product-specs">
                        <i class="fas fa-microchip"></i> Intel i7 12th Gen<br>
                        <i class="fas fa-memory"></i> 16GB RAM DDR5<br>
                        <i class="fas fa-hdd"></i> 512GB NVMe SSD<br>
                        <i class="fas fa-desktop"></i> RTX 3060 6GB
                    </p>
                    <div class="product-price">
                        <span class="old-price">1499.99 €</span>
                        1299.99 €
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Détails
                        </a>
                        <button class="btn btn-primary">
                            
                            <i class="fas fa-shopping-cart"></i> Acheter
                        </button>
                        <button class="add-to-cart" onclick="addToCart()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>


            <!-- Product Card 2 -->
            <div class="product-card">
                <img src="images/4.jpeg" alt="Gaming PC" class="product-image">
                <span class="product-badge">Populaire</span>
                <div class="product-details">
                    <h3 class="product-title">Gaming PC Elite</h3>
                    <p class="product-specs">
                        <i class="fas fa-microchip"></i> AMD Ryzen 9 7950X<br>
                        <i class="fas fa-memory"></i> 32GB RAM DDR5<br>
                        <i class="fas fa-hdd"></i> 1TB NVMe SSD<br>
                        <i class="fas fa-desktop"></i> RTX 4080 16GB
                    </p>
                    <div class="product-price">2499.99 €</div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Détails
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Acheter
                        </button>
                        <button class="add-to-cart" onclick="addToCart()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card">
                <img src="images/1.jpeg" alt="Ultrabook" class="product-image">
                <span class="product-badge">Promo -15%</span>
                <div class="product-details">
                    <h3 class="product-title">Ultrabook Air</h3>
                    <p class="product-specs">
                        <i class="fas fa-microchip"></i> Intel i5 12th Gen<br>
                        <i class="fas fa-memory"></i> 8GB RAM DDR4<br>
                        <i class="fas fa-hdd"></i> 256GB NVMe SSD<br>
                        <i class="fas fa-desktop"></i> Intel Iris Xe
                    </p>
                    <div class="product-price">
                        <span class="old-price">999.99 €</span>
                        899.99 €
                    </div>
                    <div class="product-actions">
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-info-circle"></i> Détails
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Acheter
                        </button>
                        <button class="add-to-cart" onclick="addToCart()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>À propos de TechPro</h3>
                <p>Leader dans la vente d'ordinateurs et de solutions informatiques depuis 2010. Nous nous engageons à fournir les meilleurs produits et services à nos clients.</p>
            </div>
            <div class="footer-section">
                <h3>Liens Rapides</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Nos Produits</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Service Après-Vente</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Conditions Générales</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Politique de Confidentialité</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> +33 1 23 45 67 89</li>
                    <li><i class="fas fa-envelope"></i> contact@techpro.fr</li>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Rue de la Tech, Paris</li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 TechPro - Tous droits réservés</p>
        </div>
    </footer>

    <script>
        let cartCount = 0;
        
        function addToCart() {
            cartCount++;
            document.querySelector('.cart-count').textContent = cartCount;
            
            // Show notification
            const notification = document.getElementById('cart-notification');
            notification.style.display = 'block';
            
            // Hide notification after 2 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 2000);
        }

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>