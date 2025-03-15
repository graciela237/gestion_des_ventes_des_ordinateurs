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
            'title' => 'Tableau de bord',
            'link' => 'dashboard1.php',
            'class' => 'load-page'
        ],
        'profile' => [
            'icon' => 'fas fa-user',
            'title' => 'Mon Profil',
            'link' => 'profile.php',
            'id' => 'profile-link',
            'class' => 'load-page'
        ],
    ],
 'admin' => [
    'user_management' => [
        'icon' => 'fas fa-users',
        'title' => 'Gestion des Utilisateurs',
        'link' => 'users1.php',
        'class' => 'load-page',
    ],
    'catalog_categories' => [
        'icon' => 'fas fa-book',
        'title' => 'Voir Catégories',
        'link' => 'categories.php',
        'class' => 'load-page',
    ],
    'catalog_products' => [
        'icon' => 'fas fa-box',
        'title' => 'Produits',
        'link' => 'products.php',
        'class' => 'load-page',
    ],
    'orders' => [
        'icon' => 'fas fa-shopping-cart',
        'title' => 'Commandes',
        'link' => 'orders.php',
        'class' => 'load-page',
    ],
    'shipments' => [
        'icon' => 'fas fa-truck',
        'title' => 'Expéditions',
        'link' => 'shipments.php',
        'class' => 'load-page',
    ],
    
    'suppliers' => [
        'icon' => 'fas fa-truck-loading',
        'title' => 'Fournisseurs',
        'link' => 'suppliers.php',
        'class' => 'load-page',
    ],
   
],

    'client' => [
        'shop' => ['icon' => 'fas fa-shopping-bag', 'title' => 'Boutique', 'link' => 'shop.php', 'class' => 'load-page'],
        'orders' => ['icon' => 'fas fa-shopping-cart', 'title' => 'Mes Commandes', 'link' => 'my_orders.php', 'class' => 'load-page'],
        'wishlist' => ['icon' => 'fas fa-heart', 'title' => 'Liste de Souhaits', 'link' => 'wishlist.php', 'class' => 'load-page'],
        'reviews' => ['icon' => 'fas fa-star', 'title' => 'Mes Avis', 'link' => 'my_reviews.php', 'class' => 'load-page'],
        'support' => ['icon' => 'fas fa-headset', 'title' => 'Support', 'link' => 'customer_support.php', 'class' => 'load-page']
    ],
   'vendeur' => [
        'dashboard' => ['icon' => 'fas fa-chart-line', 'title' => 'Tableau de bord', 'link' => 'vendeur_dashboard.php', 'class' => 'load-page'],
        'sales' => ['icon' => 'fas fa-cash-register', 'title' => 'Ventes', 'link' => 'sales.php', 'class' => 'load-page'],
        'customers' => ['icon' => 'fas fa-users', 'title' => 'Clients', 'link' => 'customers.php', 'class' => 'load-page'],
        'products' => ['icon' => 'fas fa-box', 'title' => 'Produits', 'link' => 'products.php', 'class' => 'load-page'],
        'orders' => ['icon' => 'fas fa-shopping-cart', 'title' => 'Commandes', 'link' => 'orders.php', 'class' => 'load-page'],
    ]
    'gestionnaire_stock' => [
        'inventory' => ['icon' => 'fas fa-archive', 'title' => 'Inventaire', 'link' => 'inventory.php', 'class' => 'load-page'],
        'suppliers' => ['icon' => 'fas fa-truck', 'title' => 'Fournisseurs', 'link' => 'suppliers.php', 'class' => 'load-page'],
        'stock_logs' => ['icon' => 'fas fa-clipboard-list', 'title' => 'Journaux de Stock', 'link' => 'stock_logs.php', 'class' => 'load-page'],
        'deliveries' => ['icon' => 'fas fa-truck-moving', 'title' => 'Livraisons', 'link' => 'deliveries.php', 'class' => 'load-page'],
        'low_stock' => ['icon' => 'fas fa-exclamation-triangle', 'title' => 'Alertes Stock Bas', 'link' => 'low_stock.php', 'class' => 'load-page']
    ],
    'responsable_financier' => [
        'transactions' => ['icon' => 'fas fa-exchange-alt', 'title' => 'Transactions', 'link' => 'transactions.php', 'class' => 'load-page'],
        'payments' => ['icon' => 'fas fa-credit-card', 'title' => 'Paiements', 'link' => 'payments.php', 'class' => 'load-page'],
        'invoices' => ['icon' => 'fas fa-file-invoice', 'title' => 'Factures', 'link' => 'invoices.php', 'class' => 'load-page'],
        'revenue' => ['icon' => 'fas fa-chart-line', 'title' => 'Revenus', 'link' => 'revenue.php', 'class' => 'load-page'],
        'expenses' => ['icon' => 'fas fa-money-bill-wave', 'title' => 'Dépenses', 'link' => 'expenses.php', 'class' => 'load-page'],
        'reports' => ['icon' => 'fas fa-chart-bar', 'title' => 'Rapports Financiers', 'link' => 'financial_reports.php', 'class' => 'load-page']
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
            <div class="user-name"><?= $_SESSION['user_name'] ?? 'Utilisateur' ?></div>
            <div class="user-role"><?= ucfirst($role) ?></div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($currentRoleItems as $key => $item): ?>
            <li class="sidebar-item <?= isset($item['isDropdown']) && $item['isDropdown'] ? 'dropdown' : '' ?>">
                <?php if (isset($item['isDropdown']) && $item['isDropdown']): ?>
                    <a href="#" class="dropdown-toggle">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span><?= $item['title'] ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($item['items'] as $subItem): ?>
                            <li class="dropdown-item">
                                <a href="<?= $subItem['link'] ?>" class="<?= $subItem['class'] ?? '' ?>">
                                    <span><?= $subItem['title'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <a href="<?= $item['link'] ?>" 
                        <?= isset($item['id']) ? 'id="'.$item['id'].'"' : '' ?> 
                        class="<?= $item['class'] ?? '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span><?= $item['title'] ?></span>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

        <li class="sidebar-item">
            <a href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
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
                    $("#dashboard-content").html("<p style='color: red;'>Erreur lors du chargement du contenu...</p>");
                }
            });
        }
        
        // Sidebar navigation (loads pages dynamically)
        $(".load-page").click(function(e) {
            e.preventDefault();
            var page = $(this).attr("href");
            loadPage(page);
            
            // Update active class
            $(".sidebar-item").removeClass("active");
            $(this).closest(".sidebar-item").addClass("active");
        });

        // Toggle sidebar for mobile screens
        $("#sidebar-toggle").click(function() {
            $("#sidebar").toggleClass("active");
            $("body").toggleClass("sidebar-open");
        });
        
        // Dropdown menu toggle
        $(".dropdown-toggle").click(function(e) {
            e.preventDefault();
            $(this).parent().toggleClass("open");
            $(this).next(".dropdown-menu").slideToggle(300);
            $(this).find(".dropdown-icon").toggleClass("fa-chevron-down fa-chevron-up");
        });
        
        // Set active class on page load
        var currentPage = window.location.pathname.split("/").pop();
        $(".sidebar-menu a[href='" + currentPage + "']").parent().addClass("active");
        
        // Open dropdown for active subitem
        if ($(".dropdown-menu a.active").length) {
            $(".dropdown-menu a.active").closest(".dropdown").addClass("open");
            $(".dropdown-menu a.active").closest(".dropdown-menu").show();
        }
    });
