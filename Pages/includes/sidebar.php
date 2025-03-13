<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if the current page matches the given link
function isActive($link) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $link) ? 'active' : '';
}

// Define sidebar items based on user role
$sidebarItems = [
    'default' => [
        'dashboard' => [
            'icon' => 'fas fa-tachometer-alt',
            'title' => 'Dashboard',
            'link' => 'dashboard1.php',
            'class' => 'load-page'
        ],
        'profile' => [
            'icon' => 'fas fa-user',
            'title' => 'My Profile',
            'link' => 'profile.php',
            'id' => 'profile-link',
            'class' => 'load-page'
        ],
        'notifications' => [
            'icon' => 'fas fa-bell',
            'title' => 'Notifications',
            'link' => 'notifications.php',
            'class' => 'load-page'
        ]
    ],
    'admin' => [
        'users' => ['icon' => 'fas fa-users', 'title' => 'User Management', 'link' => 'users.php', 'class' => 'load-page'],
        'categories' => ['icon' => 'fas fa-th-list', 'title' => 'Categories', 'link' => 'categories.php', 'class' => 'load-page'],
        'products' => ['icon' => 'fas fa-box', 'title' => 'Products', 'link' => 'dashboard1.php', 'class' => 'load-page'],
        'orders' => ['icon' => 'fas fa-shopping-cart', 'title' => 'Orders', 'link' => 'orders.php', 'class' => 'load-page'],
        'suppliers' => ['icon' => 'fas fa-truck', 'title' => 'Suppliers', 'link' => 'suppliers.php', 'class' => 'load-page'],
        'inventory' => ['icon' => 'fas fa-archive', 'title' => 'Inventory', 'link' => 'inventory.php', 'class' => 'load-page'],
        'deliveries' => ['icon' => 'fas fa-truck-moving', 'title' => 'Deliveries', 'link' => 'deliveries.php', 'class' => 'load-page'],
        'reviews' => ['icon' => 'fas fa-star', 'title' => 'Reviews', 'link' => 'reviews.php', 'class' => 'load-page'],
        'coupons' => ['icon' => 'fas fa-tags', 'title' => 'Coupons', 'link' => 'coupons.php', 'class' => 'load-page'],
        'reports' => ['icon' => 'fas fa-chart-bar', 'title' => 'Reports', 'link' => 'reports.php', 'class' => 'load-page'],
        'financials' => ['icon' => 'fas fa-dollar-sign', 'title' => 'Financials', 'link' => 'financials.php', 'class' => 'load-page'],
        'support_tickets' => ['icon' => 'fas fa-headphones', 'title' => 'Support Tickets', 'link' => 'support_tickets.php', 'class' => 'load-page'],
        'system_logs' => ['icon' => 'fas fa-database', 'title' => 'System Logs', 'link' => 'system_logs.php', 'class' => 'load-page'],
        'security' => ['icon' => 'fas fa-shield-alt', 'title' => 'Security', 'link' => 'security.php', 'class' => 'load-page'],
        'settings' => ['icon' => 'fas fa-cog', 'title' => 'Settings', 'link' => 'settings.php', 'class' => 'load-page']
    ],
    'client' => [
        'shop' => ['icon' => 'fas fa-shopping-bag', 'title' => 'Shop', 'link' => 'shop.php', 'class' => 'load-page'],
        'orders' => ['icon' => 'fas fa-shopping-cart', 'title' => 'My Orders', 'link' => 'my_orders.php', 'class' => 'load-page'],
        'wishlist' => ['icon' => 'fas fa-heart', 'title' => 'Wishlist', 'link' => 'wishlist.php', 'class' => 'load-page'],
        'reviews' => ['icon' => 'fas fa-star', 'title' => 'My Reviews', 'link' => 'my_reviews.php', 'class' => 'load-page'],
        'support' => ['icon' => 'fas fa-headset', 'title' => 'Support', 'link' => 'customer_support.php', 'class' => 'load-page']
    ],
    'vendeur' => [
        'sales' => ['icon' => 'fas fa-cash-register', 'title' => 'Sales', 'link' => 'sales.php', 'class' => 'load-page'],
        'customers' => ['icon' => 'fas fa-users', 'title' => 'Customers', 'link' => 'customers.php', 'class' => 'load-page'],
        'products' => ['icon' => 'fas fa-box', 'title' => 'Products', 'link' => 'products.php', 'class' => 'load-page'],
        'orders' => ['icon' => 'fas fa-shopping-cart', 'title' => 'Orders', 'link' => 'orders.php', 'class' => 'load-page'],
        'promotions' => ['icon' => 'fas fa-percent', 'title' => 'Promotions', 'link' => 'promotions.php', 'class' => 'load-page']
    ],
    'gestionnaire_stock' => [
        'inventory' => ['icon' => 'fas fa-archive', 'title' => 'Inventory', 'link' => 'inventory.php', 'class' => 'load-page'],
        'suppliers' => ['icon' => 'fas fa-truck', 'title' => 'Suppliers', 'link' => 'suppliers.php', 'class' => 'load-page'],
        'stock_logs' => ['icon' => 'fas fa-clipboard-list', 'title' => 'Stock Logs', 'link' => 'stock_logs.php', 'class' => 'load-page'],
        'deliveries' => ['icon' => 'fas fa-truck-moving', 'title' => 'Deliveries', 'link' => 'deliveries.php', 'class' => 'load-page'],
        'low_stock' => ['icon' => 'fas fa-exclamation-triangle', 'title' => 'Low Stock Alerts', 'link' => 'low_stock.php', 'class' => 'load-page']
    ],
    'responsable_financier' => [
        'transactions' => ['icon' => 'fas fa-exchange-alt', 'title' => 'Transactions', 'link' => 'transactions.php', 'class' => 'load-page'],
        'payments' => ['icon' => 'fas fa-credit-card', 'title' => 'Payments', 'link' => 'payments.php', 'class' => 'load-page'],
        'invoices' => ['icon' => 'fas fa-file-invoice', 'title' => 'Invoices', 'link' => 'invoices.php', 'class' => 'load-page'],
        'revenue' => ['icon' => 'fas fa-chart-line', 'title' => 'Revenue', 'link' => 'revenue.php', 'class' => 'load-page'],
        'expenses' => ['icon' => 'fas fa-money-bill-wave', 'title' => 'Expenses', 'link' => 'expenses.php', 'class' => 'load-page'],
        'reports' => ['icon' => 'fas fa-chart-bar', 'title' => 'Financial Reports', 'link' => 'financial_reports.php', 'class' => 'load-page']
    ]
];

