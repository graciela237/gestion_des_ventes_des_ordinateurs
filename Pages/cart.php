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

// Page title
$pageTitle = "Votre Panier | TechPro";

// Include header
include 'header.php';

// Function to get cart items
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

// Function to get user details
function getUserDetails($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT first_name, last_name, email, phone_number, country, state, quarter
        FROM users
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    
    return null;
}

// Get cart data
$cartData = getCartItems($conn, $_SESSION['user_id']);
$cartItems = $cartData['items'];
$totalAmount = $cartData['total'];

// Get user data
$userData = getUserDetails($conn, $_SESSION['user_id']);

// WhatsApp number
$whatsappNumber = "+237694048635"; // WhatsApp number provided
?>

<!-- Link to external CSS -->
<link rel="stylesheet" href="Styles/cart-styles.css">
<link rel="stylesheet" href="Styles/styles.css">
<main class="cart-container">
    <h1><i class="fas fa-shopping-cart"></i> Votre Panier</h1>
    
    <?php if (empty($cartItems)): ?>
    <div class="empty-cart">
        <img src="Images/empty-cart.svg" alt="Panier vide" class="empty-cart-image">
        <h2>Votre panier est vide</h2>
        <p>Découvrez nos produits et ajoutez des articles à votre panier</p>
        <a href="produit.php" class="btn-continue-shopping">
            <i class="fas fa-store"></i> Voir les produits
        </a>
    </div>
    <?php else: ?>
    
    <div class="cart-content">
        <div class="cart-items">
            <?php foreach ($cartItems as $item): ?>
          <!-- Inside the foreach loop for cart items -->
<div class="cart-item <?= (strpos(strtolower($item['category']), 'ordinateur') !== false) ? 'tech-product' : '' ?>" id="item-<?= $item['cart_id'] ?>">
    <div class="item-image">
        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
    </div>
    <div class="item-details">
        <h3><?= htmlspecialchars($item['name']) ?></h3>
        <p class="item-price"><?= number_format($item['price'], 2, ',', ' ') ?> CFA</p>
        <?php if (!empty($item['specs']) && strpos(strtolower($item['category']), 'ordinateur') !== false): ?>
        <div class="tech-specs">
            <?php 
            $specs = explode(',', $item['specs']);
            foreach ($specs as $spec): 
                if (trim($spec) !== ''): 
            ?>
                <span><?= htmlspecialchars(trim($spec)) ?></span>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>
        <!-- Add available quantity display -->
        <p class="available-qty">Disponible: <?= $item['stock_quantity'] ?? 'N/A' ?></p>
    </div>
    <div class="item-quantity">
        <button class="qty-btn qty-decrease" data-cart-id="<?= $item['cart_id'] ?>" data-action="decrease">-</button>
        <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= min(10, $item['stock_quantity'] ?? 10) ?>" readonly>
        <button class="qty-btn qty-increase" data-cart-id="<?= $item['cart_id'] ?>" data-action="increase">+</button>
    </div>
    <div class="item-subtotal">
        <p><?= number_format($item['subtotal'], 2, ',', ' ') ?> CFA</p>
    </div>
    <div class="item-actions">
        <button class="remove-item" data-cart-id="<?= $item['cart_id'] ?>">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
</div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <h2>Récapitulatif</h2>
            <div class="summary-row">
                <span>Sous-total:</span>
                <span id="subtotal"><?= number_format($totalAmount, 2, ',', ' ') ?> CFA</span>
            </div>
            <div class="summary-row">
                <span>Frais de livraison:</span>
                <span>Gratuit</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="total"><?= number_format($totalAmount, 2, ',', ' ') ?> CFA</span>
            </div>
            <button id="checkout-btn" class="btn-checkout">
                <i class="fas fa-check-circle"></i> Passer la commande
            </button>
            <button id="whatsapp-order-btn" class="btn-whatsapp-order">
                <i class="fab fa-whatsapp"></i> Commander par WhatsApp
            </button>
            <a href="produit.php" class="btn-continue-shopping">
                <i class="fas fa-arrow-left"></i> Continuer vos achats
            </a>
        </div>
    </div>
    <?php endif; ?>
</main>

<!-- Confirmation Modal -->
<div id="confirmation-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2><i class="fas fa-check-circle"></i> Confirmer votre commande</h2>
        <p>Êtes-vous sûr de vouloir passer la commande pour <span id="order-total"><?= number_format($totalAmount, 2, ',', ' ') ?> CFA</span>?</p>
        <div class="modal-actions">
            <button id="confirm-order" class="btn-confirm">Confirmer</button>
            <button id="cancel-order" class="btn-cancel">Annuler</button>
        </div>
    </div>
</div>

