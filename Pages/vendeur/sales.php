<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has vendeur role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    echo "<div class='error-message'>Accès non autorisé</div>";
    exit();
}

// Include database configuration
require_once '../DatabaseConnection/db_config.php';

// Set default values for filters
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare base query for sales data
$query = "SELECT o.order_id, o.total_amount, o.order_date, o.status, o.payment_status, 
          u.first_name, u.last_name, u.email, u.phone_number
          FROM orders o
          JOIN users u ON o.user_id = u.user_id
          WHERE 1=1";

// Apply filters
if ($date_filter == 'today') {
    $query .= " AND DATE(o.order_date) = CURDATE()";
} elseif ($date_filter == 'week') {
    $query .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($date_filter == 'month') {
    $query .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

if ($status_filter != 'all') {
    $query .= " AND o.status = ?";
}

if (!empty($search)) {
    $query .= " AND (o.order_id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
}

$query .= " ORDER BY o.order_date DESC";

// Prepare and execute the query using PDO
try {
    $stmt = $pdo->prepare($query);
    
    $paramIndex = 1;
    if ($status_filter != 'all') {
        $stmt->bindValue($paramIndex++, $status_filter);
    }
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindValue($paramIndex++, $searchParam);
        $stmt->bindValue($paramIndex++, $searchParam);
        $stmt->bindValue($paramIndex++, $searchParam);
        $stmt->bindValue($paramIndex++, $searchParam);
    }
    
    $stmt->execute();
    $sales = $stmt->fetchAll();
    
    // Get sales statistics
    $statsQuery = "SELECT 
                    SUM(CASE WHEN DATE(order_date) = CURDATE() THEN total_amount ELSE 0 END) as daily_total,
                    SUM(CASE WHEN order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN total_amount ELSE 0 END) as weekly_total,
                    SUM(CASE WHEN order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN total_amount ELSE 0 END) as monthly_total,
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status IN ('pending', 'processing') THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status IN ('shipped', 'delivered') THEN 1 END) as completed_orders,
                    AVG(total_amount) as avg_order_value
                  FROM orders";
    
    $statsStmt = $pdo->query($statsQuery);
    $salesStats = $statsStmt->fetch();
    
    // Get top selling products in last 30 days
    $topProductsQuery = "SELECT p.product_id, p.name, p.price, 
                         SUM(oi.quantity) as total_sold,
                         SUM(oi.price_at_time * oi.quantity) as total_revenue
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.product_id
                         JOIN orders o ON oi.order_id = o.order_id
                         WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                         GROUP BY p.product_id
                         ORDER BY total_sold DESC
                         LIMIT 5";
    
    $topProductsStmt = $pdo->query($topProductsQuery);
    $topProducts = $topProductsStmt->fetchAll();
    
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit();
}
?>

