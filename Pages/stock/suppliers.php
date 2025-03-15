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
    <style>
        /* Responsive styles */
        @media (max-width: 767.98px) {
            .table-responsive-card .table thead {
                display: none;
            }
            
            .table-responsive-card .table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }
            
            .table-responsive-card .table td {
                display: flex;
                justify-content: space-between;
                text-align: right;
                border-bottom: 1px solid #dee2e6;
                padding: 0.5rem 0.75rem;
            }
            
            .table-responsive-card .table td:last-child {
                border-bottom: none;
            }
            
            .table-responsive-card .table td::before {
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
                padding-right: 0.5rem;
            }
            
            .table-responsive-card .btn-group-responsive {
                display: flex;
                justify-content: space-between;
                width: 100%;
            }
            
            .table-responsive-card .btn-group-responsive .btn {
                flex: 1;
                margin: 0 0.25rem;
            }
        }
        
        /* Consistent button spacing */
        .action-buttons .btn {
            margin: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-3">Gestion des Fournisseurs</h2>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#supplierModal">
                <i class="fa fa-plus"></i> Ajouter un Fournisseur
            </button>
            <div class="d-none d-md-block">
                <small class="text-muted">Total: <?php echo $conn->query("SELECT COUNT(*) as count FROM suppliers")->fetch_assoc()['count']; ?> fournisseurs</small>
            </div>
        </div>

        <div class="table-responsive table-responsive-card">
            <table class="table table-hover">
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
                            echo "<td data-label='ID'>" . $row['supplier_id'] . "</td>";
                            echo "<td data-label='Nom Entreprise'>" . htmlspecialchars($row['company_name']) . "</td>";
                            echo "<td data-label='Nom Contact'>" . htmlspecialchars($row['contact_name']) . "</td>";
                            echo "<td data-label='Email'>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td data-label='Téléphone'>" . htmlspecialchars($row['phone_number']) . "</td>";
                            echo "<td data-label='Adresse'>" . htmlspecialchars($row['address']) . "</td>";
                            echo "<td data-label='Actions' class='action-buttons'>
                                <div class='btn-group-responsive'>
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
                                </div>
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
    </div>

    <!-- MODAL : Ajouter / Modifier un fournisseur -->
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
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
                            <label for="nom_entreprise">Nom Entreprise</label>
                            <input type="text" name="nom_entreprise" id="nom_entreprise" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="nom_contact">Nom Contact</label>
                            <input type="text" name="nom_contact" id="nom_contact" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" name="telephone" id="telephone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <textarea name="adresse" id="adresse" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL : Confirmation de suppression -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
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
            $(document).on('click', '.edit-btn', function() {
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
                    if (response.success) {
                        $('#supplierModal').modal('hide');
                        showNotification(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification(response.message, 'danger');
                    }
                }, 'json')
                .fail(function() {
                    showNotification('Une erreur est survenue. Veuillez réessayer.', 'danger');
                });
            });

            // Supprimer un fournisseur
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                $('#delete_supplier_id').val(id);
                $('#confirmDeleteModal').modal('show');
            });

            // Confirmer la suppression
            $('#confirmDelete').click(function() {
                let id = $('#delete_supplier_id').val();
                $.post('suppliers_actions.php', { action: 'delete', id_fournisseur: id }, function(response) {
                    if (response.success) {
                        $('#supplier-' + id).fadeOut('slow', function() {
                            $(this).remove();
                            $('#confirmDeleteModal').modal('hide');
                            showNotification(response.message, 'success');
                        });
                    } else {
                        showNotification(response.message, 'danger');
                    }
                }, 'json')
                .fail(function() {
                    showNotification('Une erreur est survenue. Veuillez réessayer.', 'danger');
                });
            });

            // Function to show notifications
            function showNotification(message, type) {
                // Create notification element
                let notification = $('<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>');
                    
                // Append to top of container
                $('.container').prepend(notification);
                
                // Auto remove after 3 seconds
                setTimeout(function() {
                    notification.alert('close');
                }, 3000);
            }
        });
    </script>
</body>
</html>