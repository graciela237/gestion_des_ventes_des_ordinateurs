<?php
require_once 'DatabaseConnection/db_config.php';

function getProducts($featured = false, $limit = null) {
    global $pdo;
    
    $query = "SELECT p.*, c.category_name 
              FROM products p
              JOIN product_categories c ON p.category_id = c.category_id";
    
    if ($featured) {
        $query .= " WHERE p.is_featured = TRUE";
    }
    
    if ($limit !== null) {
        $query .= " LIMIT :limit";
    }
    
    $stmt = $pdo->prepare($query);
    
    if ($limit !== null) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' FCFA';
}
?>