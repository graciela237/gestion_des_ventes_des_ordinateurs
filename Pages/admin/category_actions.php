<?php
require_once '../DatabaseConnection/db_config.php';
header('Content-Type: application/json'); // Ensure JSON response
session_start();

$response = ['success' => false, 'message' => 'Invalid request'];

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ADD CATEGORY
    if ($action === 'add') {
        if (isset($_POST['category_name']) && !empty($_POST['category_name'])) {
            $category_name = trim($_POST['category_name']);
            $description = trim($_POST['description'] ?? '');

            // Check if the category already exists
            $check_sql = "SELECT COUNT(*) as count FROM product_categories WHERE category_name = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('s', $category_name);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Cette catégorie existe déjà.'];
            } else {
                // Insert new category
                $insert_sql = "INSERT INTO product_categories (category_name, description) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('ss', $category_name, $description);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Catégorie ajoutée avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de l\'ajout de la catégorie.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Le nom de la catégorie est obligatoire.'];
        }
    }

    // EDIT CATEGORY
    elseif ($action === 'edit') {
        if (isset($_POST['category_id'], $_POST['category_name']) && !empty($_POST['category_name'])) {
            $category_id = intval($_POST['category_id']);
            $category_name = trim($_POST['category_name']);
            $description = trim($_POST['description'] ?? '');

            // Check if another category with the same name exists
            $check_sql = "SELECT COUNT(*) as count FROM product_categories WHERE category_name = ? AND category_id != ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('si', $category_name, $category_id);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Une autre catégorie porte déjà ce nom.'];
            } else {
                // Update category
                $update_sql = "UPDATE product_categories SET category_name=?, description=? WHERE category_id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param('ssi', $category_name, $description, $category_id);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Catégorie mise à jour avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour de la catégorie.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
    }

    // DELETE CATEGORY
    elseif ($action === 'delete' && isset($_POST['category_id'])) {
        $category_id = intval($_POST['category_id']);

        // Check if the category has linked products
        $check_products_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
        $stmt = $conn->prepare($check_products_sql);
        $stmt->bind_param('i', $category_id);
        $stmt->execute();
        $check_result = $stmt->get_result()->fetch_assoc();

        if ($check_result['count'] > 0) {
            $response = ['success' => false, 'message' => 'Impossible de supprimer cette catégorie car elle contient des produits.'];
        } else {
            // Delete category
            $delete_sql = "DELETE FROM product_categories WHERE category_id=?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param('i', $category_id);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Catégorie supprimée avec succès.'];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la suppression de la catégorie.'];
            }
        }
        $stmt->close();
    }
}

// Return JSON response
echo json_encode($response);
exit;
?>
