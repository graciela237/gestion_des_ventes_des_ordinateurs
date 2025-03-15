<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Fetch all categories
$categories = $conn->query("SELECT * FROM product_categories ORDER BY category_name ASC");

// Fetch all suppliers
$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY company_name ASC");

// Fetch all products with related category and supplier names
$sql = "SELECT p.*, c.category_name, s.company_name AS supplier_name 
        FROM products p 
        LEFT JOIN product_categories c ON p.category_id = c.category_id
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        ORDER BY p.product_id ASC";
$products = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-responsive { margin-top: 20px; }
        .modal-header { background-color: #007bff; color: white; }
        .btn { margin-right: 5px; }
        .modal-lg { max-width: 800px; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mt-4">Liste des Produits</h2>

    <!-- Button to Open Add Product Modal -->
    <button type="button" class="btn btn-success mb-3" id="showAddProductModal">
        <i class="fas fa-plus"></i> Ajouter un Produit
    </button>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Fournisseur</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $products->fetch_assoc()): ?>
                <tr id="product-<?php echo $row['product_id']; ?>">
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo number_format($row['price'], 2) . ' €'; ?></td>
                    <td><?php echo $row['stock_quantity']; ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                    <td><?php if(!empty($row['image_path'])): ?>
                           <img src="<?php echo $row['image_path']; ?>" width="50" alt="Product Image">
                        <?php else: ?>
                           <span>No Image</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-product"
                                data-id="<?php echo $row['product_id']; ?>"
                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                data-category="<?php echo $row['category_id']; ?>"
                                data-price="<?php echo $row['price']; ?>"
                                data-original-price="<?php echo $row['original_price']; ?>"
                                data-stock="<?php echo $row['stock_quantity']; ?>"
                                data-description="<?php echo htmlspecialchars($row['description']); ?>"
                                data-specifications="<?php echo htmlspecialchars($row['specifications']); ?>"
                                data-warranty="<?php echo htmlspecialchars($row['warranty_period']); ?>"
                                data-return-policy="<?php echo htmlspecialchars($row['return_policy']); ?>"
                                data-image="<?php echo $row['image_path']; ?>"
                                data-badge="<?php echo htmlspecialchars($row['badge']); ?>"
                                data-featured="<?php echo $row['is_featured']; ?>"
                                data-low-stock="<?php echo $row['low_stock_threshold']; ?>"
                                data-supplier="<?php echo $row['supplier_id']; ?>">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="btn btn-danger btn-sm delete-product"
                                data-id="<?php echo $row['product_id']; ?>">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- PRODUCT MODAL (FOR BOTH ADD AND EDIT) -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Gestion Produit</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="product_id">
                    <input type="hidden" name="action" id="form_action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom du Produit</label>
                                <input type="text" class="form-control" name="name" id="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Catégorie</label>
                                <select class="form-control" name="category_id" id="category_id" required>
                                    <?php 
                                    // Reset the category results pointer
                                    $categories->data_seek(0);
                                    while ($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Prix (€)</label>
                                <input type="number" class="form-control" name="price" id="price" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Prix Original (€) <small>(optionnel)</small></label>
                                <input type="number" class="form-control" name="original_price" id="original_price" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label>Stock</label>
                                <input type="number" class="form-control" name="stock_quantity" id="stock_quantity" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Seuil de Stock Bas</label>
                                <input type="number" class="form-control" name="low_stock_threshold" id="low_stock_threshold" value="5">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fournisseur</label>
                                <select class="form-control" name="supplier_id" id="supplier_id">
                                    <option value="">-- Sélectionner un fournisseur --</option>
                                    <?php 
                                    // Reset the suppliers results pointer
                                    $suppliers->data_seek(0);
                                    while ($sup = $suppliers->fetch_assoc()): ?>
                                        <option value="<?php echo $sup['supplier_id']; ?>"><?php echo htmlspecialchars($sup['company_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Badge <small>(ex: "Nouveau", "Promotion")</small></label>
                                <input type="text" class="form-control" name="badge" id="badge">
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="is_featured" id="is_featured" value="1">
                                <label class="form-check-label">Produit Vedette</label>
                            </div>
                            
                            <div class="form-group">
                                <label>Garantie</label>
                                <input type="text" class="form-control" name="warranty_period" id="warranty_period" placeholder="Ex: 12 mois">
                            </div>
                            
                            <div class="form-group">
                                <label>Image Actuelle</label>
                                <div id="current_image_container">
                                    <small class="text-muted">Aucune image</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Nouvelle Image</label>
                                <input type="file" class="form-control-file" name="image" id="image" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Spécifications Techniques</label>
                        <textarea class="form-control" name="specifications" id="specifications" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Politique de Retour</label>
                        <textarea class="form-control" name="return_policy" id="return_policy" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Show modal for adding a product
    $('#showAddProductModal').click(function() {
        // Reset the form
        $('#productForm')[0].reset();
        $('#product_id').val('');
        $('#form_action').val('add');
        $('#modalTitle').text('Ajouter un Produit');
        $('#current_image_container').html('<small class="text-muted">Aucune image</small>');
        
        // Show the modal
        $('#productModal').modal('show');
    });
    
    // Show modal for editing a product
    $('.edit-product').click(function() {
        // Reset the form first
        $('#productForm')[0].reset();
        
        // Get product data from data attributes
        const productId = $(this).data('id');
        const name = $(this).data('name');
        const categoryId = $(this).data('category');
        const price = $(this).data('price');
        const originalPrice = $(this).data('original-price');
        const stock = $(this).data('stock');
        const description = $(this).data('description');
        const specifications = $(this).data('specifications');
        const warranty = $(this).data('warranty');
        const returnPolicy = $(this).data('return-policy');
        const imagePath = $(this).data('image');
        const badge = $(this).data('badge');
        const featured = $(this).data('featured');
        const lowStock = $(this).data('low-stock');
        const supplierId = $(this).data('supplier');
        
        // Set form values
        $('#product_id').val(productId);
        $('#form_action').val('edit');
        $('#name').val(name);
        $('#category_id').val(categoryId);
        $('#price').val(price);
        $('#original_price').val(originalPrice);
        $('#stock_quantity').val(stock);
        $('#description').val(description);
        $('#specifications').val(specifications);
        $('#warranty_period').val(warranty);
        $('#return_policy').val(returnPolicy);
        $('#badge').val(badge);
        $('#low_stock_threshold').val(lowStock);
        $('#supplier_id').val(supplierId);
        
        // Set checkbox
        $('#is_featured').prop('checked', featured == 1);
        
        // Show current image if available
        if (imagePath) {
            $('#current_image_container').html(
                `<img src="${imagePath}" width="100" class="img-thumbnail mb-2"><br>
                <small class="text-muted">Laisser vide pour conserver cette image</small>`
            );
        } else {
            $('#current_image_container').html('<small class="text-muted">Aucune image</small>');
        }
        
        // Set modal title
        $('#modalTitle').text('Modifier le Produit: ' + name);
        
        // Show the modal
        $('#productModal').modal('show');
    });
    
    // Handle form submission
    $('#productForm').submit(function(e) {
        e.preventDefault();
        
        // Create FormData object for handling file uploads
        const formData = new FormData(this);
        
        // AJAX request to save the product
        $.ajax({
            url: 'product_actions.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    alert(result.message);
                    
                    if (result.success) {
                        // Close the modal and reload page to see changes
                        $('#productModal').modal('hide');
                        location.reload();
                    }
                } catch (e) {
                    alert('Une erreur est survenue. Veuillez réessayer.');
                    console.error(response);
                }
            },
            error: function() {
                alert('Erreur de connexion au serveur.');
            }
        });
    });
    
    // Delete Product
    $('.delete-product').click(function() {
        let productId = $(this).data('id');
        if (confirm("Êtes-vous sûr de vouloir supprimer ce produit ?")) {
            $.ajax({
                url: 'product_actions.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    product_id: productId
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        alert(result.message);
                        
                        if (result.success) {
                            // Remove the product row from the table
                            $('#product-' + productId).remove();
                        }
                    } catch (e) {
                        alert('Une erreur est survenue. Veuillez réessayer.');
                        console.error(response);
                    }
                },
                error: function() {
                    alert('Erreur de connexion au serveur.');
                }
            });
        }
    });
});
</script>

</body>
</html>