</script>

<style>
/* Sidebar Styles */
.sidebar {
    width: 260px;
    height: 100vh;
    background: linear-gradient(to bottom, #1a2433, #2c3e50);
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar-header {
    padding: 16px;
    text-align: center;
    background: #1a252f;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #ffffff;
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

.sidebar-item {
    position: relative;
    margin: 2px 0;
    transition: background 0.3s;
}

.sidebar-item a {
    text-decoration: none;
    color: #e6e6e6;
    display: flex;
    align-items: center;
    padding: 12px 16px;
    transition: all 0.2s ease;
    font-size: 14px;
}

.sidebar-item a i {
    margin-right: 12px;
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.sidebar-item span {
    display: inline-block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-item.active > a {
    background: rgba(52, 73, 94, 0.8);
    color: #ffffff;
    border-left: 4px solid #3498db;
}

.sidebar-item:hover > a {
    background: rgba(52, 73, 94, 0.6);
    color: #ffffff;
}

.sidebar-user {
    display: flex;
    align-items: center;
    padding: 16px;
    background: #1a252f;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-user .user-image {
    font-size: 32px;
    margin-right: 12px;
    color: #3498db;
}

.sidebar-user .user-info {
    flex-grow: 1;
}

.sidebar-user .user-name {
    font-size: 16px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #ffffff;
}

.sidebar-user .user-role {
    font-size: 12px;
    color: #bdc3c7;
}

/* Dropdown styles */
.dropdown-toggle {
    position: relative;
    justify-content: space-between;
}

.dropdown-icon {
    position: absolute;
    right: 15px;
    transition: transform 0.3s;
}

.dropdown-menu {
    display: none;
    background: rgba(26, 37, 47, 0.9);
    list-style: none;
    padding: 5px 0;
    margin: 0;
}

.dropdown-item a {
    padding: 10px 15px 10px 45px;
    font-size: 13px;
}

.dropdown.open > a {
    background: rgba(52, 73, 94, 0.8);
    color: #ffffff;
}

/* Custom scrollbar for sidebar menu */
.sidebar-menu::-webkit-scrollbar {
    width: 5px;
}

.sidebar-menu::-webkit-scrollbar-track {
    background: rgba(26, 37, 47, 0.5);
}

.sidebar-menu::-webkit-scrollbar-thumb {
    background: rgba(52, 73, 94, 0.8);
    border-radius: 5px;
}

.sidebar-menu::-webkit-scrollbar-thumb:hover {
    background: #3498db;
}

/* For mobile screens */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: block;
    }
    
    body.sidebar-open {
        overflow: hidden;
    }
    
    .sidebar-header {
        padding: 12px;
    }
}

/* Active item highlighting */
.sidebar-item.active > a {
    background: rgba(52, 152, 219, 0.3);
    color: #ffffff;
    border-left: 4px solid #3498db;
}

/* Logout button special styling */
.sidebar-item:last-child {
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-item:last-child a {
    color: #e74c3c;
}

.sidebar-item:last-child:hover a {
    background: rgba(231, 76, 60, 0.2);
}
</style>