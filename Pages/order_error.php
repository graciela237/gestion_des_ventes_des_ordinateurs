<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Page title
$pageTitle = "Erreur de Commande | TechPro";

// Get error message from URL
$errorType = isset($_GET['error']) ? $_GET['error'] : 'unknown';
$errorMessage = isset($_GET['message']) ? urldecode($_GET['message']) : 'Une erreur inconnue est survenue';

// Include header
include 'header.php';
?>

<!-- Link to external CSS -->
<link rel="stylesheet" href="Styles/error-styles.css">
<link rel="stylesheet" href="Styles/styles.css">

<main class="error-container">
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        
        <h1>Erreur de Commande</h1>
        
        <div class="error-message">
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
            
            <?php if ($errorType === 'payment_verification'): ?>
            <p>Votre paiement n'a pas pu être vérifié. Aucun montant n'a été débité de votre compte.</p>
            <?php endif; ?>
        </div>
        
        <div class="error-actions">
            <a href="cart.php" class="btn-return-cart">
                <i class="fas fa-shopping-cart"></i> Retourner au panier
            </a>
            <a href="contact.php" class="btn-contact-support">
                <i class="fas fa-headset"></i> Contacter le support
            </a>
        </div>
    </div>
</main>

<style>
.error-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 2rem;
}

.error-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    max-width: 600px;
    width: 100%;
    text-align: center;
}

.error-icon {
    font-size: 5rem;
    color: #f44336;
    margin-bottom: 1.5rem;
}

.error-message {
    background-color: #fff8f8;
    border-left: 4px solid #f44336;
    border-radius: 4px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    text-align: left;
}

.error-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-return-cart, .btn-contact-support {
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-return-cart {
    background-color: #2979ff;
    color: white;
}

.btn-contact-support {
    background-color: #f0f0f0;
    color: #333;
}

@media (max-width: 768px) {
    .error-actions {
        flex-direction: column;
    }
}
</style>

<?php
// Include footer
include 'footer.php';
?>