<!-- WhatsApp Order Modal -->
<div id="whatsapp-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2><i class="fab fa-whatsapp"></i> Commander par WhatsApp</h2>
        <div class="user-info-preview">
            <h3>Vos informations</h3>
            <div class="info-row">
                <span>Nom:</span>
                <span><?= htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) ?></span>
            </div>
            <div class="info-row">
                <span>Email:</span>
                <span><?= htmlspecialchars($userData['email']) ?></span>
            </div>
            <div class="info-row">
                <span>Téléphone:</span>
                <span><?= htmlspecialchars($userData['phone_number'] ?? 'Non spécifié') ?></span>
            </div>
            <div class="info-row">
                <span>Adresse:</span>
                <span>
                    <?= htmlspecialchars(
                        ($userData['quarter'] ?? '') . 
                        ((!empty($userData['quarter']) && !empty($userData['state'])) ? ', ' : '') .
                        ($userData['state'] ?? '') .
                        ((!empty($userData['state']) && !empty($userData['country'])) ? ', ' : '') .
                        ($userData['country'] ?? '')
                    ) ?>
                </span>
            </div>
        </div>
        <div class="cart-summary-preview">
            <h3>Résumé de la commande</h3>
            <div class="order-items-preview">
                <?php foreach ($cartItems as $item): ?>
                <div class="order-item-row">
                    <span><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
                    <span><?= number_format($item['subtotal'], 2, ',', ' ') ?> CFA</span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="order-total-preview">
                <span>Total:</span>
                <span><?= number_format($totalAmount, 2, ',', ' ') ?> CFA</span>
            </div>
        </div>
        <p class="whatsapp-info">Votre commande sera envoyée par WhatsApp au <?= chunk_split($whatsappNumber, 3, ' ') ?></p>
        <div class="modal-actions">
            <button id="send-whatsapp-order" class="btn-whatsapp-confirm">
                <i class="fab fa-whatsapp"></i> Envoyer la commande
            </button>
            <button id="cancel-whatsapp-order" class="btn-cancel">Annuler</button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="modal">
    <div class="modal-content success">
        <span class="close">&times;</span>
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Commande Réussie!</h2>
        <p>Votre commande a été confirmée. Vous recevrez un email avec les détails de votre commande.</p>
        <p>Numéro de commande: <span id="order-number"></span></p>
        <button id="continue-shopping" class="btn-continue">Continuer vos achats</button>
    </div>
</div>

<!-- Error Message -->
<div id="error-message"></div>

