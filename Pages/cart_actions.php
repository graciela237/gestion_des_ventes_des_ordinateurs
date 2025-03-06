<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter pour gérer votre panier.']);
    exit();
}

// Include database connection
require_once 'DatabaseConnection/db_config.php';

// Function to get cart count
function getCartCount($conn, $userId) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

// Function to get total amount
function getCartTotal($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT SUM(p.price * c.quantity) as total
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to format price
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

// Process cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    
    // Remove item from cart
    if ($action === 'remove' && isset($_POST['cart_id'])) {
        $cartId = (int)$_POST['cart_id'];
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $cartCount = getCartCount($conn, $userId);
            $total = getCartTotal($conn, $userId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Article supprimé du panier.',
                'cartCount' => $cartCount,
                'subtotal' => formatPrice($total),
                'total' => formatPrice($total)
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Impossible de supprimer l\'article.']);
            exit();
        }
    }
    
    // Update quantity
    if ($action === 'update_quantity' && isset($_POST['cart_id']) && isset($_POST['update_action'])) {
        $cartId = (int)$_POST['cart_id'];
        $updateAction = $_POST['update_action'];
        
        // Get current cart item
        $stmt = $conn->prepare("
            SELECT c.quantity, c.product_id, p.stock_quantity 
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.cart_id = ? AND c.user_id = ?
        ");
        $stmt->bind_param("ii", $cartId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $currentQuantity = $row['quantity'];
            $productId = $row['product_id'];
            $stockQuantity = $row['stock_quantity'];
            $newQuantity = $currentQuantity;
            
            if ($updateAction === 'increase') {
                // Check if there's enough stock
                if ($currentQuantity < $stockQuantity && $currentQuantity < 10) {
                    $newQuantity = $currentQuantity + 1;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Quantité maximale atteinte ou stock insuffisant.'
                    ]);
                    exit();
                }
            } else if ($updateAction === 'decrease') {
                $newQuantity = max(0, $currentQuantity - 1);
            }
            
            // If quantity is 0, we'll handle removal in JavaScript
            if ($newQuantity > 0) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                $stmt->bind_param("iii", $newQuantity, $cartId, $userId);
                $stmt->execute();
            }
            
            // Get item subtotal
            $stmt = $conn->prepare("
                SELECT (p.price * ?) as item_subtotal, p.price 
                FROM products p WHERE product_id = ?
            ");
            $stmt->bind_param("ii", $newQuantity, $productId);
            $stmt->execute();
            $itemResult = $stmt->get_result();
            $itemRow = $itemResult->fetch_assoc();
            $itemSubtotal = $itemRow['item_subtotal'];
            
            // Get updated cart totals
            $cartCount = getCartCount($conn, $userId);
            $total = getCartTotal($conn, $userId);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'newQuantity' => $newQuantity,
                'itemSubtotal' => formatPrice($itemSubtotal),
                'subtotal' => formatPrice($total),
                'total' => formatPrice($total),
                'cartCount' => $cartCount,
                'availableQuantity' => $stockQuantity
            ]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Article non trouvé.']);
            exit();
        }
    }
}

// Default response for invalid requests
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Action non valide.']);
exit();