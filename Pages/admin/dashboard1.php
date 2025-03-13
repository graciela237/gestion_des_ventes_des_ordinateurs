<?php
// Start session
session_start();

// Include database connection
require_once '../DatabaseConnection/db_config.php';

// Define current year
$currentYear = date('Y');

// Modified function to get data without defaults
function getQueryResult($conn, $query) {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    return $result;
}

// Modified function to get single value
function getSingleValue($conn, $query, $columnName) {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return isset($row[$columnName]) ? $row[$columnName] : null;
    }
    
    return null;
}

// Error handling function
function handleErrors($errorMsg) {
    error_log($errorMsg);
    // You can redirect to an error page or display a user-friendly message
    // For now, we'll just return null so the dashboard shows empty data
    return null;
}

// Initialize variables
$totalUsers = 0;
$totalProducts = 0;
$totalOrders = 0;
$totalRevenue = "0.00";
$monthlyRevenueData = array_fill(0, 12, 0);
$orderStatusLabels = [];
$orderStatusData = [];
$topProductsLabels = [];
$topProductsData = [];
$userTrendsLabels = [];
$userTrendsData = [];
$lowStockLabels = [];
$lowStockData = [];
$lowStockThresholds = [];
$weeklyLabels = [];
$weeklyData = [];
$monthlyWeekLabels = [];
$monthlyWeekData = [];
$yearlyMonthLabels = [];
$yearlyMonthData = [];