<!-- Add JavaScript for cart functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const cartItems = document.querySelector('.cart-items');
    const checkoutBtn = document.getElementById('checkout-btn');
    const whatsappOrderBtn = document.getElementById('whatsapp-order-btn');
    const confirmationModal = document.getElementById('confirmation-modal');
    const whatsappModal = document.getElementById('whatsapp-modal');
    const successModal = document.getElementById('success-modal');
    const confirmOrderBtn = document.getElementById('confirm-order');
    const sendWhatsappOrderBtn = document.getElementById('send-whatsapp-order');
    const cancelOrderBtn = document.getElementById('cancel-order');
    const cancelWhatsappOrderBtn = document.getElementById('cancel-whatsapp-order');
    const continueShoppingBtn = document.getElementById('continue-shopping');
    const closeButtons = document.querySelectorAll('.close');
    const errorMessage = document.getElementById('error-message');
    
    // WhatsApp data preparation
    const userData = {
        name: "<?= addslashes($userData['first_name'] . ' ' . $userData['last_name']) ?>",
        email: "<?= addslashes($userData['email']) ?>",
        phone: "<?= addslashes($userData['phone_number'] ?? 'Non spécifié') ?>",
        address: "<?= addslashes(
            ($userData['quarter'] ?? '') . 
            ((!empty($userData['quarter']) && !empty($userData['state'])) ? ', ' : '') .
            ($userData['state'] ?? '') . 
            ((!empty($userData['state']) && !empty($userData['country'])) ? ', ' : '') .
            ($userData['country'] ?? '')
        ) ?>"
    };
    
    const cartSummary = [];
    <?php foreach ($cartItems as $item): ?>
    cartSummary.push({
        name: "<?= addslashes($item['name']) ?>",
        quantity: <?= $item['quantity'] ?>,
        price: "<?= number_format($item['price'], 2, ',', ' ') ?> CFA",
        subtotal: "<?= number_format($item['subtotal'], 2, ',', ' ') ?> CFA"
    });
    <?php endforeach; ?>
    
    const totalAmount = "<?= number_format($totalAmount, 2, ',', ' ') ?> CFA";
    const whatsappNumber = "<?= $whatsappNumber ?>";
    
    // Quantity change handlers
    if (cartItems) {
        cartItems.addEventListener('click', function(e) {
            const target = e.target;
            
            // Handle remove button
            if (target.classList.contains('remove-item') || target.parentElement.classList.contains('remove-item')) {
                const button = target.classList.contains('remove-item') ? target : target.parentElement;
                const cartId = button.dataset.cartId;
                removeCartItem(cartId);
            }
            
            // Handle quantity buttons
            if (target.classList.contains('qty-btn')) {
                const cartId = target.dataset.cartId;
                const action = target.dataset.action;
                updateQuantity(cartId, action);
            }
        });
    }
    
    // Checkout flow
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            showModal(confirmationModal);
        });
    }
    
    // WhatsApp order flow
    if (whatsappOrderBtn) {
        whatsappOrderBtn.addEventListener('click', function() {
            showModal(whatsappModal);
        });
    }
    
    if (confirmOrderBtn) {
        confirmOrderBtn.addEventListener('click', initiatePayment);
    }
    
    if (sendWhatsappOrderBtn) {
        sendWhatsappOrderBtn.addEventListener('click', sendWhatsAppOrder);
    }
    
    if (cancelOrderBtn) {
        cancelOrderBtn.addEventListener('click', function() {
            hideModal(confirmationModal);
        });
    }
    
    if (cancelWhatsappOrderBtn) {
        cancelWhatsappOrderBtn.addEventListener('click', function() {
            hideModal(whatsappModal);
        });
    }
    
    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', function() {
            hideModal(successModal);
            window.location.href = 'produit.php';
        });
    }
    
    // Close modal buttons
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            hideModal(modal);
        });
    });
    
    // Remove item from cart
    function removeCartItem(cartId) {
        fetch('cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const itemElement = document.getElementById(`item-${cartId}`);
                if (itemElement) {
                    itemElement.remove();
                }
                
                // Update totals
                document.getElementById('subtotal').textContent = data.subtotal;
                document.getElementById('total').textContent = data.total;
                document.getElementById('order-total').textContent = data.total;
                
                // Update cart count in header
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cartCount;
                }
                
                // Check if cart is empty
                if (data.cartCount === 0) {
                    window.location.reload();
                }
            } else {
                showError(data.message || 'Une erreur est survenue');
            }
        })
        .catch(error => {
            showError('Problème de connexion au serveur');
            console.error('Error:', error);
        });
    }
    
    // Update item quantity
    function updateQuantity(cartId, action) {
        fetch('cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_quantity&cart_id=${cartId}&update_action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // If quantity becomes 0, remove the item
                if (data.newQuantity <= 0) {
                    removeCartItem(cartId);
                    return;
                }
                
                const itemElement = document.getElementById(`item-${cartId}`);
                if (itemElement) {
                    // Update quantity input
                    const qtyInput = itemElement.querySelector('.qty-input');
                    qtyInput.value = data.newQuantity;
                    
                    // Update subtotal for this item
                    const subtotalElement = itemElement.querySelector('.item-subtotal p');
                    subtotalElement.textContent = data.itemSubtotal;
                    
                    // Update available quantity display if it exists
                    const availableQty = itemElement.querySelector('.available-qty');
                    if (availableQty && data.availableQuantity !== undefined) {
                        availableQty.textContent = `Disponible: ${data.availableQuantity}`;
                    }
                }
                
                // Update totals
                document.getElementById('subtotal').textContent = data.subtotal;
                document.getElementById('total').textContent = data.total;
                document.getElementById('order-total').textContent = data.total;
                
                // Update cart count in header
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cartCount;
                }
            } else {
                showError(data.message || 'Une erreur est survenue');
            }
        })
        .catch(error => {
            showError('Problème de connexion au serveur');
            console.error('Error:', error);
        });
    }
    
    // Send order via WhatsApp
    function sendWhatsAppOrder() {
        // Format cart items for WhatsApp message
        let itemsList = "";
        cartSummary.forEach(item => {
            itemsList += `- ${item.name} x${item.quantity} = ${item.subtotal}\n`;
        });
        
        // Create WhatsApp message
        const message = encodeURIComponent(
            `*NOUVELLE COMMANDE TECHPRO*\n\n` +
            `*Informations client:*\n` +
            `Nom: ${userData.name}\n` +
            `Email: ${userData.email}\n` +
            `Téléphone: ${userData.phone}\n` +
            `Adresse: ${userData.address}\n\n` +
            `*Articles commandés:*\n${itemsList}\n` +
            `*TOTAL: ${totalAmount}*\n\n` +
            `Merci pour votre commande!`
        );
        
        // Open WhatsApp with the message
        window.open(`https://wa.me/${whatsappNumber}?text=${message}`, '_blank');
        
        // Close modal
        hideModal(whatsappModal);
    }
    
    // Initiate payment with Flutterwave
    function initiatePayment() {
        fetch('order_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=initiate_payment'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to Flutterwave payment page
                window.location.href = data.payment_link;
            } else {
                hideModal(confirmationModal);
                showError(data.message || 'Une erreur est survenue lors de l\'initialisation du paiement');
            }
        })
        .catch(error => {
            hideModal(confirmationModal);
            showError('Problème de connexion au serveur');
            console.error('Error:', error);
        });
    }
    
    // Helper functions
    function showModal(modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function hideModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 5000);
    }
});
</script>




<?php
// Include footer
include 'footer.php';
?>