<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=cart.php");
    exit();
}

// Include database connection
require_once 'DatabaseConnection/db_config.php';

// Function to get cart items (same as in cart.php)
function getCartItems($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT c.cart_id, c.quantity, c.product_id, p.name, p.price, p.image_path, 
               pc.category_name as category, p.specifications as specs, 
               (p.price * c.quantity) as subtotal, p.stock_quantity
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        JOIN product_categories pc ON p.category_id = pc.category_id
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    $totalAmount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
        $totalAmount += $row['subtotal'];
    }
    
    return ['items' => $items, 'total' => $totalAmount];
}

// Function to place an order
function placeOrder($conn, $userId, $cartItems, $totalAmount) {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert into orders table
        $orderQuery = "INSERT INTO orders (
            user_id, 
            total_amount,
            status,
            payment_method,
            payment_status
        ) VALUES (?, ?, 'pending', ?, 'pending')";
        
        $stmt = mysqli_prepare($conn, $orderQuery);
        
        // Default to WhatsApp payment method if action is whatsapp_order
        $paymentMethod = isset($_POST['action']) && $_POST['action'] === 'whatsapp_order' ? 'WhatsApp' : 'Online Payment';
        
        mysqli_stmt_bind_param(
            $stmt, 
            "ids", 
            $userId, 
            $totalAmount,
            $paymentMethod
        );
        
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_insert_id($conn);
        
        // Insert order items
        $itemQuery = "INSERT INTO order_items (
            order_id,
            product_id,
            quantity,
            price_at_time
        ) VALUES (?, ?, ?, ?)";
        
        $stmtItems = mysqli_prepare($conn, $itemQuery);
        
        foreach ($cartItems as $item) {
            mysqli_stmt_bind_param(
                $stmtItems,
                "iiid",
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            mysqli_stmt_execute($stmtItems);
            
            // Update product stock quantity
            $updateStockQuery = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
            $stmtStock = mysqli_prepare($conn, $updateStockQuery);
            mysqli_stmt_bind_param($stmtStock, "ii", $item['quantity'], $item['product_id']);
            mysqli_stmt_execute($stmtStock);
        }
        
        // Clear the cart after successful order placement
        $clearCartQuery = "DELETE FROM cart WHERE user_id = ?";
        $stmtClearCart = mysqli_prepare($conn, $clearCartQuery);
        mysqli_stmt_bind_param($stmtClearCart, "i", $userId);
        mysqli_stmt_execute($stmtClearCart);
        
        // Commit transaction
        mysqli_commit($conn);
        
        return [
            'status' => 'success',
            'order_id' => $orderId,
            'message' => 'Order placed successfully!'
        ];
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        return [
            'status' => 'error',
            'message' => 'Failed to place order: ' . $e->getMessage()
        ];
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Get cart data
    $cartData = getCartItems($conn, $_SESSION['user_id']);
    $cartItems = $cartData['items'];
    $totalAmount = $cartData['total'];
    
    switch ($action) {
        case 'initiate_payment':
            // Place the order first
            $orderResult = placeOrder($conn, $_SESSION['user_id'], $cartItems, $totalAmount);
            
            if ($orderResult['status'] === 'success') {
                // Here you would normally integrate with a payment gateway like Flutterwave
                // For this example, we'll just return a success message
                echo json_encode([
                    'success' => true,
                    'order_id' => $orderResult['order_id'],
                    'payment_link' => 'order_success.php?order_id=' . $orderResult['order_id'],
                    'message' => 'Order placed successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $orderResult['message']
                ]);
            }
            break;
            
        case 'whatsapp_order':
            // Place the order
            $orderResult = placeOrder($conn, $_SESSION['user_id'], $cartItems, $totalAmount);
            
            if ($orderResult['status'] === 'success') {
                echo json_encode([
                    'success' => true,
                    'order_id' => $orderResult['order_id'],
                    'message' => 'Order placed successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => $orderResult['message']
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
    
    exit();
}
?>