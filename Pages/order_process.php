<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter pour finaliser votre commande.']);
    exit();
}

// Include database connection and Flutterwave API
require_once 'DatabaseConnection/db_config.php';
require_once 'flutterwave_config.php';

// Process order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    
    // Initiate payment - We'll modify this part to first verify cart, then initiate payment, and only create order on callback
    if ($action === 'initiate_payment') {
        try {
            // Get cart items and total
            $cartData = getCartData($conn, $userId);
            
            if (empty($cartData['items'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Votre panier est vide.'
                ]);
                exit();
            }
            
            // Check stock availability
            $stockIssue = false;
            foreach ($cartData['items'] as $item) {
                if ($item['quantity'] > $item['stock_quantity']) {
                    $stockIssue = true;
                    break;
                }
            }
            
            if ($stockIssue) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Certains produits ne sont pas disponibles en quantité suffisante.'
                ]);
                exit();
            }
            
            // Get user data
            // Get user data
$stmt = $conn->prepare("SELECT email, first_name, last_name, phone_number FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userRow = $result->fetch_assoc();
            
            // Create temporary order ID (will be confirmed after payment)
            $tempOrderId = 'TEMP_' . time() . '_' . $userId;
            
            // Store cart data in session for later use
            $_SESSION['pending_order'] = [
                'temp_id' => $tempOrderId,
                'cart_data' => $cartData,
                'timestamp' => time()
            ];
            
            // Prepare data for payment
            $userData = [
                'user_id' => $userId,
                'email' => $userRow['email'],
                'first_name' => $userRow['first_name'],
                'last_name' => $userRow['last_name'],
                'phone_number' => $userRow['phone_number']
            ];
            
            $orderData = [
                'orderId' => $tempOrderId,
                'amount' => $cartData['total']
            ];
            
            // Initialize payment
            $paymentResult = initializeFlutterwavePayment($userData, $orderData);
            
            if ($paymentResult['success']) {
                // Store payment reference in session
                $_SESSION['pending_order']['tx_ref'] = $paymentResult['tx_ref'];
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Paiement initié avec succès.',
                    'temp_order_id' => $tempOrderId,
                    'payment_link' => $paymentResult['payment_link']
                ]);
                exit();
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Échec de l\'initialisation du paiement: ' . $paymentResult['message']
                ]);
                exit();
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ]);
            exit();
        }
    }
}

// Function to get cart data
function getCartData($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price, p.stock_quantity
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    $totalAmount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
        $totalAmount += ($row['price'] * $row['quantity']);
    }
    
    return [
        'items' => $items,
        'total' => $totalAmount
    ];
}

// Function to create an order - Will be called from payment_callback.php after successful payment
function createOrder($conn, $userId, $cartData, $paymentReference) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $totalAmount = $cartData['total'];
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, order_date, total_amount, status, payment_reference) 
            VALUES (?, NOW(), ?, 'paid', ?)
        ");
        $stmt->bind_param("ids", $userId, $totalAmount, $paymentReference);
        $stmt->execute();
        
        $orderId = $conn->insert_id;
        
        if ($orderId) {
            // Add order items
            foreach ($cartData['items'] as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $unitPrice = $item['price'];
                $subtotal = $unitPrice * $quantity;
                
                // Fix the column name to match your database (unit_price instead of price)
                $stmt = $conn->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iidd", $orderId, $productId, $quantity, $unitPrice, $subtotal);
                $stmt->execute();
                
                // Update product stock
                $stmt = $conn->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ?, 
                        items_sold = items_sold + ? 
                    WHERE product_id = ?
                ");
                $stmt->bind_param("iii", $quantity, $quantity, $productId);
                $stmt->execute();
            }
            
            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            return $orderId;
        } else {
            // Rollback if order creation fails
            $conn->rollback();
            return false;
        }
    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        throw $e;
    }
}

// Default response for invalid requests
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Action non valide.']);
exit();