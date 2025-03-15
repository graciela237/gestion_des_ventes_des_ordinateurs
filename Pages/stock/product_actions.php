<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Function to handle image upload
function handleImageUpload($file) {
    // Define the target directory relative to the document root
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/images/";
    $web_path = "/images/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename
    $filename = uniqid() . "_" . basename($file["name"]);
    $target_file = $target_dir . $filename;
    $web_accessible_path = $web_path . $filename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "Le fichier n'est pas une image."];
    }
    
    // Check file size (limit to 5MB)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "Désolé, votre fichier est trop volumineux."];
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return ["success" => false, "message" => "Désolé, seuls les fichiers JPG, JPEG, PNG & GIF sont autorisés."];
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Make sure file permissions are set correctly
        chmod($target_file, 0644);
        return ["success" => true, "path" => $web_accessible_path];
    } else {
        return ["success" => false, "message" => "Désolé, une erreur s'est produite lors de l'envoi de votre fichier."];
    }
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? sanitize($_POST['action']) : '';
    
    switch ($action) {
        case 'add':
            addProduct();
            break;
        
        case 'edit':
            editProduct();
            break;
        
        case 'delete':
            deleteProduct();
            break;
        
        default:
            echo json_encode(["success" => false, "message" => "Action non reconnue"]);
            break;
    }
}

// Function to add a new product
function addProduct() {
    global $conn;
    
    // Sanitize inputs
    $name = sanitize($_POST['name']);
    $category_id = sanitize($_POST['category_id']);
    $price = sanitize($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? sanitize($_POST['original_price']) : "NULL";
    $stock_quantity = sanitize($_POST['stock_quantity']);
    $description = sanitize($_POST['description']);
    $specifications = sanitize($_POST['specifications']);
    $warranty_period = sanitize($_POST['warranty_period']);
    $return_policy = sanitize($_POST['return_policy']);
    $badge = sanitize($_POST['badge']);
    $supplier_id = !empty($_POST['supplier_id']) ? sanitize($_POST['supplier_id']) : "NULL";
    $low_stock_threshold = !empty($_POST['low_stock_threshold']) ? sanitize($_POST['low_stock_threshold']) : 5;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle image upload if present
    $image_path = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload = handleImageUpload($_FILES['image']);
        if($upload['success']) {
            $image_path = $upload['path'];
        } else {
            echo json_encode(["success" => false, "message" => $upload['message']]);
            return;
        }
    }
    
    // Prepare SQL query
    if($original_price === "NULL") {
        $original_price_sql = "NULL";
    } else {
        $original_price_sql = "'$original_price'";
    }
    
    if($supplier_id === "NULL") {
        $supplier_id_sql = "NULL";
    } else {
        $supplier_id_sql = "'$supplier_id'";
    }
    
    $sql = "INSERT INTO products (
                category_id, name, description, specifications, price, 
                original_price, image_path, badge, stock_quantity, 
                is_featured, low_stock_threshold, supplier_id, 
                warranty_period, return_policy
            ) VALUES (
                '$category_id', '$name', '$description', '$specifications', 
                '$price', $original_price_sql, '$image_path', '$badge', 
                '$stock_quantity', '$is_featured', '$low_stock_threshold', 
                $supplier_id_sql, '$warranty_period', '$return_policy'
            )";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Produit ajouté avec succès"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur: " . $conn->error]);
    }
}

// Function to edit an existing product
// Function to edit an existing product
function editProduct() {
    global $conn;
    
    // Sanitize inputs
    $product_id = sanitize($_POST['product_id']);
    $name = sanitize($_POST['name']);
    $category_id = sanitize($_POST['category_id']);
    $price = sanitize($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? sanitize($_POST['original_price']) : "NULL";
    $stock_quantity = sanitize($_POST['stock_quantity']);
    $description = sanitize($_POST['description']);
    $specifications = sanitize($_POST['specifications']);
    $warranty_period = sanitize($_POST['warranty_period']);
    $return_policy = sanitize($_POST['return_policy']);
    $badge = sanitize($_POST['badge']);
    $supplier_id = !empty($_POST['supplier_id']) ? sanitize($_POST['supplier_id']) : "NULL";
    $low_stock_threshold = !empty($_POST['low_stock_threshold']) ? sanitize($_POST['low_stock_threshold']) : 5;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Get current product data
    $result = $conn->query("SELECT image_path FROM products WHERE product_id = '$product_id'");
    $current_product = $result->fetch_assoc();
    
    // Handle image path - Keep existing path by default
    $image_path = $current_product['image_path'];
    
    // Only process new image if one was uploaded
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0 && $_FILES['image']['size'] > 0) {
        $upload = handleImageUpload($_FILES['image']);
        if($upload['success']) {
            // Delete old image if exists (and is not a default image)
            if(!empty($current_product['image_path']) && 
               file_exists($current_product['image_path']) && 
               strpos($current_product['image_path'], 'default') === false) {
                @unlink($current_product['image_path']);
            }
            $image_path = $upload['path'];
        } else {
            echo json_encode(["success" => false, "message" => $upload['message']]);
            return;
        }
    }
    
    // Prepare SQL query
    if($original_price === "NULL") {
        $original_price_sql = "NULL";
    } else {
        $original_price_sql = "'$original_price'";
    }
    
    if($supplier_id === "NULL") {
        $supplier_id_sql = "NULL";
    } else {
        $supplier_id_sql = "'$supplier_id'";
    }
    
    $sql = "UPDATE products SET 
                category_id = '$category_id',
                name = '$name',
                description = '$description',
                specifications = '$specifications',
                price = '$price',
                original_price = $original_price_sql,
                image_path = '$image_path',
                badge = '$badge',
                stock_quantity = '$stock_quantity',
                is_featured = '$is_featured',
                low_stock_threshold = '$low_stock_threshold',
                supplier_id = $supplier_id_sql,
                warranty_period = '$warranty_period',
                return_policy = '$return_policy'
            WHERE product_id = '$product_id'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Produit modifié avec succès"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur: " . $conn->error]);
    }
}

// Function to delete a product
function deleteProduct() {
    global $conn;
    
    $product_id = sanitize($_POST['product_id']);
    
    // Get image path before deleting
    $result = $conn->query("SELECT image_path FROM products WHERE product_id = '$product_id'");
    $product = $result->fetch_assoc();
    
    // Delete the product
    $sql = "DELETE FROM products WHERE product_id = '$product_id'";
    
    if ($conn->query($sql) === TRUE) {
        // Delete the image file if it exists
        if(!empty($product['image_path']) && file_exists($product['image_path'])) {
            unlink($product['image_path']);
        }
        
        echo json_encode(["success" => true, "message" => "Produit supprimé avec succès"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur: " . $conn->error]);
    }
}
?>