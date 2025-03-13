<?php
// This function will return the appropriate sidebar menu based on user role
function generateSidebar($userRole) {
    // Define sidebar menu items for each role
    $menuItems = [
        // Admin has access to everything
        'admin' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'admin/dashboard.php'],
            ['icon' => 'users', 'title' => 'Users Management', 'link' => 'admin/users.php'],
            ['icon' => 'package', 'title' => 'Products', 'link' => 'admin/products.php'],
            ['icon' => 'layers', 'title' => 'Categories', 'link' => 'admin/categories.php'],
            ['icon' => 'truck', 'title' => 'Suppliers', 'link' => 'admin/suppliers.php'],
            ['icon' => 'shopping-cart', 'title' => 'Orders', 'link' => 'admin/orders.php'],
            ['icon' => 'dollar-sign', 'title' => 'Finances', 'link' => 'admin/finances.php'],
            ['icon' => 'archive', 'title' => 'Inventory', 'link' => 'admin/inventory.php'],
            ['icon' => 'truck', 'title' => 'Deliveries', 'link' => 'admin/deliveries.php'],
            ['icon' => 'headphones', 'title' => 'Support Tickets', 'link' => 'admin/tickets.php'],
            ['icon' => 'tag', 'title' => 'Coupons', 'link' => 'admin/coupons.php'],
            ['icon' => 'star', 'title' => 'Reviews', 'link' => 'admin/reviews.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'admin/notifications.php'],
            ['icon' => 'settings', 'title' => 'Settings', 'link' => 'admin/settings.php'],
        ],
        
        // Super Admin has extended privileges
        'super_admin' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'admin/dashboard.php'],
            ['icon' => 'users', 'title' => 'Users Management', 'link' => 'admin/users.php'],
            ['icon' => 'package', 'title' => 'Products', 'link' => 'admin/products.php'],
            ['icon' => 'layers', 'title' => 'Categories', 'link' => 'admin/categories.php'],
            ['icon' => 'truck', 'title' => 'Suppliers', 'link' => 'admin/suppliers.php'],
            ['icon' => 'shopping-cart', 'title' => 'Orders', 'link' => 'admin/orders.php'],
            ['icon' => 'dollar-sign', 'title' => 'Finances', 'link' => 'admin/finances.php'],
            ['icon' => 'archive', 'title' => 'Inventory', 'link' => 'admin/inventory.php'],
            ['icon' => 'truck', 'title' => 'Deliveries', 'link' => 'admin/deliveries.php'],
            ['icon' => 'headphones', 'title' => 'Support Tickets', 'link' => 'admin/tickets.php'],
            ['icon' => 'tag', 'title' => 'Coupons', 'link' => 'admin/coupons.php'],
            ['icon' => 'star', 'title' => 'Reviews', 'link' => 'admin/reviews.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'admin/notifications.php'],
            ['icon' => 'settings', 'title' => 'Settings', 'link' => 'admin/settings.php'],
            ['icon' => 'database', 'title' => 'System Logs', 'link' => 'admin/logs.php'],
            ['icon' => 'shield', 'title' => 'Security', 'link' => 'admin/security.php'],
        ],
        
        // Client has no dashboard as per your requirements
        'client' => [],
        
        // Vendeur (Salesperson)
        'vendeur' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'vendeur/dashboard.php'],
            ['icon' => 'package', 'title' => 'Products', 'link' => 'vendeur/products.php'],
            ['icon' => 'shopping-cart', 'title' => 'Orders', 'link' => 'vendeur/orders.php'],
            ['icon' => 'users', 'title' => 'Customers', 'link' => 'vendeur/customers.php'],
            ['icon' => 'tag', 'title' => 'Coupons', 'link' => 'vendeur/coupons.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'vendeur/notifications.php'],
        ],
        
        // Gestionnaire Stock (Stock Manager)
        'gestionnaire_stock' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'stock/dashboard.php'],
            ['icon' => 'package', 'title' => 'Products', 'link' => 'stock/products.php'],
            ['icon' => 'archive', 'title' => 'Inventory', 'link' => 'stock/inventory.php'],
            ['icon' => 'truck', 'title' => 'Suppliers', 'link' => 'stock/suppliers.php'],
            ['icon' => 'refresh-cw', 'title' => 'Stock History', 'link' => 'stock/history.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'stock/notifications.php'],
        ],
        
        // Fournisseur (Supplier)
        'fournisseur' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'fournisseur/dashboard.php'],
            ['icon' => 'package', 'title' => 'My Products', 'link' => 'fournisseur/products.php'],
            ['icon' => 'shopping-bag', 'title' => 'Orders', 'link' => 'fournisseur/orders.php'],
            ['icon' => 'truck', 'title' => 'Shipments', 'link' => 'fournisseur/shipments.php'],
            ['icon' => 'file-text', 'title' => 'Invoices', 'link' => 'fournisseur/invoices.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'fournisseur/notifications.php'],
        ],
        
        // Responsable Financier (Finance Manager)
        'responsable_financier' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'finance/dashboard.php'],
            ['icon' => 'dollar-sign', 'title' => 'Transactions', 'link' => 'finance/transactions.php'],
            ['icon' => 'check-square', 'title' => 'Payment Verifications', 'link' => 'finance/verifications.php'],
            ['icon' => 'shopping-cart', 'title' => 'Orders', 'link' => 'finance/orders.php'],
            ['icon' => 'pie-chart', 'title' => 'Sales Reports', 'link' => 'finance/reports.php'],
            ['icon' => 'file-text', 'title' => 'Invoices', 'link' => 'finance/invoices.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'finance/notifications.php'],
        ],
        
        // Livreur (Delivery Person)
        'livreur' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'delivery/dashboard.php'],
            ['icon' => 'truck', 'title' => 'My Deliveries', 'link' => 'delivery/deliveries.php'],
            ['icon' => 'map', 'title' => 'Routes', 'link' => 'delivery/routes.php'],
            ['icon' => 'check-square', 'title' => 'Completed Deliveries', 'link' => 'delivery/completed.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'delivery/notifications.php'],
        ],
        
        // Support Client (Customer Support)
        'support_client' => [
            ['icon' => 'grid', 'title' => 'Dashboard', 'link' => 'support/dashboard.php'],
            ['icon' => 'headphones', 'title' => 'Support Tickets', 'link' => 'support/tickets.php'],
            ['icon' => 'users', 'title' => 'Customers', 'link' => 'support/customers.php'],
            ['icon' => 'shopping-cart', 'title' => 'Orders', 'link' => 'support/orders.php'],
            ['icon' => 'message-square', 'title' => 'Messages', 'link' => 'support/messages.php'],
            ['icon' => 'bell', 'title' => 'Notifications', 'link' => 'support/notifications.php'],
        ],
    ];
    
    // Return the appropriate menu for the role
    return isset($menuItems[$userRole]) ? $menuItems[$userRole] : [];
}