try {
    // Fetch total users
    $totalUsers = getSingleValue(
        $conn, 
        "SELECT COUNT(user_id) AS total_users FROM users", 
        'total_users'
    ) ?? 0;

    // Fetch total products
    $totalProducts = getSingleValue(
        $conn, 
        "SELECT COUNT(product_id) AS total_products FROM products", 
        'total_products'
    ) ?? 0;

    // Fetch total orders
    $totalOrders = getSingleValue(
        $conn, 
        "SELECT COUNT(order_id) AS total_orders FROM orders", 
        'total_orders'
    ) ?? 0;

    // Fetch total revenue
    $revenueValue = getSingleValue(
        $conn, 
        "SELECT SUM(total_amount) AS total_revenue FROM orders WHERE payment_status = 'completed'", 
        'total_revenue'
    ) ?? 0;
    $totalRevenue = number_format((float)$revenueValue, 2);

    // Get monthly sales data for current year
    $monthlySalesQuery = "
        SELECT 
            MONTH(order_date) as month, 
            SUM(total_amount) as monthly_revenue
        FROM 
            orders 
        WHERE 
            YEAR(order_date) = $currentYear AND 
            payment_status IN ('completed', 'partial')
        GROUP BY 
            MONTH(order_date)
        ORDER BY 
            month ASC
    ";

    // Initialize monthly revenue data array
    $monthlyRevenueData = array_fill(0, 12, 0);

    // Get actual monthly sales data
    $monthlySalesResult = getQueryResult($conn, $monthlySalesQuery);
    while($row = $monthlySalesResult->fetch_assoc()) {
        $month = (int)$row['month'] - 1; // Adjust for 0-based array
        $monthlyRevenueData[$month] = (float)$row['monthly_revenue'];
    }

    // Get order status distribution
    $orderStatusQuery = "
        SELECT 
            status, 
            COUNT(*) as count
        FROM 
            orders
        GROUP BY 
            status
    ";

    // Get order status data
    $orderStatusResult = getQueryResult($conn, $orderStatusQuery);
    $orderStatusLabels = [];
    $orderStatusData = [];
    while($row = $orderStatusResult->fetch_assoc()) {
        $orderStatusLabels[] = ucfirst($row['status']);
        $orderStatusData[] = (int)$row['count'];
    }

    // Get top selling products
    $topProductsQuery = "
        SELECT 
            p.name, 
            SUM(oi.quantity) as total_sold
        FROM 
            order_items oi
        JOIN 
            products p ON oi.product_id = p.product_id
        JOIN 
            orders o ON oi.order_id = o.order_id
        WHERE 
            o.payment_status IN ('completed', 'partial')
        GROUP BY 
            p.product_id
        ORDER BY 
            total_sold DESC
        LIMIT 5
    ";

    // Get top products data
    $topProductsResult = getQueryResult($conn, $topProductsQuery);
    $topProductsLabels = [];
    $topProductsData = [];
    while($row = $topProductsResult->fetch_assoc()) {
        $topProductsLabels[] = $row['name'];
        $topProductsData[] = (int)$row['total_sold'];
    }

    // Get user registration trends
    $userTrendsQuery = "
        SELECT 
            MONTH(registration_date) as month,
            YEAR(registration_date) as year,
            COUNT(*) as new_users
        FROM 
            users
        WHERE 
            registration_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY 
            YEAR(registration_date), MONTH(registration_date)
        ORDER BY 
            year ASC, month ASC
    ";

    // Get user trends data
    $userTrendsResult = getQueryResult($conn, $userTrendsQuery);
    $userTrendsLabels = [];
    $userTrendsData = [];
    while($row = $userTrendsResult->fetch_assoc()) {
        $monthName = date("M", mktime(0, 0, 0, $row['month'], 1));
        $userTrendsLabels[] = $monthName . " " . $row['year'];
        $userTrendsData[] = (int)$row['new_users'];
    }

    // Get product inventory status (low stock items)
    $lowStockQuery = "
        SELECT 
            name, 
            stock_quantity,
            low_stock_threshold
        FROM 
            products
        WHERE 
            stock_quantity <= low_stock_threshold
        ORDER BY 
            (stock_quantity / low_stock_threshold) ASC
        LIMIT 5
    ";

    // Get low stock data
    $lowStockResult = getQueryResult($conn, $lowStockQuery);
    $lowStockLabels = [];
    $lowStockData = [];
    $lowStockThresholds = [];
    while($row = $lowStockResult->fetch_assoc()) {
        $lowStockLabels[] = $row['name'];
        $lowStockData[] = (int)$row['stock_quantity'];
        $lowStockThresholds[] = (int)$row['low_stock_threshold'];
    }

    // Get weekly sales data
    $weeklyQuery = "
        SELECT 
            DAYOFWEEK(order_date) as day,
            SUM(total_amount) as daily_revenue
        FROM 
            orders
        WHERE 
            order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)
        GROUP BY 
            DAYOFWEEK(order_date)
        ORDER BY 
            day ASC
    ";
    
    $weeklyResult = getQueryResult($conn, $weeklyQuery);
    $weeklyLabels = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    $weeklyData = array_fill(0, 7, 0);
    while($row = $weeklyResult->fetch_assoc()) {
        $day = ($row['day'] % 7); // Convert to 0-6 index (Sunday = 0)
        $weeklyData[$day] = (float)$row['daily_revenue'];
    }

    // Get monthly (weekly) sales data
    $monthlyWeekQuery = "
        SELECT 
            WEEK(order_date, 1) - WEEK(DATE_SUB(order_date, INTERVAL DAYOFMONTH(order_date)-1 DAY), 1) + 1 as week_of_month,
            SUM(total_amount) as weekly_revenue
        FROM 
            orders
        WHERE 
            MONTH(order_date) = MONTH(CURDATE()) AND
            YEAR(order_date) = YEAR(CURDATE())
        GROUP BY 
            week_of_month
        ORDER BY 
            week_of_month ASC
    ";
    
    $monthlyWeekResult = getQueryResult($conn, $monthlyWeekQuery);
    $monthlyWeekLabels = [];
    $monthlyWeekData = [];
    while($row = $monthlyWeekResult->fetch_assoc()) {
        $monthlyWeekLabels[] = 'Sem ' . $row['week_of_month'];
        $monthlyWeekData[] = (float)$row['weekly_revenue'];
    }

    // Get yearly (monthly) sales data
    $yearlyMonthQuery = "
        SELECT 
            MONTH(order_date) as month,
            SUM(total_amount) as monthly_revenue
        FROM 
            orders
        WHERE 
            YEAR(order_date) = YEAR(CURDATE())
        GROUP BY 
            MONTH(order_date)
        ORDER BY 
            month ASC
    ";
    
    $yearlyMonthResult = getQueryResult($conn, $yearlyMonthQuery);
    $monthNames = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
    $yearlyMonthLabels = [];
    $yearlyMonthData = array_fill(0, 12, 0);
    while($row = $yearlyMonthResult->fetch_assoc()) {
        $month = (int)$row['month'] - 1;
        $yearlyMonthLabels[$month] = $monthNames[$month];
        $yearlyMonthData[$month] = (float)$row['monthly_revenue'];
    }
    
    // Ensure we have all month labels
    for ($i = 0; $i < 12; $i++) {
        if (!isset($yearlyMonthLabels[$i])) {
            $yearlyMonthLabels[$i] = $monthNames[$i];
        }
    }
    ksort($yearlyMonthLabels);
    
} catch (Exception $e) {
    handleErrors($e->getMessage());
}

