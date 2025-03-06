<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'DatabaseConnection/db_config.php';

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Verify order belongs to the logged-in user
if ($orderId > 0) {
    $stmt = $conn->prepare("
        SELECT o.order_id, o.order_date, o.total_amount, o.payment_reference
        FROM orders o
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Order not found or doesn't belong to this user
        header("Location: account.php");
        exit();
    }
    
    $orderData = $result->fetch_assoc();
} else {
    // Invalid order ID
    header("Location: account.php");
    exit();
}

// Page title
$pageTitle = "Commande Confirmée | TechPro";

// Include header
include 'header.php';
?>

<!-- Link to external CSS -->
<link rel="stylesheet" href="Styles/success-styles.css">
<link rel="stylesheet" href="Styles/styles.css">

<main class="success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Commande Confirmée!</h1>
        
        <div class="order-details">
            <p class="order-number">Commande #<?php echo $orderId; ?></p>
            <p class="order-date">Date: <?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?></p>
            <p class="order-total">Total: <?php echo number_format($orderData['total_amount'], 2, ',', ' '); ?> €</p>
            <p class="payment-ref">Référence: <?php echo $orderData['payment_reference']; ?></p>
        </div>
        
        <p class="success-message">
            Merci pour votre achat! Votre commande a été traitée avec succès.
            Un email de confirmation a été envoyé à votre adresse email.
        </p>
        
        <div class="success-actions">
            <a href="account.php?section=orders" class="btn-view-orders">
                <i class="fas fa-list"></i> Voir mes commandes
            </a>
            <a href="produit.php" class="btn-continue-shopping">
                <i class="fas fa-store"></i> Continuer vos achats
            </a>
        </div>
    </div>
</main>

<style>
.success-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 2rem;
}

.success-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    max-width: 600px;
    width: 100%;
    text-align: center;
}

.success-icon {
    font-size: 5rem;
    color: #4CAF50;
    margin-bottom: 1.5rem;
}

.order-details {
    background-color: #f9f9f9;
    border-radius: 6px;
    padding: 1.5rem;
    margin: 1.5rem 0;
}

.order-number {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.success-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-view-orders, .btn-continue-shopping {
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-view-orders {
    background-color: #f0f0f0;
    color: #333;
}

.btn-continue-shopping {
    background-color: #2979ff;
    color: white;
}

@media (max-width: 768px) {
    .success-actions {
        flex-direction: column;
    }
}
</style>

<?php
// Include footer
include 'footer.php';
?>