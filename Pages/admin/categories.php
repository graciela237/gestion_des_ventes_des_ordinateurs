<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Fetch all product categories
$sql = "SELECT * FROM product_categories ORDER BY category_id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-responsive { margin-top: 20px; }
        .modal-header { background-color: #007bff; color: white; }
        .btn { margin-right: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mt-4">Liste des Catégories</h2>

    <!-- Button to Open Add Category Modal -->
    <button id="openAddCategoryModal" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ajouter une Catégorie
    </button>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom de la Catégorie</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categoryTableBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="category-<?php echo $row['category_id']; ?>">
                    <td><?php echo $row['category_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-category"
                                data-id="<?php echo $row['category_id']; ?>"
                                data-name="<?php echo htmlspecialchars($row['category_name']); ?>"
                                data-description="<?php echo htmlspecialchars($row['description']); ?>">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="btn btn-danger btn-sm delete-category"
                                data-id="<?php echo $row['category_id']; ?>">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Catégorie</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nom de la Catégorie</label>
                        <input type="text" class="form-control" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description"></textarea>
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

<!-- EDIT CATEGORY MODAL -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la Catégorie</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editCategoryForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="form-group">
                        <label>Nom de la Catégorie</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="edit_description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à Jour</button>
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
    // Open Add Category Modal
    $('#openAddCategoryModal').click(function() {
        $('#addCategoryModal').modal('show');
    });

    // Submit Add Category Form
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&action=add';

        $.ajax({
            url: 'category_actions.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    $('#addCategoryModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.error("Erreur AJAX:", xhr.responseText);
            }
        });
    });

    // Open Edit Modal
    $('.edit-category').click(function() {
        $('#edit_category_id').val($(this).data('id'));
        $('#edit_category_name').val($(this).data('name'));
        $('#edit_description').val($(this).data('description'));
        $('#editCategoryModal').modal('show');
    });

    // Submit Edit Category Form
    $('#editCategoryForm').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&action=edit';

        $.ajax({
            url: 'category_actions.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    $('#editCategoryModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                console.error("Erreur AJAX:", xhr.responseText);
            }
        });
    });

    // DELETE CATEGORY
    $('.delete-category').click(function() {
        let categoryId = $(this).data('id');
        if (confirm("Voulez-vous vraiment supprimer cette catégorie ?")) {
            $.ajax({
                url: 'category_actions.php',
                type: 'POST',
                data: { action: 'delete', category_id: categoryId },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        $('#category-' + categoryId).remove();
                    }
                },
                error: function(xhr) {
                    console.error("Erreur AJAX:", xhr.responseText);
                }
            });
        }
    });
});
</script>

</body>
</html>
