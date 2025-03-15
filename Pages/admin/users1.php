<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Fetch all users with their roles
$sql = "SELECT u.*, r.role_name FROM users u 
        JOIN roles r ON u.role_id = r.role_id
        ORDER BY u.user_id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {   background-color: #f8f9fa }
        .table-responsive { margin-top: 20px; }
        .modal-header { background-color: #007bff; color: white; }
        .btn { margin-right: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mt-4">Liste des Utilisateurs</h2>

    <!-- Button to Open Add User Modal -->
    <button id="openAddUserModal" class="btn btn-success mb-3">
    <i class="fas fa-user-plus"></i> Enregistrer un utilisateur
</button>

    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="user-<?php echo $row['user_id']; ?>">
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number'] ?? 'N/A'); ?></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($row['role_name']); ?></span></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['registration_date'])); ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-user"
                                data-id="<?php echo $row['user_id']; ?>"
                                data-firstname="<?php echo htmlspecialchars($row['first_name']); ?>"
                                data-lastname="<?php echo htmlspecialchars($row['last_name']); ?>"
                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                data-phone="<?php echo htmlspecialchars($row['phone_number']); ?>"
                                data-role="<?php echo $row['role_id']; ?>">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        <button class="btn btn-danger btn-sm delete-user"
                                data-id="<?php echo $row['user_id']; ?>">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select class="form-control" id="edit_role" name="role_id">
                            <option value="1">Admin</option>
                            <option value="2">Client</option>
                            <option value="3">Vendeur</option>
                            <option value="4">Gestionnaire Stock</option>
                            <option value="5">Responsable Financier</option>
                        </select>
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
<!-- Button to Open Add User Modal -->

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select class="form-control" name="role_id">
                            <option value="1">Admin</option>
                            <option value="2">Client</option>
                            <option value="3">Vendeur</option>
                            <option value="4">Gestionnaire Stock</option>
                            <option value="5">Responsable Financier</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mot de Passe</label>
                        <input type="password" class="form-control" name="password" required>
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
    // ✅ FIX: Ensure the button correctly opens the modal
    $('#openAddUserModal').click(function() {
        $('#addUserModal').modal('show');
    });

    // ✅ FIX: Ensure the modal can be submitted via AJAX
    $('#addUserForm').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&action=add';

        $.ajax({
            url: 'user_actions.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#addUserModal').modal('hide');
                    location.reload();
                } else {
                    alert("Erreur : " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur AJAX:", xhr.responseText);
                alert("Erreur AJAX: Vérifiez la console.");
            }
        });
    });
});
</script>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Open Edit Modal
    $('.edit-user').click(function() {
        $('#edit_user_id').val($(this).data('id'));
        $('#edit_first_name').val($(this).data('firstname'));
        $('#edit_last_name').val($(this).data('lastname'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_phone').val($(this).data('phone'));
        $('#edit_role').val($(this).data('role'));
        $('#editUserModal').modal('show');
    });

    // Submit Edit User Form
    $('#editUserForm').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&action=edit';

        $.ajax({
            url: 'user_actions.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#editUserModal').modal('hide');
                    location.reload();
                } else {
                    alert("Erreur : " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur AJAX:", xhr.responseText);
                alert("Erreur AJAX: Vérifiez la console.");
            }
        });
    });

    // DELETE USER
    $('.delete-user').click(function() {
        let userId = $(this).data('id');
        if (confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
            $.ajax({
                url: 'user_actions.php',
                type: 'POST',
                data: { action: 'delete', user_id: userId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#user-' + userId).remove();
                    } else {
                        alert("Erreur : " + response.message);
                    }
                },
                error: function(xhr) {
                    console.error("Erreur AJAX:", xhr.responseText);
                    alert("Erreur AJAX: Vérifiez la console.");
                }
            });
        }
    });
});
</script>

</body>
</html>
