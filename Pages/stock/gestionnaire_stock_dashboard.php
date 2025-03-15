<?php
// Start session to access user data
session_start();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) { // 4 is the role_id for gestionnaire_stock
    // Redirect to login page if not logged in or not the correct role
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once '../DatabaseConnection/db_config.php';

// Get essential dashboard statistics
$stats = array();

// Get total products count
$stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM products");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_products'] = $result->fetch_assoc()['total_products'];

// Get low stock products count
$stmt = $conn->prepare("SELECT COUNT(*) as low_stock FROM products WHERE stock_quantity <= low_stock_threshold");
$stmt->execute();
$result = $stmt->get_result();
$stats['low_stock'] = $result->fetch_assoc()['low_stock'];

// Get total suppliers count
$stmt = $conn->prepare("SELECT COUNT(*) as total_suppliers FROM suppliers");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_suppliers'] = $result->fetch_assoc()['total_suppliers'];

// Get pending deliveries count
$stmt = $conn->prepare("SELECT COUNT(*) as pending_deliveries FROM deliveries WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
$stats['pending_deliveries'] = $result->fetch_assoc()['pending_deliveries'];

// Get recent stock changes
$stmt = $conn->prepare("
    SELECT 
        pih.history_id,
        p.name as product_name,
        pih.previous_quantity,
        pih.new_quantity,
        pih.change_type,
        pih.change_date,
        CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
    FROM product_inventory_history pih
    JOIN products p ON pih.product_id = p.product_id
    JOIN users u ON pih.changed_by = u.user_id
    ORDER BY pih.change_date DESC
    LIMIT 10
");
$stmt->execute();
$recent_changes = $stmt->get_result();

// Get low stock products with detailed info
$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.stock_quantity,
        p.low_stock_threshold,
        p.sales_count,
        s.company_name as supplier
    FROM products p
    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
    WHERE p.stock_quantity <= p.low_stock_threshold
    ORDER BY (p.low_stock_threshold - p.stock_quantity) DESC
    LIMIT 10
");
$stmt->execute();
$low_stock_products = $stmt->get_result();

// Get top selling products
$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.name,
        p.stock_quantity,
        p.sales_count,
        pc.category_name
    FROM products p
    JOIN product_categories pc ON p.category_id = pc.category_id
    ORDER BY p.sales_count DESC
    LIMIT 10
");
$stmt->execute();
$top_selling = $stmt->get_result();

// Get unread notifications for the user
$stmt = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$unread_notifications = $result->fetch_assoc()['unread_count'];

// Close connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestionnaire de Stock</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gray-color: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .dashboard-container {
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .dashboard-title {
            font-size: 24px;
            color: var(--primary-color);
        }
        
        .date-display {
            font-size: 14px;
            color: var(--gray-color);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-primary {
            border-left: 4px solid var(--primary-color);
        }
        
        .card-warning {
            border-left: 4px solid var(--warning-color);
        }
        
        .card-success {
            border-left: 4px solid var(--success-color);
        }
        
        .card-info {
            border-left: 4px solid var(--secondary-color);
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: var(--gray-color);
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .stat-card .icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            opacity: 0.2;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h2 {
            font-size: 18px;
            color: var(--primary-color);
        }
        
        .card-header .view-all {
            font-size: 14px;
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 10px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f5f7fa;
        }
        
        .data-table tbody tr:hover {
            background-color: #edf2f7;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }
        
        .badge-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }
        
        .badge-info {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }
        
        .low-stock-warning {
            color: var(--danger-color);
            font-weight: 700;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-info {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .action-card {
            flex: 1;
            min-width: 200px;
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: var(--dark-color);
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .action-card i {
            font-size: 24px;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .action-card h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .action-card p {
            font-size: 12px;
            color: var(--gray-color);
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Tableau de Bord Gestionnaire de Stock</h1>
            <div class="date-display"><?php echo date('l, d F Y'); ?></div>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card card-primary">
                <h3>Total Produits</h3>
                <div class="value"><?php echo $stats['total_products']; ?></div>
                <div class="icon"><i class="fas fa-box"></i></div>
            </div>
            
            <div class="stat-card card-warning">
                <h3>Produits Stock Bas</h3>
                <div class="value"><?php echo $stats['low_stock']; ?></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            
            <div class="stat-card card-success">
                <h3>Fournisseurs</h3>
                <div class="value"><?php echo $stats['total_suppliers']; ?></div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
            
            <div class="stat-card card-info">
                <h3>Livraisons en Attente</h3>
                <div class="value"><?php echo $stats['pending_deliveries']; ?></div>
                <div class="icon"><i class="fas fa-truck-loading"></i></div>
            </div>
        </div>
        
        
        <div class="dashboard-sections">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Produits à Stock Bas</h2>
                   
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Seuil</th>
                            <th>Fournisseur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($low_stock_products->num_rows > 0): ?>
                            <?php while ($product = $low_stock_products->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td class="<?php echo ($product['stock_quantity'] == 0) ? 'low-stock-warning' : ''; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </td>
                                    <td><?php echo $product['low_stock_threshold']; ?></td>
                                    <td><?php echo htmlspecialchars($product['supplier']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Aucun produit à stock bas pour le moment</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        
        <div class="dashboard-sections">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Produits les Plus Vendus</h2>
                    
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Catégorie</th>
                            <th>Ventes</th>
                            <th>Stock Actuel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($top_selling->num_rows > 0): ?>
                            <?php while ($product = $top_selling->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo $product['sales_count']; ?></td>
                                    <td class="<?php echo ($product['stock_quantity'] <= 5) ? 'low-stock-warning' : ''; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </td>
                                  
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Aucune donnée de vente disponible</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Actions Rapides</h2>
                </div>
                
                
                <?php if ($unread_notifications > 0): ?>
                <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 15px;">
                    <h3 style="color: #856404; font-size: 16px; margin-bottom: 5px;">
                        <i class="fas fa-bell" style="margin-right: 5px;"></i> 
                        Vous avez <?php echo $unread_notifications; ?> notifications non lues
                    </h3>
                    <a href="../notifications.php" style="color: #0056b3; text-decoration: none; font-size: 14px;">
                        Voir toutes les notifications
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Add any JavaScript functionality you need here
        document.addEventListener('DOMContentLoaded', function() {
            // Example: Refresh dashboard data every 5 minutes
            setInterval(function() {
                location.reload();
            }, 300000);
        });
    </script>
</body>
</html>