<?php
/**
 * Product filtering functions
 * This file contains functions to handle product filtering
 */

/**
 * Get filtered products based on search criteria
 * 
 * @param PDO $pdo Database connection
 * @param array $filters Array containing filter parameters
 * @return array Filtered products
 */
function getFilteredProducts($pdo, $filters = []) {
    // Default values
    $searchTerm = $filters['search'] ?? '';
    $maxPrice = $filters['max_price'] ?? PHP_INT_MAX;
    $category = $filters['category'] ?? '';
    $sortBy = $filters['sort_by'] ?? 'price';
    $sortOrder = $filters['sort_order'] ?? 'ASC';
    
    // Build the SQL query
    $sql = "SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.category_id
            WHERE 1=1";
    
    $params = [];
    
    // Add search condition if search term is provided
    if (!empty($searchTerm)) {
        $sql .= " AND (p.name LIKE :search OR p.specifications LIKE :search)";
        $params[':search'] = "%$searchTerm%";
    }
    
    // Add price condition
    if ($maxPrice < PHP_INT_MAX) {
        $sql .= " AND p.price <= :price";
        $params[':price'] = $maxPrice;
    }
    
    // Add category condition
    if (!empty($category)) {
        $sql .= " AND c.category_name = :category";
        $params[':category'] = $category;
    }
    
    // Add sorting
    $sql .= " ORDER BY p.$sortBy $sortOrder";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Return results
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process products (adding badges, etc.)
    foreach ($products as &$product) {
        // Set default badge
        $product['badge'] = '';
        
        // Set badge based on certain conditions
        if (isset($product['is_new']) && $product['is_new']) {
            $product['badge'] = 'Nouveau';
        } elseif (isset($product['original_price']) && $product['original_price'] > $product['price']) {
            $discount = round(($product['original_price'] - $product['price']) / $product['original_price'] * 100);
            $product['badge'] = "-$discount%";
        }
    }
    
    return $products;
}

/**
 * Format price with FCFA currency
 * 
 * @param float $price The price to format
 * @return string Formatted price
 */
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

/**
 * Apply AJAX filtering and return JSON
 * Use for asynchronous filtering without page reload
 */
function handleAjaxFilter($pdo) {
    if (isset($_GET['ajax_filter'])) {
        // Get filter parameters from request
        $filters = [
            'search' => $_GET['search'] ?? '',
            'max_price' => $_GET['max_price'] ?? PHP_INT_MAX,
            'category' => $_GET['category'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'price',
            'sort_order' => $_GET['sort_order'] ?? 'ASC'
        ];
        
        // Get filtered products
        $products = getFilteredProducts($pdo, $filters);
        
        // Return as JSON
        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    }
}
?>