// Close the database connection if it exists
if ($conn) {
    $conn->close();
}
?>

<!-- Dashboard Content -->
<div class="page-header">
    <h1>Tableau de Bord</h1>
    <div class="breadcrumb">
        <a href="dashboard.php">Accueil</a> / <span>Tableau de Bord</span>
    </div>
</div>

<div class="dashboard-content">
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-card-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-card-info">
                <h4>Total Utilisateurs</h4>
                <h2><?= $totalUsers ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon green">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-card-info">
                <h4>Total Produits</h4>
                <h2><?= $totalProducts ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon orange">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-card-info">
                <h4>Commandes</h4>
                <h2><?= $totalOrders ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon red">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-card-info">
                <h4>Revenu Total</h4>
                <h2>CFA<?= $totalRevenue ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Add table containers after the stat cards -->
<div class="dashboard-tables">
    <div class="table-row">
        <div class="table-container">
            <div class="table-header">
                <h3>Revenu Mensuel <?= $currentYear ?></h3>
            </div>
            <div class="table-body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th>Revenu (CFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                        $totalMonthlyRevenue = 0;
                        for ($i = 0; $i < 12; $i++) { 
                            $revenue = number_format($monthlyRevenueData[$i], 2);
                            $totalMonthlyRevenue += $monthlyRevenueData[$i];
                            echo "<tr>";
                            echo "<td>{$monthNames[$i]}</td>";
                            echo "<td class='number-cell'>CFA {$revenue}</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th class="number-cell">CFA <?= number_format($totalMonthlyRevenue, 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                <h3>Statut des Commandes</h3>
            </div>
            <div class="table-body">
                <?php if (empty($orderStatusLabels)): ?>
                <div class="no-data-message">Aucune donnée disponible</div>
                <?php else: ?>
                <table class="dashboard-table">
                    <thead>
    <tr>
        <th>Statut</th>
        <th>Nombre</th>
        <th>Pourcentage</th>
    </tr>
</thead>
<tbody>
    <?php 
    $totalOrders = array_sum($orderStatusData);
    for ($i = 0; $i < count($orderStatusLabels); $i++) { 
        $percentage = $totalOrders > 0 ? round(($orderStatusData[$i] / $totalOrders) * 100, 1) : 0;
        $statusClass = '';
        switch ($orderStatusLabels[$i]) {
            case 'En attente':
                $statusClass = 'status-pending';
                break;
            case 'En traitement':
                $statusClass = 'status-processing';
                break;
            case 'Expédié':
                $statusClass = 'status-shipped';
                break;
            case 'Livré':
                $statusClass = 'status-delivered';
                break;
            case 'Annulé':
                $statusClass = 'status-cancelled';
                break;
        }
        echo "<tr>";
        echo "<td><span class='status-badge {$statusClass}'>{$orderStatusLabels[$i]}</span></td>";
        echo "<td class='number-cell'>{$orderStatusData[$i]}</td>";
        echo "<td class='number-cell'>{$percentage}%</td>";
        echo "</tr>";
    }
    ?>
</tbody>
<tfoot>
    <tr>
        <th>Total</th>
        <th class="number-cell"><?= $totalOrders ?></th>
        <th class="number-cell">100%</th>
    </tr>
</tfoot>
</table>
<?php endif; ?>
</div>
</div>
</div>
<div class="table-row">
<div class="table-container">
<div class="table-header">
    <h3>Produits les Plus Vendus</h3>
</div>
<div class="table-body">
<?php if (empty($topProductsLabels)): ?>
<div class="no-data-message">Aucune donnée disponible</div>
<?php else: ?>
<table class="dashboard-table">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Unités Vendues</th>
            <th>Pourcentage</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $totalSold = array_sum($topProductsData);
        for ($i = 0; $i < count($topProductsLabels); $i++) { 
            $percentage = $totalSold > 0 ? round(($topProductsData[$i] / $totalSold) * 100, 1) : 0;
            echo "<tr>";
            echo "<td>{$topProductsLabels[$i]}</td>";
            echo "<td class='number-cell'>{$topProductsData[$i]}</td>";
            echo "<td class='number-cell'>{$percentage}%</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th class="number-cell"><?= $totalSold ?></th>
            <th class="number-cell">100%</th>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
</div>
</div>
<div class="table-container">
<div class="table-header">
    <h3>Tendance des Inscriptions</h3>
</div>
<div class="table-body">
<?php if (empty($userTrendsLabels)): ?>
<div class="no-data-message">Aucune donnée disponible</div>
<?php else: ?>
<table class="dashboard-table">
    <thead>
        <tr>
            <th>Période</th>
            <th>Nouveaux Utilisateurs</th>
            <th>Variation</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        for ($i = 0; $i < count($userTrendsLabels); $i++) { 
            $variation = 0;
            $variationClass = '';
            if ($i > 0) {
                $variation = $userTrendsData[$i] - $userTrendsData[$i-1];
                $variationPercentage = $userTrendsData[$i-1] > 0 ? round(($variation / $userTrendsData[$i-1]) * 100, 1) : 0;
                $variationText = $variation > 0 ? "+{$variation} ({$variationPercentage}%)" : "{$variation} ({$variationPercentage}%)";
                $variationClass = $variation > 0 ? 'positive-variation' : ($variation < 0 ? 'negative-variation' : '');
            } else {
                $variationText = "—";
            }
            echo "<tr>";
            echo "<td>{$userTrendsLabels[$i]}</td>";
            echo "<td class='number-cell'>{$userTrendsData[$i]}</td>";
            echo "<td class='number-cell {$variationClass}'>{$variationText}</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th class="number-cell"><?= array_sum($userTrendsData) ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
</div>
</div>
</div>
<div class="table-row">
<div class="table-container">
<div class="table-header">
    <h3>Produits à Faible Stock</h3>
</div>
<div class="table-body">
<?php if (empty($lowStockLabels)): ?>
<div class="no-data-message">Aucune donnée disponible</div>
<?php else: ?>
<table class="dashboard-table">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Stock Actuel</th>
            <th>Seuil Minimum</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php 
       for ($i = 0; $i < count($lowStockLabels); $i++) { 
    $stockRatio = $lowStockThresholds[$i] > 0 ? round(($lowStockData[$i] / $lowStockThresholds[$i]) * 100) : 0;
    $statusClass = '';
    $statusText = '';
    if ($stockRatio <= 25) {
        $statusClass = 'status-critical';
        $statusText = 'Critique';
    } elseif ($stockRatio <= 50) {
        $statusClass = 'status-warning';
        $statusText = 'Alerte';
    } elseif ($stockRatio <= 75) {
        $statusClass = 'status-caution';
        $statusText = 'Attention';
    } else {
        $statusClass = 'status-ok';
        $statusText = 'OK';
    }
        
        echo "<tr>";
        echo "<td>{$lowStockLabels[$i]}</td>";
        echo "<td class='number-cell'>{$lowStockData[$i]}</td>";
        echo "<td class='number-cell'>{$lowStockThresholds[$i]}</td>";
        echo "<td><span class='status-badge {$statusClass}'>{$statusText}</span></td>";
        echo "</tr>";
    }
    ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>
<div class="table-container">
<div class="table-header">
<h3>Analyse des Ventes</h3>
<div class="table-toggle">
    <button id="weeklyBtn" class="table-btn active">Semaine</button>
    <button id="monthlyBtn" class="table-btn">Mois</button>
    <button id="yearlyBtn" class="table-btn">Année</button>
</div>
</div>
<div class="table-body">
<!-- Weekly Sales Table (displayed by default) -->
<?php if (empty($weeklyData) || array_sum($weeklyData) == 0): ?>
<div class="no-data-message" id="weeklySalesNoData">Aucune donnée disponible</div>
<?php else: ?>
<table class="dashboard-table" id="weeklySalesTable">
    <thead>
        <tr>
            <th>Jour</th>
            <th>Ventes (CFA)</th>
            <th>Pourcentage</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $totalWeeklySales = array_sum($weeklyData);
        for ($i = 0; $i < count($weeklyLabels); $i++) { 
            $percentage = $totalWeeklySales > 0 ? round(($weeklyData[$i] / $totalWeeklySales) * 100, 1) : 0;
            echo "<tr>";
            echo "<td>{$weeklyLabels[$i]}</td>";
            echo "<td class='number-cell'>CFA " . number_format($weeklyData[$i], 2) . "</td>";
            echo "<td class='number-cell'>{$percentage}%</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th class="number-cell">CFA <?= number_format($totalWeeklySales, 2) ?></th>
            <th class="number-cell">100%</th>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
<!-- Monthly Sales Table (hidden by default) -->
<?php if (empty($monthlyWeekData) || array_sum($monthlyWeekData) == 0): ?>
<div class="no-data-message" id="monthlySalesNoData" style="display: none;">Aucune donnée disponible</div>
<?php else: ?>
<table class="dashboard-table" id="monthlySalesTable" style="display: none;">
    <thead>
        <tr>
            <th>Semaine</th>
            <th>Ventes (CFA)</th>
            <th>Pourcentage</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $totalMonthlySales = array_sum($monthlyWeekData);
        for ($i = 0; $i < count($monthlyWeekLabels); $i++) { 
            $percentage = $totalMonthlySales > 0 ? round(($monthlyWeekData[$i] / $totalMonthlySales) * 100, 1) : 0;
            echo "<tr>";
            echo "<td>{$monthlyWeekLabels[$i]}</td>";
            echo "<td class='number-cell'>CFA " . number_format($monthlyWeekData[$i], 2) . "</td>";
            echo "<td class='number-cell'>{$percentage}%</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th class="number-cell">CFA <?= number_format($totalMonthlySales, 2) ?></th>
            <th class="number-cell">100%</th>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
<!-- Yearly Sales Table (hidden by default) -->
<?php if (empty($yearlyMonthData) || array_sum($yearlyMonthData) == 0): ?>
<div class="no-data-message" id="yearlySalesNoData" style="display: none;">Aucune donnée disponible</div>
<?php else: ?>
<table class="dashboard-table" id="yearlySalesTable" style="display: none;">
    <thead>
        <tr>
            <th>Mois</th>
            <th>Ventes (CFA)</th>
            <th>Pourcentage</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $totalYearlySales = array_sum($yearlyMonthData);
        foreach ($yearlyMonthLabels as $i => $month) { 
            $percentage = $totalYearlySales > 0 ? round(($yearlyMonthData[$i] / $totalYearlySales) * 100, 1) : 0;
            echo "<tr>";
            echo "<td>{$month}</td>";
            echo "<td class='number-cell'>CFA " . number_format($yearlyMonthData[$i], 2) . "</td>";
            echo "<td class='number-cell'>{$percentage}%</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th class="number-cell">CFA <?= number_format($totalYearlySales, 2) ?></th>
            <th class="number-cell">100%</th>
        </tr>
    </tfoot>
</table>
<?php endif; ?>
</div>
</div>
</div>
</div>
<!-- Table styling -->
<style>
.dashboard-tables {
    margin-top: 30px;
}

.table-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.table-container {
    flex: 1;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.table-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.table-body {
    padding: 0;
    overflow-x: auto;
}

.dashboard-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.dashboard-table th, 
.dashboard-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.dashboard-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.dashboard-table tr:last-child td {
    border-bottom: none;
}

.dashboard-table tbody tr:hover {
    background-color: #f5f8ff;
}

.dashboard-table tfoot {
    font-weight: 600;
    background-color: #f8f9fa;
}

.number-cell {
    text-align: right;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-pending {
    background-color: #fff8e1;
    color: #f57c00;
}

.status-processing {
    background-color: #e3f2fd;
    color: #1976d2;
}

.status-shipped {
    background-color: #e8f5e9;
    color: #388e3c;
}

.status-delivered {
    background-color: #e8f5e9;
    color: #388e3c;
}

.status-delivered {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-cancelled {
    background-color: #ffebee;
    color: #d32f2f;
}

.status-critical {
    background-color: #ffebee;
    color: #d32f2f;
}

.status-warning {
    background-color: #fff8e1;
    color: #f57c00;
}

.status-caution {
    background-color: #fffde7;
    color: #fbc02d;
}

.status-ok {
    background-color: #e8f5e9;
    color: #388e3c;
}

.positive-variation {
    color: #388e3c;
}

.negative-variation {
    color: #d32f2f;
}

.table-toggle {
    display: flex;
    gap: 5px;
}

.table-btn {
    background: #f5f5f5;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.table-btn.active {
    background: #4a6cf7;
    color: white;
}

@media (max-width: 1024px) {
    .table-row {
        flex-direction: column;
    }
    
    .table-container {
        margin-bottom: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle buttons for Sales Analysis Table
    const weeklyBtn = document.getElementById('weeklyBtn');
    const monthlyBtn = document.getElementById('monthlyBtn');
    const yearlyBtn = document.getElementById('yearlyBtn');
    
    const weeklySalesTable = document.getElementById('weeklySalesTable');
    const monthlySalesTable = document.getElementById('monthlySalesTable');
    const yearlySalesTable = document.getElementById('yearlySalesTable');

    if (weeklyBtn && monthlyBtn && yearlyBtn) {
        weeklyBtn.addEventListener('click', function() {
            setActiveButton(this);
            weeklySalesTable.style.display = 'table';
            monthlySalesTable.style.display = 'none';
            yearlySalesTable.style.display = 'none';
        });

        monthlyBtn.addEventListener('click', function() {
    setActiveButton(this);
    weeklySalesTable.style.display = 'none';
    monthlySalesTable.style.display = 'table';
    yearlySalesTable.style.display = 'none';
});

yearlyBtn.addEventListener('click', function() {
    setActiveButton(this);
    weeklySalesTable.style.display = 'none';
    monthlySalesTable.style.display = 'none';
    yearlySalesTable.style.display = 'table';
});
}

function setActiveButton(btn) {
    const buttons = document.querySelectorAll('.table-btn');
    if (buttons) {
        buttons.forEach(button => {
            button.classList.remove('active');
        });
        btn.classList.add('active');
    }
}
});
</script>