<?php
// Start session to access user data
session_start();

// Check if user is logged in and has finance role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 5) {
    // Redirect to login page if not logged in or not finance manager
    header("Location: ../login.php");
    exit();
}

// Set page title
$pageTitle = "Finance Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - TechPro Ecommerce</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <!-- Include the sidebar -->
    <?php include "../includes/sidebar.php"; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><?= $pageTitle ?></h1>
            <div class="breadcrumb">
                <a href="dashboard.php">Home</a> / <span>Dashboard</span>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards Row -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Total Revenue</h4>
                        <h2>₦2.4M</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Transactions</h4>
                        <h2>187</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Pending Payments</h4>
                        <h2>24</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon red">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Refunds</h4>
                        <h2>5</h2>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Recent Transactions</h3>
                    <a href="transactions.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="section-content">
                    <div class="transaction-list">
                        <div class="transaction-item">
                            <div class="transaction-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="transaction-details">
                                <p>Payment verified: <strong>Order #2458</strong></p>
                                <span class="transaction-amount">₦45,999.99</span>
                                <span class="transaction-time">2 hours ago</span>
                            </div>
                        </div>
                        
                        <div class="transaction-item">
                            <div class="transaction-icon orange">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="transaction-details">
                                <p>Pending verification: <strong>Order #2462</strong></p>
                                <span class="transaction-amount">₦32,499.99</span>
                                <span class="transaction-time">3 hours ago</span>
                            </div>
                        </div>
                        
                        <div class="transaction-item">
                            <div class="transaction-icon blue">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="transaction-details">
                                <p>New order payment: <strong>Order #2465</strong></p>
                                <span class="transaction-amount">₦57,999.99</span>
                                <span class="transaction-time">5 hours ago</span>
                            </div>
                        </div>
                        
                        <div class="transaction-item">
                            <div class="transaction-icon red">
                                <i class="fas fa-undo-alt"></i>
                            </div>
                            <div class="transaction-details">
                                <p>Refund processed: <strong>Order #2442</strong></p>
                                <span class="transaction-amount">-₦4,999.99</span>
                                <span class="transaction-time">Yesterday</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Revenue Chart -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Monthly Revenue</h3>
                </div>
                <div class="section-content">
                    <div class="chart-container">
                        <!-- Placeholder for chart - would use Chart.js or similar in production -->
                        <div class="chart-placeholder">
                            <div class="chart-bar" style="height: 60%;" title="Jan: ₦1.2M"></div>
                            <div class="chart-bar" style="height: 75%;" title="Feb: ₦1.5M"></div>
                            <div class="chart-bar" style="height: 65%;" title="Mar: ₦1.3M"></div>
                            <div class="chart-bar" style="height: 90%;" title="Apr: ₦1.8M"></div>
                            <div class="chart-bar" style="height: 85%;" title="May: ₦1.7M"></div>
                            <div class="chart-bar active" style="height: 100%;" title="Jun: ₦2.0M"></div>
                        </div>
                        <div class="chart-legend">
                            <div>Jan</div>
                            <div>Feb</div>
                            <div>Mar</div>
                            <div>Apr</div>
                            <div>May</div>
                            <div>Jun</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Verifications Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Pending Verifications</h3>
                    <a href="verifications.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="section-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#2462</td>
                                <td>Client User</td>
                                <td>₦32,499.99</td>
                                <td>Today, 10:45 AM</td>
                                <td><span class="status pending">Pending</span></td>
                                <td>
                                    <a href="verify.php?id=2462" class="btn-small">Verify</a>
                                </td>
                            </tr>
                            <tr>
                                <td>#2464</td>
                                <td>John Doe</td>
                                <td>₦24,999.99</td>
                                <td>Today, 11:30 AM</td>
                                <td><span class="status pending">Pending</span></td>
                                <td>
                                    <a href="verify.php?id=2464" class="btn-small">Verify</a>
                                </td>
                            </tr>
                            <tr>
                                <td>#2466</td>
                                <td>Mary Smith</td>
                                <td>₦57,999.99</td>
                                <td>Today, 12:15 PM</td>
                                <td><span class="status pending">Pending</span></td>
                                <td>
                                    <a href="verify.php?id=2466" class="btn-small">Verify</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer>
            <p>&copy; <?= date('Y') ?> TechPro Ecommerce. All Rights Reserved.</p>
        </footer>
    </div>
    
    <!-- JavaScript Files -->
    <script src="../assets/js/sidebar.js"></script>
</body>
</html>