<?php
// Start session to access user data
session_start();

// Check if user is logged in and has vendeur role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    // Redirect to login page if not logged in or not a salesperson
    header("Location: ../login.php");
    exit();
}

// Set page title
$pageTitle = "Sales Dashboard";
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
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Total Sales</h4>
                        <h2>56</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>New Customers</h4>
                        <h2>12</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Pending Orders</h4>
                        <h2>8</h2>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon red">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-card-info">
                        <h4>Customer Reviews</h4>
                        <h2>24</h2>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Recent Orders</h3>
                    <a href="orders.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="section-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Products</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#2458</td>
                                <td>Client User</td>
                                <td>Laptop Pro X1</td>
                                <td>₦45,999.99</td>
                                <td>Today, 9:30 AM</td>
                                <td><span class="status completed">Completed</span></td>
                                <td>
                                    <a href="order-detail.php?id=2458" class="btn-small">View</a>
                                </td>
                            </tr>
                            <tr>
                                <td>#2462</td>
                                <td>John Doe</td>
                                <td>Ultrabook Air</td>
                                <td>₦32,499.99</td>
                                <td>Today, 10:45 AM</td>
                                <td><span class="status pending">Pending</span></td>
                                <td>
                                    <a href="order-detail.php?id=2462" class="btn-small">Process</a>
                                </td>
                            </tr>
                            <tr>
                                <td>#2465</td>
                                <td>Mary Smith</td>
                                <td>Gaming PC Elite</td>
                                <td>₦57,999.99</td>
                                <td>Today, 11:15 AM</td>
                                <td><span class="status processing">Processing</span></td>
                                <td>
                                    <a href="order-detail.php?id=2465" class="btn-small">Update</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Top Selling Products Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Top Selling Products</h3>
                    <a href="products.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="section-content">
                    <div class="product-list">
                        <div class="product-item">
                            <div class="product-image">
                                <img src="../assets/images/laptop1.jpeg" alt="Laptop Pro X1">
                            </div>
                            <div class="product-details">
                                <h4>Laptop Pro X1</h4>
                                <div class="product-meta">
                                    <span class="product-price">₦45,999.99</span>
                                    <span class="product-sales">50 units sold</span>
                                </div>
                                <div class="product-stock">
                                    <div class="stock-bar">
                                        <div class="stock-level" style="width: 75%;"></div>
                                    </div>
                                    <span>75% in stock</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-image">
                                <img src="../assets/images/gamingpc1.jpeg" alt="Gaming PC Elite">
                            </div>
                            <div class="product-details">
                                <h4>Gaming PC Elite</h4>
                                <div class="product-meta">
                                    <span class="product-price">₦57,999.99</span>
                                    <span class="product-sales">30 units sold</span>
                                </div>
                                <div class="product-stock">
                                    <div class="stock-bar">
                                        <div class="stock-level" style="width: 60%;"></div>
                                    </div>
                                    <span>60% in stock</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-item">
                            <div class="product-image">
                                <img src="../assets/images/Ultrabook1.jpeg" alt="Ultrabook Air">
                            </div>
                            <div class="product-details">
                                <h4>Ultrabook Air</h4>
                                <div class="product-meta">
                                    <span class="product-price">₦32,499.99</span>
                                    <span class="product-sales">40 units sold</span>
                                </div>
                                <div class="product-stock">
                                    <div class="stock-bar">
                                        <div class="stock-level" style="width: 85%;"></div>
                                    </div>
                                    <span>85% in stock</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Support Tickets -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3>Customer Support Tickets</h3>
                    <a href="support.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="section-content">
                    <div class="ticket-list">
                        <div class="ticket-item">
                            <div class="ticket-icon red">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="ticket-details">
                                <p><strong>Order Issue</strong> - Order #2442 missing items</p>
                                <span class="ticket-customer">Client User</span>
                                <span class="ticket-time">2 hours ago</span>
                                <span class="ticket-status urgent">Urgent</span>
                            </div>
                            <div class="ticket-action">
                                <a href="ticket-detail.php?id=1" class="btn-small">Respond</a>
                            </div>
                        </div>
                        
                        <div class="ticket-item">
                            <div class="ticket-icon orange">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="ticket-details">
                                <p><strong>Product Inquiry</strong> - Gaming PC Elite specifications</p>
                                <span class="ticket-customer">John Doe</span>
                                <span class="ticket-time">4 hours ago</span>
                                <span class="ticket-status medium">Medium</span>
                            </div>
                            <div class="ticket-action">
                                <a href="ticket-detail.php?id=2" class="btn-small">Respond</a>
                            </div>
                        </div>
                        
                        <div class="ticket-item">
                            <div class="ticket-icon blue">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="ticket-details">
                                <p><strong>Return Request</strong> - Mechanical Keyboard Elite</p>
                                <span class="ticket-customer">Mary Smith</span>
                                <span class="ticket-time">Yesterday</span>
                                <span class="ticket-status low">Low</span>
                            </div>
                            <div class="ticket-action">
                                <a href="ticket-detail.php?id=3" class="btn-small">Respond</a>
                            </div>
                        </div>
                    </div>
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