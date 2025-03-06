<?php
session_start();
require_once 'DatabaseConnection/db_config.php';

// Set response headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, return error message
    echo json_encode([
        'status' => 'error',
        'message' => 'Veuillez vous connecter pour ajouter des produits au panier.',
        'redirect' => 'login.php',
        'require_login' => true
    ]);
    exit;
}

// If user is logged in, proceed with adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $userId = $_SESSION['user_id'];
    
    try {
        // Check if product exists and has stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Produit non trouvé.'
            ]);
            exit;
        }
        
        // Check if product already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        $cartItem = $stmt->fetch();
        
        if ($cartItem) {
            // Update existing cart item
            $newQuantity = $cartItem['quantity'] + 1;
            
            // Check if enough stock is available
            if ($product['stock_quantity'] < $newQuantity) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Stock insuffisant. Seulement ' . $product['stock_quantity'] . ' disponible.'
                ]);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $success = $stmt->execute([$newQuantity, $userId, $productId]);
        } else {
            // Add new cart item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $success = $stmt->execute([$userId, $productId]);
        }
        
        if ($success) {
            // Get updated cart count from database
            $stmt = $pdo->prepare("SELECT SUM(quantity) as cart_count FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            $cartCount = $result['cart_count'] ?: 0;
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Produit ajouté au panier!',
                'cart_total' => $cartCount
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Échec de l\'ajout au panier.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Une erreur est survenue: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Requête invalide.'
    ]);
}
?>