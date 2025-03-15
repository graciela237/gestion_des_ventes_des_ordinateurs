<?php
// Inclure la configuration de la base de données
require_once('../DatabaseConnection/db_config.php');

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté et a le rôle de vendeur
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) { // 3 est le rôle vendeur
    header('Location: login.php');
    exit();
}

$vendeur_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Traiter la soumission du formulaire de vérification de paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    
    // Commencer la transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Mettre à jour le statut de paiement du panier
        $update_query = "UPDATE cart SET 
                            payment_status = 'paid', 
                            payment_date = NOW(), 
                            payment_verified_by = ? 
                        WHERE cart_id = ? AND payment_status = 'pending'";
        
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $vendeur_id, $cart_id);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result && mysqli_affected_rows($conn) > 0) {
            // Après la vérification réussie du paiement, supprimer l'article du panier
            $delete_query = "DELETE FROM cart WHERE cart_id = ? AND payment_status = 'paid'";
            $delete_stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($delete_stmt, "i", $cart_id);
            mysqli_stmt_execute($delete_stmt);
            
            // Valider la transaction
            mysqli_commit($conn);
            $success_message = "Paiement vérifié avec succès ! Le stock et l'inventaire ont été mis à jour automatiquement.";
        } else {
            // Paiement déjà vérifié ou l'article du panier n'existe pas
            mysqli_rollback($conn);
            $error_message = "La vérification du paiement a échoué. Cet article a peut-être déjà été vérifié ou n'existe pas.";
        }
    } catch (Exception $e) {
        // Annuler en cas d'erreur
        mysqli_rollback($conn);
        $error_message = "Une erreur s'est produite : " . $e->getMessage();
        error_log("Erreur de vérification de paiement : " . $e->getMessage());
    }
}

// Obtenir tous les articles du panier en attente avec les détails du client
$pending_query = "SELECT c.cart_id, c.user_id, c.product_id, c.quantity, c.added_at,
                    u.first_name, u.last_name, u.email, u.phone_number,
                    p.name as product_name, p.price, p.stock_quantity, p.image_path,
                    (p.price * c.quantity) as total_amount
                FROM cart c
                JOIN users u ON c.user_id = u.user_id
                JOIN products p ON c.product_id = p.product_id
                WHERE c.payment_status = 'pending'
                ORDER BY c.added_at DESC";

$pending_result = mysqli_query($conn, $pending_query);
$pending_items = [];

if ($pending_result) {
    while ($row = mysqli_fetch_assoc($pending_result)) {
        $pending_items[] = $row;
    }
}

// Obtenir les paiements récemment vérifiés (les 20 derniers)
$recent_query = "SELECT c.cart_id, c.user_id, c.product_id, c.quantity, c.payment_date,
                    u.first_name, u.last_name, u.email,
                    p.name as product_name,
                    (p.price * c.quantity) as total_amount,
                    v.first_name as verified_by_first, v.last_name as verified_by_last
                FROM cart c
                JOIN users u ON c.user_id = u.user_id
                JOIN products p ON c.product_id = p.product_id
                JOIN users v ON c.payment_verified_by = v.user_id
                WHERE c.payment_status = 'paid'
                ORDER BY c.payment_date DESC
                LIMIT 20";

$recent_result = mysqli_query($conn, $recent_query);
$recent_payments = [];

if ($recent_result) {
    while ($row = mysqli_fetch_assoc($recent_result)) {
        $recent_payments[] = $row;
    }
}

