<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once('../DatabaseConnection/db_config.php');

// Check if user is logged in and has vendeur role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    echo "<div class='error-message'>Accès non autorisé</div>";
    exit();
}

// Récupération des statistiques de ventes récentes
$vendeurId = $_SESSION['user_id'];

// Requête pour les ventes récentes
$recentSalesQuery = "
    SELECT 
        o.order_id as id, 
        CONCAT(u.first_name, ' ', u.last_name) as customer,
        o.total_amount as amount,
        DATE_FORMAT(o.order_date, '%d/%m/%Y') as date,
        o.status,
        CASE 
            WHEN o.status = 'delivered' THEN 'Livré'
            WHEN o.status = 'shipped' THEN 'Expédié'
            WHEN o.status = 'processing' THEN 'En traitement'
            WHEN o.status = 'pending' THEN 'En attente'
            WHEN o.status = 'cancelled' THEN 'Annulé'
            ELSE o.status
        END as status_fr
    FROM 
        orders o
    JOIN 
        users u ON o.user_id = u.user_id
    WHERE 
        o.verified_by = ? OR EXISTS (
            SELECT 1 FROM order_items oi 
            WHERE oi.order_id = o.order_id AND oi.payment_verified_by = ?
        )
    ORDER BY 
        o.order_date DESC
    LIMIT 4";

$stmt = $pdo->prepare($recentSalesQuery);
$stmt->execute([$vendeurId, $vendeurId]);
$recentSales = $stmt->fetchAll();

// Convertir les montants au format français
foreach ($recentSales as &$sale) {
    $sale['amount'] = number_format($sale['amount'], 2, ',', ' ') . ' CFA';
}

// Requête pour les statistiques de ventes
$salesStatsQuery = "
    SELECT
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE DATE(order_date) = CURDATE() AND (verified_by = ? OR EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.order_id AND payment_verified_by = ?))) as daily,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND (verified_by = ? OR EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.order_id AND payment_verified_by = ?))) as weekly,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND (verified_by = ? OR EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.order_id AND payment_verified_by = ?))) as monthly,
        (SELECT COUNT(*) FROM orders WHERE (verified_by = ? OR EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.order_id AND payment_verified_by = ?))) as totalOrders,
        (SELECT COUNT(*) FROM orders WHERE status = 'pending' AND (verified_by = ? OR EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.order_id AND payment_verified_by = ?))) as pendingOrders,
        (SELECT COALESCE(AVG(total_amount), 0) FROM orders WHERE (verified_by = ? OR EXISTS (SELECT 1 FROM order_items WHERE order_id = orders.order_id AND payment_verified_by = ?))) as avgOrderValue
";

$stmt = $pdo->prepare($salesStatsQuery);
$stmt->execute([
    $vendeurId, $vendeurId,
    $vendeurId, $vendeurId,
    $vendeurId, $vendeurId,
    $vendeurId, $vendeurId,
    $vendeurId, $vendeurId,
    $vendeurId, $vendeurId
]);
$salesStats = $stmt->fetch();

// Formater les montants au format français
$salesStats['daily'] = number_format($salesStats['daily'], 2, ',', ' ') . ' CFA';
$salesStats['weekly'] = number_format($salesStats['weekly'], 2, ',', ' ') . ' CFA';
$salesStats['monthly'] = number_format($salesStats['monthly'], 2, ',', ' ') . ' CFA';
$salesStats['avgOrderValue'] = number_format($salesStats['avgOrderValue'], 2, ',', ' ') . ' CFA';

// Requête pour les produits les plus vendus
$topProductsQuery = "
    SELECT 
        p.product_id as id,
        p.name,
        p.sales_count as sales,
        (p.price * p.sales_count) as revenue,
        p.stock_quantity as stock
    FROM 
        products p
    ORDER BY 
        p.sales_count DESC
    LIMIT 4";

$stmt = $pdo->prepare($topProductsQuery);
$stmt->execute();
$topProducts = $stmt->fetchAll();

