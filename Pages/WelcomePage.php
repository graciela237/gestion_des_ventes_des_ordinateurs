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
        .filter-section {
            background: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 70px;
            z-index: 100;
        }

        .filter-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 1rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .price-filter {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .price-filter input {
            width: 120px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .sort-button {
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .sort-button:hover {
            background-color: var(--secondary-color);
        }

        /* Update product grid styles for better filtering integration */
        .products-section {
            padding-top: 2rem;
        }

       .hero {
    position: relative;
    width: 100%;
    height: 80vh; /* Adjust height as needed */
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    overflow: hidden;
}
.hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -1;
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
    padding: 20px;
    background: rgba(0, 0, 0, 0.5); /* Optional: Dark overlay for better readability */
    border-radius: 10px;
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
    <video autoplay loop muted playsinline class="hero-video">
        <source src="images/background.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la lecture de vidéos.
    </video>
    <div class="hero-content">
        <h1>Découvrez Notre Collection d'Ordinateurs</h1>
        <p>Des performances exceptionnelles pour tous vos besoins professionnels et personnels</p>
        <a href="#products" class="hero-btn">
            <i class="fas fa-shopping-bag"></i> Découvrir nos produits
        </a>
    </div>
</section>
    <section class="filter-section">
        <div class="filter-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="product-search" placeholder="Rechercher un produit...">
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
            <!-- Product Card 1 -->
            <div class="product-card" data-price="849995">
                <img src="images/laptop1.jpeg" alt="Laptop Pro" class="product-image">
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
                        <span class="old-price">999.995 FCFA</span>
                        849.995 FCFA
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
            <div class="product-card" data-price="1649995">
                <img src="images/gamingpc1.jpeg" alt="Gaming PC" class="product-image">
                <span class="product-badge">Populaire</span>
                <div class="product-details">
                    <h3 class="product-title">Gaming PC Elite</h3>
                    <p class="product-specs">
                        <i class="fas fa-microchip"></i> AMD Ryzen 9 7950X<br>
                        <i class="fas fa-memory"></i> 32GB RAM DDR5<br>
                        <i class="fas fa-hdd"></i> 1TB NVMe SSD<br>
                        <i class="fas fa-desktop"></i> RTX 4080 16GB
                    </p>
                    <div class="product-price">1.649.995 FCFA</div>
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

            <!-- More Product Cards with FCFA... -->
            <!-- Product Card 3 -->
            <div class="product-card" data-price="549995">
    <img src="images/Ultrabook1.jpeg" class="product-image">
    <span class="product-badge">Promo -15%</span>
    <div class="product-details">
        <h3 class="product-title">Ultrabook Air</h3>
        <p class="product-specs">
            <i class="fas fa-microchip"></i> Intel i5 12e Gén<br>
            <i class="fas fa-memory"></i> 8 Go RAM DDR4<br>
            <i class="fas fa-hdd"></i> 256 Go NVMe SSD<br>
            <i class="fas fa-desktop"></i> Intel Iris Xe
        </p>
        <div class="product-price">
            <span class="old-price">649.995 FCFA</span>
            549.995 FCFA
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


            <!-- Product Card 4 -->
           <div class="product-card" data-price="2149995">
    <img src="images/WorkStation.jpeg" alt="WorkStation Pro" class="product-image">
    <span class="product-badge">Professionnel</span>
    <div class="product-details">
        <h3 class="product-title">WorkStation Pro</h3>
        <p class="product-specs">
            <i class="fas fa-microchip"></i> Intel i9 13900K<br>
            <i class="fas fa-memory"></i> 64 Go RAM DDR5<br>
            <i class="fas fa-hdd"></i> 2 To NVMe SSD<br>
            <i class="fas fa-desktop"></i> RTX 4090 24 Go
        </p>
        <div class="product-price">2.149.995 FCFA</div>
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


            <!-- Product Card 5 -->
            <div class="product-card" data-price="1199995">
    <img src="images/MacBook1.jpeg" alt="MacBook Pro" class="product-image">
    <span class="product-badge">Premium</span>
    <div class="product-details">
        <h3 class="product-title">MacBook Pro M2</h3>
        <p class="product-specs">
            <i class="fas fa-microchip"></i> Apple M2 Pro<br>
            <i class="fas fa-memory"></i> 16 Go RAM<br>
            <i class="fas fa-hdd"></i> 512 Go SSD<br>
            <i class="fas fa-desktop"></i> GPU 16 cœurs
        </p>
        <div class="product-price">1.199.995 FCFA</div>
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


            <!-- Product Card 6 -->
      <div class="product-card" data-price="499995">
    <img src="images/MiniPCCreator1.jpeg" alt="Mini PC" class="product-image">
    <span class="product-badge">Compact</span>
    <div class="product-details">
        <h3 class="product-title">Mini PC Creator</h3>
        <p class="product-specs">
            <i class="fas fa-microchip"></i> AMD Ryzen 5 7600X<br>
            <i class="fas fa-memory"></i> 16 Go RAM DDR5<br>
            <i class="fas fa-hdd"></i> 500 Go NVMe SSD<br>
            <i class="fas fa-desktop"></i> RTX 3050 8 Go
        </p>
        <div class="product-price">
            <span class="old-price">599.995 FCFA</span>
            499.995 FCFA
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
        <!-- Additional Gaming Mouse Products -->
<div class="product-card" data-price="89995">
    <img src="images/mouse1.jpeg" alt="Souris Gaming" class="product-image">
    <span class="product-badge">Gaming Pro</span>
    <div class="product-details">
        <h3 class="product-title">Souris Gaming Pro X1</h3>
        <p class="product-specs">
            <i class="fas fa-mouse"></i> Capteur 20K DPI<br>
            <i class="fas fa-bolt"></i> Temps de réponse 1ms<br>
            <i class="fas fa-weight"></i> 63g Ultra-léger<br>
            <i class="fas fa-battery-full"></i> Batterie 70h
        </p>
        <div class="product-price">89.995 FCFA</div>
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


<div class="product-card" data-price="69995">
    <img src="images/mouse2.jpeg" alt="Souris Gaming" class="product-image">
    <span class="product-badge">Sans Fil</span>
    <div class="product-details">
        <h3 class="product-title">Souris Gaming Pro X2</h3>
        <p class="product-specs">
            <i class="fas fa-mouse"></i> Capteur 16K DPI<br>
            <i class="fas fa-bolt"></i> Temps de réponse 1ms<br>
            <i class="fas fa-weight"></i> 75g Léger<br>
            <i class="fas fa-battery-full"></i> Batterie 60h
        </p>
        <div class="product-price">69.995 FCFA</div>
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


<!-- Printer Products -->
<div class="product-card" data-price="299995">
    <img src="images/printer1.jpeg" alt="Imprimante Laser" class="product-image">
    <span class="product-badge">Laser Pro</span>
    <div class="product-details">
        <h3 class="product-title">LaserJet Pro X1</h3>
        <p class="product-specs">
            <i class="fas fa-print"></i> Vitesse 45 ppm<br>
            <i class="fas fa-wifi"></i> Impression sans fil<br>
            <i class="fas fa-copy"></i> Impression recto-verso<br>
            <i class="fas fa-tint"></i> Laser couleur
        </p>
        <div class="product-price">299.995 FCFA</div>
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


<div class="product-card" data-price="199995">
    <img src="images/printer2.jpeg" alt="Imprimante Jet d'encre" class="product-image">
    <span class="product-badge">Photo Pro</span>
    <div class="product-details">
        <h3 class="product-title">PhotoJet Pro X2</h3>
        <p class="product-specs">
            <i class="fas fa-print"></i> Vitesse 30 ppm<br>
            <i class="fas fa-wifi"></i> WiFi Direct<br>
            <i class="fas fa-copy"></i> Qualité photo<br>
            <i class="fas fa-tint"></i> Encre 6 couleurs
        </p>
        <div class="product-price">199.995 FCFA</div>
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


<!-- Scanner Products -->
<div class="product-card" data-price="149995">
    <img src="images/scanner1.jpeg" alt="Scanner Professionnel" class="product-image">
    <span class="product-badge">Scanner Pro</span>
    <div class="product-details">
        <h3 class="product-title">ScanPro X1</h3>
        <p class="product-specs">
            <i class="fas fa-scanner"></i> 4800 DPI<br>
            <i class="fas fa-bolt"></i> 1 sec/page<br>
            <i class="fas fa-file"></i> Chargeur automatique de documents<br>
            <i class="fas fa-wifi"></i> Numérisation sans fil
        </p>
        <div class="product-price">149.995 FCFA</div>
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


<div class="product-card" data-price="99995">
    <img src="images/scanner2.jpeg" alt="Scanner Portable" class="product-image">
    <span class="product-badge">Portable</span>
    <div class="product-details">
        <h3 class="product-title">ScanPro X2 Portable</h3>
        <p class="product-specs">
            <i class="fas fa-scanner"></i> 2400 DPI<br>
            <i class="fas fa-bolt"></i> 3 sec/page<br>
            <i class="fas fa-battery-full"></i> Fonctionne sur batterie<br>
            <i class="fas fa-wifi"></i> WiFi Direct
        </p>
        <div class="product-price">99.995 FCFA</div>
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


<!-- Camera Products -->
<div class="product-card" data-price="399995">
    <img src="images/camera1.jpeg" alt="Appareil Photo Professionnel" class="product-image">
    <span class="product-badge">Pro Photo</span>
    <div class="product-details">
        <h3 class="product-title">CameraPro X1</h3>
        <p class="product-specs">
            <i class="fas fa-camera"></i> Capteur 45MP<br>
            <i class="fas fa-microchip"></i> Double EXPEED 7<br>
            <i class="fas fa-video"></i> Vidéo 4K/60fps<br>
            <i class="fas fa-battery-full"></i> 2000 clichés/charge
        </p>
        <div class="product-price">399.995 FCFA</div>
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

<div class="product-card" data-price="299995">
    <img src="images/camera2.jpeg" alt="Appareil Photo Hybride" class="product-image">
    <span class="product-badge">Hybride</span>
    <div class="product-details">
        <h3 class="product-title">CameraPro X2</h3>
        <p class="product-specs">
            <i class="fas fa-camera"></i> Capteur 32MP<br>
            <i class="fas fa-microchip"></i> Traitement IA<br>
            <i class="fas fa-video"></i> Vidéo 4K/30fps<br>
            <i class="fas fa-battery-full"></i> 1500 clichés/charge
        </p>
        <div class="product-price">299.995 FCFA</div>
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



<!-- Additional Monitor Products -->
<div class="product-card" data-price="349995">
    <img src="images/monitor3.jpeg" alt="Moniteur Professionnel" class="product-image">
    <span class="product-badge">Écran Pro</span>
    <div class="product-details">
        <h3 class="product-title">Monitor Pro X3</h3>
        <p class="product-specs">
            <i class="fas fa-desktop"></i> 32" IPS Pro<br>
            <i class="fas fa-bolt"></i> Taux de rafraîchissement 165Hz<br>
            <i class="fas fa-eye"></i> HDR 1000<br>
            <i class="fas fa-expand-arrows-alt"></i> 4K UHD
        </p>
        <div class="product-price">349.995 FCFA</div>
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


<!-- Additional Ultrabook Products -->
<div class="product-card" data-price="999995">
    <img src="images/Ultrabook4.jpeg" alt="Ultrabook Premium" class="product-image">
    <span class="product-badge">Premium</span>
    <div class="product-details">
        <h3 class="product-title">Ultrabook Elite X4</h3>
        <p class="product-specs">
            <i class="fas fa-microchip"></i> Intel i9 13e Gén<br>
            <i class="fas fa-memory"></i> 32 Go RAM DDR5<br>
            <i class="fas fa-hdd"></i> 2 To NVMe SSD<br>
            <i class="fas fa-battery-full"></i> Batterie 20h
        </p>
        <div class="product-price">999.995 FCFA</div>
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
            
            const notification = document.getElementById('cart-notification');
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 2000);
        }

        // New filtering functionality
        const productSearch = document.getElementById('product-search');
        const priceMax = document.getElementById('price-max');
        const sortButton = document.getElementById('sort-button');
        let sortAscending = true;

        function filterProducts() {
            const searchTerm = productSearch.value.toLowerCase();
            const maxPrice = parseInt(priceMax.value);
            const products = document.querySelectorAll('.product-card');

            products.forEach(product => {
                const title = product.querySelector('.product-title').textContent.toLowerCase();
                const price = parseInt(product.dataset.price);
                const shouldShow = title.includes(searchTerm) && price <= maxPrice;
                product.style.display = shouldShow ? 'block' : 'none';
            });
        }

        function sortProducts() {
            const productsGrid = document.querySelector('.products-grid');
            const products = Array.from(document.querySelectorAll('.product-card'));
            
            products.sort((a, b) => {const priceA = parseInt(a.dataset.price);
                const priceB = parseInt(b.dataset.price);
                
                return sortAscending ? priceA - priceB : priceB - priceA;
            });

            // Clear and re-append sorted products
            products.forEach(product => productsGrid.appendChild(product));
            
            // Update sort button icon
            sortButton.querySelector('i').className = sortAscending ? 
                'fas fa-sort-amount-up' : 'fas fa-sort-amount-down';
        }

        // Event listeners for filtering and sorting
        productSearch.addEventListener('input', filterProducts);
        priceMax.addEventListener('input', filterProducts);
        sortButton.addEventListener('click', () => {
            sortAscending = !sortAscending;
            sortProducts();
        });

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Initialize filters and sorting
        filterProducts();
        sortProducts();

        // Format currency function
        function formatFCFA(amount) {
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " FCFA";
        }

        // Update all price displays with formatted FCFA
        document.querySelectorAll('.product-price').forEach(priceElement => {
            const currentPrice = parseInt(priceElement.textContent.replace(/[^\d]/g, ''));
            priceElement.textContent = formatFCFA(currentPrice);
            
            const oldPriceElement = priceElement.querySelector('.old-price');
            if (oldPriceElement) {
                const oldPrice = parseInt(oldPriceElement.textContent.replace(/[^\d]/g, ''));
                oldPriceElement.textContent = formatFCFA(oldPrice);
            }
        });

        // Cart notification system
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Enhanced add to cart functionality
        function addToCart(productName, price) {
            cartCount++;
            document.querySelector('.cart-count').textContent = cartCount;
            
            // Show enhanced notification with product details
            showNotification(`${productName} ajouté au panier! - ${formatFCFA(price)}`);
            
            // Update cart total if displayed
            updateCartTotal(price);
        }

        // Cart total calculation
        let cartTotal = 0;
        function updateCartTotal(price) {
            cartTotal += price;
            const cartTotalElement = document.querySelector('.cart-total');
            if (cartTotalElement) {
                cartTotalElement.textContent = formatFCFA(cartTotal);
            }
        }

        // Add loading animation for images
        document.querySelectorAll('.product-image').forEach(img => {
            img.addEventListener('load', function() {
                this.classList.add('loaded');
            });
        });

        // Responsive menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
            });
        }

        // Initialize tooltips if any
        const tooltips = document.querySelectorAll('[data-tooltip]');
        tooltips.forEach(tooltip => {
            tooltip.addEventListener('mouseover', e => {
                const tip = document.createElement('div');
                tip.className = 'tooltip';
                tip.textContent = e.target.dataset.tooltip;
                document.body.appendChild(tip);
                
                const rect = e.target.getBoundingClientRect();
                tip.style.top = rect.bottom + 'px';
                tip.style.left = rect.left + 'px';
            });
            
            tooltip.addEventListener('mouseout', () => {
                const tip = document.querySelector('.tooltip');
                if (tip) tip.remove();
            });
        });
    </script>
</body>
</html>