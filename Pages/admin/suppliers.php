<?php
require_once '../DatabaseConnection/db_config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fournisseurs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2>Gestion des Fournisseurs</h2>
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#supplierModal">Ajouter un Fournisseur</button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom Entreprise</th>
                    <th>Nom Contact</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="supplierTable">
                <?php
                $result = $conn->query("SELECT * FROM suppliers ORDER BY supplier_id ASC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr id='supplier-" . $row['supplier_id'] . "'>";
                        echo "<td>" . $row['supplier_id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['contact_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                        echo "<td>
                            <button class='btn btn-warning btn-sm edit-btn' 
                                data-id='" . $row['supplier_id'] . "' 
                                data-company='" . htmlspecialchars($row['company_name']) . "' 
                                data-contact='" . htmlspecialchars($row['contact_name']) . "' 
                                data-email='" . htmlspecialchars($row['email']) . "' 
                                data-phone='" . htmlspecialchars($row['phone_number']) . "' 
                                data-address='" . htmlspecialchars($row['address']) . "'>
                                Modifier
                            </button>
                            <button class='btn btn-danger btn-sm delete-btn' 
                                data-id='" . $row['supplier_id'] . "'>
                                Supprimer
                            </button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>Aucun fournisseur trouvé</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL : Ajouter / Modifier un fournisseur -->
<div class="modal" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalLabel">Ajouter un Fournisseur</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="supplierForm">
                    <div class="modal-body">
                        <input type="hidden" name="id_fournisseur" id="id_fournisseur">
                        <input type="hidden" name="action" id="action" value="add">
                        <div class="form-group">
                            <label>Nom Entreprise</label>
                            <input type="text" name="nom_entreprise" id="nom_entreprise" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nom Contact</label>
                            <input type="text" name="nom_contact" id="nom_contact" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="text" name="telephone" id="telephone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Adresse</label>
                            <textarea name="adresse" id="adresse" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL : Confirmation de suppression -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer ce fournisseur ?</p>
                    <input type="hidden" id="delete_supplier_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Ouvrir la modal d'ajout
            $('.btn-primary').click(function() {
                $('#supplierForm')[0].reset();
                $('#action').val('add');
                $('#supplierModalLabel').text('Ajouter un Fournisseur');
            });

            // Remplir la modal avec les données existantes pour modification
            $('.edit-btn').click(function() {
                $('#id_fournisseur').val($(this).data('id'));
                $('#nom_entreprise').val($(this).data('company'));
                $('#nom_contact').val($(this).data('contact'));
                $('#email').val($(this).data('email'));
                $('#telephone').val($(this).data('phone'));
                $('#adresse').val($(this).data('address'));
                $('#action').val('edit');
                $('#supplierModalLabel').text('Modifier le Fournisseur');
                $('#supplierModal').modal('show');
            });

            // Gérer l'envoi du formulaire AJAX
            $('#supplierForm').submit(function(e) {
                e.preventDefault();
                $.post('suppliers_actions.php', $(this).serialize(), function(response) {
                    alert(response.message);
                    if (response.success) {
                        location.reload();
                    }
                }, 'json');
            });

            // Supprimer un fournisseur
            $('.delete-btn').click(function() {
                let id = $(this).data('id');
                $('#delete_supplier_id').val(id);
                $('#confirmDeleteModal').modal('show');
            });

            // Confirmer la suppression
            $('#confirmDelete').click(function() {
                let id = $('#delete_supplier_id').val();
                $.post('suppliers_actions.php', { action: 'delete', id_fournisseur: id }, function(response) {
                    alert(response.message);
                    if (response.success) {
                        $('#supplier-' + id).remove();
                        $('#confirmDeleteModal').modal('hide');
                    }
                }, 'json');
            });
        });
    </script>
</body>
</html>