// Function to render the sidebar HTML
function renderSidebar($userRole, $userName, $userEmail) {
    $menuItems = generateSidebar($userRole);
    
    // If it's a client or empty role, don't render a sidebar
    if (empty($menuItems) || $userRole === 'client') {
        return '';
    }
    
    // Start building the sidebar HTML
    $html = '<div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="assets/images/logo.png" alt="TechPro Logo">
            </div>
            <div class="user-info">
                <h5>' . htmlspecialchars($userName) . '</h5>
                <p>' . htmlspecialchars($userEmail) . '</p>
                <span class="role-badge">' . ucfirst(htmlspecialchars($userRole)) . '</span>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul>';
    
    // Add menu items
    foreach ($menuItems as $item) {
        $html .= '<li>
                <a href="' . htmlspecialchars($item['link']) . '">
                    <i data-feather="' . htmlspecialchars($item['icon']) . '"></i>
                    <span>' . htmlspecialchars($item['title']) . '</span>
                </a>
            </li>';
    }
    
    // Close the sidebar HTML
    $html .= '</ul>
        </div>
        <div class="sidebar-footer">
            <a href="profile.php"><i data-feather="user"></i> Profile</a>
            <a href="logout.php"><i data-feather="log-out"></i> Logout</a>
        </div>
    </div>';
    
    return $html;
}

// Example usage in a page
function displaySidebar() {
    // Get user data from session
    $userId = $_SESSION['user_id'] ?? null;
    
    // If no user is logged in, return nothing
    if (!$userId) {
        return '';
    }
    
    // Get user data from database
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=techpro_ecommerce', 'username', 'password');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare('
            SELECT u.first_name, u.last_name, u.email, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.user_id = ?
        ');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $userName = $user['first_name'] . ' ' . $user['last_name'];
            $userEmail = $user['email'];
            $userRole = $user['role_name'];
            
            return renderSidebar($userRole, $userName, $userEmail);
        }
    } catch (PDOException $e) {
        // Log error
        error_log('Database error: ' . $e->getMessage());
        return '<div class="error">Error loading user data</div>';
    }
    
    return '';
}
?>