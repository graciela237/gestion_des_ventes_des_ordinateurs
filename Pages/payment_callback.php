<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and Flutterwave API
require_once 'DatabaseConnection/db_config.php';
require_once 'flutterwave_config.php';
require_once 'order_process.php';

// Handle Flutterwave callback
$callbackResult = handleFlutterwaveCallback();

// Get pending order from session
$pendingOrder = $_SESSION['pending_order'] ?? null;

if ($callbackResult['success'] && $pendingOrder) {
    try {
        // Create the actual order now that payment is confirmed
        $userId = $_SESSION['user_id'];
        $cartData = $pendingOrder['cart_data'];
        $paymentReference = $pendingOrder['tx_ref'];
        
        // Create order in database
        $orderId = createOrder($conn, $userId, $cartData, $paymentReference);
        
        if ($orderId) {
            // Clear pending order from session
            unset($_SESSION['pending_order']);
            
            // Redirect to success page
            header("Location: order_success.php?order_id=" . $orderId);
            exit();
        } else {
            // Order creation failed
            header("Location: order_error.php?error=order_creation_failed");
            exit();
        }
    } catch (Exception $e) {
        // Log error
        error_log("Payment callback error: " . $e->getMessage());
        header("Location: order_error.php?error=exception&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Payment verification failed
    $errorMessage = $callbackResult['message'] ?? 'Payment verification failed';
    header("Location: order_error.php?error=payment_verification&message=" . urlencode($errorMessage));
    exit();
}