// Formater les données des produits
foreach ($topProducts as &$product) {
    $product['id'] = 'PRD-' . $product['id'];
    $product['revenue'] = number_format($product['revenue'], 2, ',', ' ') . ' CFA';
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><i class="fas fa-chart-line"></i> Tableau de Bord Vendeur</h1>
        <p>Bienvenue, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Vendeur') ?>! Voici un aperçu de vos activités commerciales récentes.</p>
    </div>

    <!-- Statistiques de Ventes -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-card-info">
                <h3>Ventes Aujourd'hui</h3>
                <p><?= $salesStats['daily'] ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-card-info">
                <h3>Ventes Hebdomadaires</h3>
                <p><?= $salesStats['weekly'] ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon purple">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-card-info">
                <h3>Ventes Mensuelles</h3>
                <p><?= $salesStats['monthly'] ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon orange">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-card-info">
                <h3>Panier Moyen</h3>
                <p><?= $salesStats['avgOrderValue'] ?></p>
            </div>
        </div>
    </div>

    <!-- Aperçu des Commandes -->
    <div class="dashboard-row">
        <div class="dashboard-card orders-overview">
            <div class="card-header">
                <h2>Aperçu des Commandes</h2>
            </div>
            <div class="card-body">
                <div class="order-stats">
                    <div class="order-stat">
                        <span class="stat-value"><?= $salesStats['totalOrders'] ?></span>
                        <span class="stat-label">Total</span>
                    </div>
                    <div class="order-stat">
                        <span class="stat-value"><?= $salesStats['pendingOrders'] ?></span>
                        <span class="stat-label">En attente</span>
                    </div>
                    <div class="order-stat">
                        <span class="stat-value"><?= $salesStats['totalOrders'] - $salesStats['pendingOrders'] ?></span>
                        <span class="stat-label">Complétées</span>
                    </div>
                </div>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSales)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucune vente récente trouvée</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recentSales as $sale): ?>
                            <tr>
                                <td>#<?= $sale['id'] ?></td>
                                <td><?= htmlspecialchars($sale['customer']) ?></td>
                                <td><?= $sale['amount'] ?></td>
                                <td><?= $sale['date'] ?></td>
                                <td>
                                    <span class="status-badge <?= $sale['status'] === 'delivered' || $sale['status'] === 'shipped' ? 'success' : 'pending' ?>">
                                        <?= $sale['status_fr'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Produits les Plus Vendus -->
    <div class="dashboard-row">
        <div class="dashboard-card top-products">
            <div class="card-header">
                <h2>Produits les Plus Vendus</h2>
            </div>
            <div class="card-body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produit</th>
                            <th>Ventes</th>
                            <th>Revenu</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Aucun produit trouvé</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= $product['sales'] ?></td>
                                <td><?= $product['revenue'] ?></td>
                                <td>
                                    <span class="stock-badge <?= $product['stock'] < 10 ? 'low' : 'ok' ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<style>
.dashboard-container {
    padding: 20px;
    font-family: 'Nunito', sans-serif;
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-header h1 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.dashboard-header h1 i {
    margin-right: 10px;
    color: #3498db;
}

.dashboard-header p {
    color: #7f8c8d;
    font-size: 14px;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    display: flex;
    align-items: center;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.stat-card-icon i {
    font-size: 20px;
    color: white;
}

.stat-card-icon.blue {
    background: linear-gradient(45deg, #3498db, #2980b9);
}

.stat-card-icon.green {
    background: linear-gradient(45deg, #2ecc71, #27ae60);
}

.stat-card-icon.purple {
    background: linear-gradient(45deg, #9b59b6, #8e44ad);
}
.stat-card-icon.orange {
    background: linear-gradient(45deg, #f39c12, #d35400);
}

.stat-card-info h3 {
    margin: 0;
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 600;
}

.stat-card-info p {
    margin: 5px 0 0;
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
}

.dashboard-row {
    margin-bottom: 30px;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #2c3e50;
}

.view-all {
    font-size: 14px;
    color: #3498db;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.view-all i {
    margin-left: 5px;
}

.card-body {
    padding: 20px;
}

.order-stats {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ecf0f1;
}

.order-stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
}

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
}

.dashboard-table th {
    text-align: left;
    padding: 12px 15px;
    background-color: #f8f9fa;
    color: #2c3e50;
    font-size: 14px;
    font-weight: 600;
}

.dashboard-table td {
    padding: 12px 15px;
    border-top: 1px solid #ecf0f1;
    color: #2c3e50;
    font-size: 14px;
}

.text-center {
    text-align: center;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.success {
    background-color: rgba(46, 204, 113, 0.15);
    color: #27ae60;
}

.status-badge.pending {
    background-color: rgba(243, 156, 18, 0.15);
    color: #d35400;
}

.stock-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.stock-badge.ok {
    background-color: rgba(46, 204, 113, 0.15);
    color: #27ae60;
}

.stock-badge.low {
    background-color: rgba(231, 76, 60, 0.15);
    color: #c0392b;
}

.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.quick-link-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: #2c3e50;
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.quick-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.quick-link-card i {
    font-size: 24px;
    margin-bottom: 10px;
    color: #3498db;
}

.quick-link-card span {
    font-weight: 600;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    text-align: center;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: 1fr 1fr;
    }
    
    .quick-links {
        grid-template-columns: 1fr 1fr;
    }
    
    .dashboard-table th:nth-child(4),
    .dashboard-table td:nth-child(4) {
        display: none;
    }
}

@media (max-width: 576px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .quick-links {
        grid-template-columns: 1fr;
    }
    
    .dashboard-table th:nth-child(3),
    .dashboard-table td:nth-child(3),
    .dashboard-table th:nth-child(5),
    .dashboard-table td:nth-child(5) {
        display: none;
    }
    
    .order-stats {
        flex-direction: column;
        gap: 15px;
    }
}