// Get current user role from session
$role = isset($_SESSION['user_role']) ? strtolower($_SESSION['user_role']) : 'default';

// Get sidebar items for the current role
$currentRoleItems = $sidebarItems['default']; // Always include default items
if (isset($sidebarItems[$role]) && $role !== 'default') {
    $currentRoleItems = array_merge($currentRoleItems, $sidebarItems[$role]);
}

// Site name
$siteName = "TechPro Ecommerce";
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><?= $siteName ?></h3>
        <div class="sidebar-toggle" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-image">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
            <div class="user-name"><?= $_SESSION['user_name'] ?? 'User' ?></div>
            <div class="user-role"><?= ucfirst($role) ?></div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($currentRoleItems as $key => $item): ?>
            <li class="<?= isActive($item['link']) ?>">
                <a href="<?= $item['link'] ?>" 
                   <?= isset($item['id']) ? 'id="'.$item['id'].'"' : '' ?> 
                   class="<?= $item['class'] ?? '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= $item['title'] ?></span>
                </a>
            </li>
        <?php endforeach; ?>

        <li>
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Function to load content dynamically inside the dashboard
        function loadPage(page) {
            $.ajax({
                url: page,
                type: "GET",
                success: function(data) {
                    $("#dashboard-content").html(data);
                },
                error: function() {
                    $("#dashboard-content").html("<p style='color: red;'>Error loading content...</p>");
                }
            });
        }
        // Sidebar navigation (loads pages dynamically)
        $(".load-page").click(function(e) {
            e.preventDefault();
            var page = $(this).attr("href");
            loadPage(page);
            
            // Update active class
            $(".sidebar-menu li").removeClass("active");
            $(this).parent().addClass("active");
        });

        // Toggle sidebar for mobile screens
        $("#sidebar-toggle").click(function() {
            $("#sidebar").toggleClass("active");
        });
    });
</script>

<style>
/* Sidebar Styles */
.sidebar {
    width: 250px;
    height: 100vh;
    background: #2c3e50;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    transition: 0.3s;
    z-index: 1000;
}

.sidebar-header {
    padding: 15px;
    text-align: center;
    background: #1a252f;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-toggle {
    display: none;
    cursor: pointer;
    padding: 5px;
}

.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
    overflow-y: auto;
    max-height: calc(100vh - 150px);
}

.sidebar-menu li {
    transition: background 0.3s;
}

.sidebar-menu li a {
    text-decoration: none;
    color: white;
    display: flex;
    align-items: center;
    padding: 12px 15px;
}

.sidebar-menu li a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

.sidebar-menu li.active {
    background: #34495e;
    border-left: 4px solid #3498db;
}

.sidebar-menu li:hover {
    background: #34495e;
}

.sidebar-user {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #1a252f;
    border-bottom: 1px solid #34495e;
}

.sidebar-user .user-image {
    font-size: 30px;
    margin-right: 10px;
}

.sidebar-user .user-info {
    flex-grow: 1;
}

.sidebar-user .user-name {
    font-size: 16px;
    font-weight: bold;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user .user-role {
    font-size: 12px;
    color: #bdc3c7;
}

/* Custom scrollbar for sidebar menu */
.sidebar-menu::-webkit-scrollbar {
    width: 5px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: #1a252f;
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: #34495e;
    border-radius: 5px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 0;
        overflow: hidden;
    }
    
    .sidebar.active {
        width: 250px;
    }
    
    .sidebar-toggle {
        display: block;
    }
    
    .dashboard-content {
        margin-left: 0;
    }
}
</style>