// Obtenir les statistiques des ventes
$stats_query = "SELECT 
                COUNT(CASE WHEN c.payment_status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN c.payment_status = 'paid' THEN 1 END) as verified_count,
                SUM(CASE WHEN c.payment_status = 'paid' THEN p.price * c.quantity ELSE 0 END) as total_sales
                FROM cart c
                LEFT JOIN products p ON c.product_id = p.product_id";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification des Paiements - Espace Vendeur</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Personnalisé -->
    <style>
        .card-header {
            background-color: #3498db;
            color: white;
        }
        .badge-pending {
            background-color: #f39c12;
        }
        .badge-verified {
            background-color: #2ecc71;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .stats-card {
            transition: all 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-cash-register me-2"></i> Vérification des Paiements</h2>
            <p class="text-muted">Vérifiez les paiements des clients et gérez les articles du panier</p>
        </div>
    </div>
    
    <!-- Alertes pour les messages de succès/erreur -->
    <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Paiements en Attente</h5>
                    <p class="display-4 mb-0 text-warning"><?php echo number_format($stats['pending_count']); ?></p>
                    <small class="text-muted">En attente de vérification</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Paiements Vérifiés</h5>
                    <p class="display-4 mb-0 text-success"><?php echo number_format($stats['verified_count']); ?></p>
                    <small class="text-muted">Traités avec succès</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Ventes Totales</h5>
                    <p class="display-4 mb-0 text-primary">
                        <?php echo number_format($stats['total_sales'], 2); ?> FCFA
                    </p>
                    <small class="text-muted">Des paiements vérifiés</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Onglet des paiements en attente -->
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Paiements en Attente</h5>
                    <span class="badge badge-pending rounded-pill"><?php echo count($pending_items); ?> articles</span>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_items)): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Aucun paiement en attente à vérifier pour le moment.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Image</th>
                                        <th scope="col">Produit</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Quantité</th>
                                        <th scope="col">Montant</th>
                                        <th scope="col">Date d'ajout</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['cart_id']; ?></td>
                                        <td>
                                            <img src="<?php echo $item['image_path']; ?>" class="product-image rounded" alt="Image du Produit">
                                        </td>
                                        <td>
                                            <strong><?php echo $item['product_name']; ?></strong>
                                            <div class="small text-muted">ID: <?php echo $item['product_id']; ?></div>
                                        </td>
                                        <td>
                                            <?php echo $item['first_name'] . ' ' . $item['last_name']; ?>
                                            <div class="small text-muted"><?php echo $item['email']; ?></div>
                                            <div class="small text-muted"><?php echo $item['phone_number']; ?></div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['total_amount'], 2); ?> FCFA</td>
                                        <td><?php echo date('d M Y H:i', strtotime($item['added_at'])); ?></td>
                                        <td>
                                            <form name="verify_payment_form" method="post" onsubmit="return false;">
    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
    <button type="submit" name="verify_payment" class="btn btn-success btn-sm">
        <i class="fas fa-check me-1"></i> Vérifier le Paiement
    </button>
</form> 
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Onglet des paiements récemment vérifiés -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i> Paiements Récemment Vérifiés</h5>
                    <span class="badge badge-verified rounded-pill"><?php echo count($recent_payments); ?> articles</span>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_payments)): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Aucun paiement vérifié pour le moment.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Produit</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Montant</th>
                                        <th scope="col">Date de Paiement</th>
                                        <th scope="col">Vérifié Par</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_payments as $payment): ?>
                                    <tr>
                                        <td><?php echo $payment['cart_id']; ?></td>
                                        <td>
                                            <strong><?php echo $payment['product_name']; ?></strong>
                                            <div class="small text-muted">
                                                QTÉ: <?php echo $payment['quantity']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $payment['first_name'] . ' ' . $payment['last_name']; ?>
                                            <div class="small text-muted"><?php echo $payment['email']; ?></div>
                                        </td>
                                        <td><?php echo number_format($payment['total_amount'], 2); ?> FCFA</td>
                                        <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <?php echo $payment['verified_by_first'] . ' ' . $payment['verified_by_last']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle avec Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- JavaScript Personnalisé -->
<script>
    $(document).ready(function() {
    // Fermeture automatique des alertes après 5 secondes
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
    
    // Initialisation des infobulles
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Fonctionnalité de recherche de client
    $("#customerSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#pendingPaymentsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Soumission de formulaire AJAX pour la vérification des paiements
    $("form[name='verify_payment_form']").on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('Êtes-vous sûr de vouloir vérifier ce paiement ? Cela mettra à jour l\'inventaire et les niveaux de stock.')) {
            return false;
        }
        
        var cartId = $(this).find("input[name='cart_id']").val();
        
        $.ajax({
            url: 'verify_payment_api.php', // Assurez-vous que cela pointe vers votre fichier paste.txt
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'verify_payment',
                cart_id: cartId
            }),
            success: function(response) {
                if (response.status === 'success') {
                    // Afficher le message de succès
                    var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-check-circle me-2"></i> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    
                    // Supprimer la ligne vérifiée du tableau
                    $("input[value='" + cartId + "']").closest('tr').fadeOut(500, function() {
                        $(this).remove();
                    });
                    
                    // Mettre à jour les statistiques (vous pouvez actualiser la page ou mettre à jour les statistiques dynamiquement)
                    
                    // Ajouter l'alerte en haut du conteneur
                    $('.container-fluid').prepend(alertHtml);
                    
                    // Fermeture automatique après 5 secondes
                    setTimeout(function() {
                        $('.alert').alert('close');
                    }, 5000);
                } else {
                    alert('Erreur : ' + response.message);
                }
            },
            error: function() {
                alert('Une erreur s\'est produite lors du traitement de votre demande.');
            }
        });
    });
});
    
    // Confirmer la vérification
    function confirmVerification(cartId, productName) {
        return confirm(`Êtes-vous sûr de vouloir vérifier le paiement pour ${productName} ? Cela mettra à jour l'inventaire et les niveaux de stock.`);
    }
</script>

</body>
</html>