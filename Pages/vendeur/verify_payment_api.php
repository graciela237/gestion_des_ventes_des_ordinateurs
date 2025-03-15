<?php
// Include database configuration
require_once('../DatabaseConnection/db_config.php');

// Start session
session_start();

// Check if user is logged in and has vendor role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) { // 3 is vendor role
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$vendeur_id = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid request'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Process payment verification
    if (isset($input['action']) && $input['action'] === 'verify_payment' && isset($input['cart_id'])) {
        $cart_id = intval($input['cart_id']);
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update cart payment status
            $update_query = "UPDATE cart SET 
                                payment_status = 'paid', 
                                payment_date = NOW(), 
                                payment_verified_by = ? 
                            WHERE cart_id = ? AND payment_status = 'pending'";
            
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ii", $vendeur_id, $cart_id);
            $result = mysqli_stmt_execute($stmt);
            
            if ($result && mysqli_affected_rows($conn) > 0) {
                // Get updated cart details for response
                $details_query = "SELECT c.cart_id, p.name as product_name, c.quantity, 
                                    (p.price * c.quantity) as total_amount,
                                    u.first_name, u.last_name
                                FROM cart c
                                JOIN products p ON c.product_id = p.product_id
                                JOIN users u ON c.user_id = u.user_id
                                WHERE c.cart_id = ?";
                                
                $stmt_details = mysqli_prepare($conn, $details_query);
                mysqli_stmt_bind_param($stmt_details, "i", $cart_id);
                mysqli_stmt_execute($stmt_details);
                $result_details = mysqli_stmt_get_result($stmt_details);
                $cart_details = mysqli_fetch_assoc($result_details);
                
                // Get updated stock quantity
                $stock_query = "SELECT p.stock_quantity 
                                FROM cart c
                                JOIN products p ON c.product_id = p.product_id
                                WHERE c.cart_id = ?";
                                
                $stmt_stock = mysqli_prepare($conn, $stock_query);
                mysqli_stmt_bind_param($stmt_stock, "i", $cart_id);
                mysqli_stmt_execute($stmt_stock);
                $result_stock = mysqli_stmt_get_result($stmt_stock);
                $stock_details = mysqli_fetch_assoc($result_stock);
                
                // Commit transaction
                mysqli_commit($conn);
                
                $response = [
                    'status' => 'success',
                    'message' => 'Paiement vérifié avec succès!',
                    'details' => [
                        'cart_id' => $cart_id,
                        'product' => $cart_details['product_name'],
                        'customer' => $cart_details['first_name'] . ' ' . $cart_details['last_name'],
                        'amount' => number_format($cart_details['total_amount'], 2),
                        'quantity' => $cart_details['quantity'],
                        'new_stock' => $stock_details['stock_quantity'],
                        'verification_date' => date('Y-m-d H:i:s')
                    ]
                ];
            } else {
                // Payment already verified or cart item doesn't exist
                mysqli_rollback($conn);
                $response = [
                    'status' => 'error',
                    'message' => 'La vérification du paiement a échoué. Cet article a peut-être déjà été vérifié ou n\'existe pas.'
                ];
            }
        } catch (Exception $e) {
            // Rollback in case of error
            mysqli_rollback($conn);
            $response = [
                'status' => 'error',
                'message' => 'Une erreur s\'est produite: ' . $e->getMessage()
            ];
            error_log("Payment verification error: " . $e->getMessage());
        }
    } 
    // Process getting payment details
    else if (isset($input['action']) && $input['action'] === 'get_payment_details' && isset($input['cart_id'])) {
        $cart_id = intval($input['cart_id']);
        
        $details_query = "SELECT c.cart_id, c.user_id, c.product_id, c.quantity, c.added_at,
                            u.first_name, u.last_name, u.email, u.phone_number,
                            p.name as product_name, p.price, p.stock_quantity, p.image_path,
                            (p.price * c.quantity) as total_amount
                        FROM cart c
                        JOIN users u ON c.user_id = u.user_id
                        JOIN products p ON c.product_id = p.product_id
                        WHERE c.cart_id = ? AND c.payment_status = 'pending'";
                        
        $stmt = mysqli_prepare($conn, $details_query);
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $details = mysqli_fetch_assoc($result);
            $response = [
                'status' => 'success',
                'details' => $details
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Payment details not found or already verified.'
            ];
        }
    }
    // Process search customers
    else if (isset($input['action']) && $input['action'] === 'search_customers' && isset($input['search_term'])) {
        $search_term = '%' . mysqli_real_escape_string($conn, $input['search_term']) . '%';
        
        $search_query = "SELECT c.cart_id, c.user_id, c.product_id, c.quantity, c.added_at,
                            u.first_name, u.last_name, u.email, u.phone_number,
                            p.name as product_name, p.price,
                            (p.price * c.quantity) as total_amount
                        FROM cart c
                        JOIN users u ON c.user_id = u.user_id
                        JOIN products p ON c.product_id = p.product_id
                        WHERE c.payment_status = 'pending'
                        AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? 
                            OR u.phone_number LIKE ? OR p.name LIKE ?)
                        ORDER BY c.added_at DESC";
                        
        $stmt = mysqli_prepare($conn, $search_query);
        mysqli_stmt_bind_param($stmt, "sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $search_results = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $search_results[] = $row;
            }
        }
        
        $response = [
            'status' => 'success',
            'results' => $search_results,
            'count' => count($search_results)
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>