<div class="sales-container">
    <div class="page-header">
        <h1><i class="fas fa-cash-register"></i> Gestion des Ventes</h1>
        <p>Consultez et gérez toutes les ventes et transactions.</p>
    </div>
    
    <!-- Sales Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-card-info">
                <h3>Ventes Aujourd'hui</h3>
                <p><?= number_format($salesStats['daily_total'] ?? 0, 2, ',', ' ') ?> CFA</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="stat-card-info">
                <h3>Ventes Hebdomadaires</h3>
                <p><?= number_format($salesStats['weekly_total'] ?? 0, 2, ',', ' ') ?> CFA</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon purple">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-card-info">
                <h3>Ventes Mensuelles</h3>
                <p><?= number_format($salesStats['monthly_total'] ?? 0, 2, ',', ' ') ?> CFA</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon orange">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-card-info">
                <h3>Panier Moyen</h3>
                <p><?= number_format($salesStats['avg_order_value'] ?? 0, 2, ',', ' ') ?> CFA</p>
            </div>
        </div>
    </div>
    
    <!-- Filter Controls -->
    <div class="filters-container">
        <form id="sales-filter" method="GET" class="filters-form">
            <div class="filter-group">
                <label for="date_filter">Période:</label>
                <select name="date_filter" id="date_filter" class="form-control">
                    <option value="all" <?= $date_filter == 'all' ? 'selected' : '' ?>>Toutes les dates</option>
                    <option value="today" <?= $date_filter == 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                    <option value="week" <?= $date_filter == 'week' ? 'selected' : '' ?>>Cette semaine</option>
                    <option value="month" <?= $date_filter == 'month' ? 'selected' : '' ?>>Ce mois</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="status_filter">Statut:</label>
                <select name="status_filter" id="status_filter" class="form-control">
                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Tous les statuts</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>En traitement</option>
                    <option value="shipped" <?= $status_filter == 'shipped' ? 'selected' : '' ?>>Expédié</option>
                    <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Livré</option>
                    <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>
            <div class="filter-group search-group">
                <input type="text" name="search" id="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" class="form-control">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <button id="export-sales" class="btn-export"><i class="fas fa-file-export"></i> Exporter</button>
    </div>
    
    <!-- Sales Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Paiement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sales) > 0): ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td>#<?= $sale['order_id'] ?></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name"><?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?></div>
                                    <div class="customer-email"><?= htmlspecialchars($sale['email']) ?></div>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($sale['order_date'])) ?></td>
                            <td><?= number_format($sale['total_amount'], 2, ',', ' ') ?> €</td>
                            <td>
                                <span class="status-badge <?= strtolower($sale['status']) ?>">
                                    <?= ucfirst($sale['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="payment-badge <?= strtolower($sale['payment_status']) ?>">
                                    <?= ucfirst($sale['payment_status']) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="view_order.php?id=<?= $sale['order_id'] ?>" class="btn-action view" title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="update_order.php?id=<?= $sale['order_id'] ?>" class="btn-action edit" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($sale['payment_status'] == 'pending'): ?>
                                    <a href="verify_payment.php?id=<?= $sale['order_id'] ?>" class="btn-action verify" title="Vérifier paiement">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">Aucune vente trouvée</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Top Products Section -->
    <!-- Top Products Section -->


<style>
.sales-container {
    padding: 20px;
    font-family: 'Nunito', sans-serif;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.page-header h1 i {
    margin-right: 10px;
    color: #3498db;
}

.page-header p {
    color: #7f8c8d;
    font-size: 14px;
}

.filters-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filters-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    flex: 1;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 180px;
}

.filter-group label {
    font-size: 12px;
    margin-bottom: 5px;
    color: #7f8c8d;
}

.search-group {
    position: relative;
    flex-grow: 1;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn-search {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
}

.btn-export {
    padding: 8px 15px;
    background-color: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-export:hover {
    background-color: #27ae60;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.stat-card-icon.blue {
    background-color: #3498db;
}

.stat-card-icon.green {
    background-color: #2ecc71;
}

.stat-card-icon.purple {
    background-color: #9b59b6;
}

.stat-card-icon.orange {
    background-color: #e67e22;
}

.stat-card-info h3 {
    font-size: 14px;
    color: #7f8c8d;
    margin: 0 0 5px 0;
}

.stat-card-info p {
    font-size: 20px;
    font-weight: bold;
    color: #2c3e50;
    margin: 0;
}

.table-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th, .data-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
}

.data-table th {
    background-color: #f9f9f9;
    color: #7f8c8d;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.data-table tbody tr:hover {
    background-color: #f5f7fa;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-name {
    font-weight: 600;
    color: #2c3e50;
}

.customer-email {
    font-size: 12px;
    color: #7f8c8d;
}

.status-badge, .payment-badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.status-badge.pending, .payment-badge.pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-badge.processing {
    background-color: #cce5ff;
    color: #004085;
}

.status-badge.shipped {
    background-color: #d1ecf1;
    color: #0c5460;
}

.status-badge.delivered {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.cancelled, .payment-badge.failed {
    background-color: #f8d7da;
    color: #721c24;
}

.payment-badge.paid, .payment-badge.completed {
    background-color: #d4edda;
    color: #155724;
}

.actions {
    display: flex;
    gap: 5px;
}

.btn-action {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-size: 12px;
    transition: transform 0.2s, background-color 0.2s;
}

.btn-action:hover {
    transform: scale(1.1);
}

.btn-action.view {
    background-color: #3498db;
}

.btn-action.edit {
    background-color: #f39c12;
}

.btn-action.verify {
    background-color: #2ecc71;
}

.no-data {
    text-align: center;
    color: #7f8c8d;
    padding: 20px;
}

.top-products-section {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 30px;
}

.top-products-section h2 {
    font-size: 18px;
    color: #2c3e50;
    margin-top: 0;
    margin-bottom: 15px;
}

.product-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.product-link:hover {
    text-decoration: underline;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

@media (max-width: 1024px) {
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .data-table {
        min-width: 700px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to filter controls for auto-submit
    document.getElementById('date_filter').addEventListener('change', function() {
        document.getElementById('sales-filter').submit();
    });
    
    document.getElementById('status_filter').addEventListener('change', function() {
        document.getElementById('sales-filter').submit();
    });
    
    // Export functionality
    document.getElementById('export-sales').addEventListener('click', function() {
        exportSalesToCSV();
    });
    
    function exportSalesToCSV() {
        // Collect current filter parameters
        const dateFilter = document.getElementById('date_filter').value;
        const statusFilter = document.getElementById('status_filter').value;
        const search = document.getElementById('search').value;
        
        // Create export URL with current filters
        const exportUrl = `export_sales.php?date_filter=${dateFilter}&status_filter=${statusFilter}&search=${encodeURIComponent(search)}`;
        
        // Redirect to export script
        window.location.href = exportUrl;
    }
    
    // Add search functionality with debounce
    let searchTimeout = null;
    const searchInput = document.getElementById('search');
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            document.getElementById('sales-filter').submit();
        }, 500);
    });
    
    // Initialize tooltips for action buttons
    const actionButtons = document.querySelectorAll('.btn-action');
    actionButtons.forEach(function(button) {
        const title = button.getAttribute('title');
        if (title) {
            button.setAttribute('data-tooltip', title);
            
            // Simple tooltip functionality
            button.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('data-tooltip');
                tooltip.style.position = 'absolute';
                tooltip.style.background = 'rgba(0,0,0,0.8)';
                tooltip.style.color = '#fff';
                tooltip.style.padding = '5px 10px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.fontSize = '12px';
                tooltip.style.zIndex = '1000';
                
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.bottom + 10) + 'px';
                tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                
                this.tooltip = tooltip;
            });
            
            button.addEventListener('mouseleave', function() {
                if (this.tooltip) {
                    document.body.removeChild(this.tooltip);
                    this.tooltip = null;
                }
            });
        }
    });
    
    // Highlight search results if any
    if (searchInput.value) {
        highlightSearchTerms(searchInput.value);
    }
    
    function highlightSearchTerms(term) {
        if (!term) return;
        
        const tableRows = document.querySelectorAll('.data-table tbody tr');
        term = term.toLowerCase();
        
        tableRows.forEach(function(row) {
            const cells = row.querySelectorAll('td');
            cells.forEach(function(cell) {
                if (!cell.classList.contains('actions')) {
                    const text = cell.textContent;
                    if (text.toLowerCase().includes(term)) {
                        const regex = new RegExp('(' + term + ')', 'gi');
                        cell.innerHTML = cell.innerHTML.replace(regex, '<mark>$1</mark>');
                    }
                }
            });
        });
    }
    
    // Add row click functionality for order details view
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(function(row) {
        row.style.cursor = 'pointer';
        
        row.addEventListener('click', function(e) {
            // Don't trigger if clicked on an action button
            if (e.target.closest('.actions') || e.target.closest('.btn-action')) {
                return;
            }
            
            // Extract order ID from the first cell
            const orderId = this.querySelector('td:first-child').textContent.replace('#', '');
            window.location.href = 'view_order.php?id=' + orderId;
        });
    });
    
    // Add visual feedback when form is submitted
    document.getElementById('sales-filter').addEventListener('submit', function() {
        document.querySelector('.sales-container').classList.add('loading');
    });
});
</script>

<style>
/* Additional styles for enhanced functionality */
.tooltip {
    pointer-events: none;
    transition: opacity 0.3s;
}

.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7) url('path/to/loading-spinner.gif') no-repeat center;
    z-index: 1000;
}

mark {
    background-color: #fff3cd;
    padding: 2px 0;
    border-radius: 2px;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
}

.data-table tbody tr:hover td:first-child {
    animation: pulse 1.5s infinite;
}
</style>