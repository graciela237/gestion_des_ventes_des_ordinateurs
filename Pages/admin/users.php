<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_condition = " WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param];
    $types = 'sss';
}

// Fetch users with pagination and search
$sql = "SELECT u.*, r.role_name FROM users u 
        JOIN roles r ON u.role_id = r.role_id
        {$search_condition}
        ORDER BY u.registration_date DESC
        LIMIT ?, ?";

// Add pagination parameters
$params[] = $offset;
$params[] = $records_per_page;
$types .= 'ii';

$stmt = $conn->prepare($sql);
// Bind parameters if there are any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total records for pagination
$count_sql = "SELECT COUNT(*) AS total FROM users u {$search_condition}";
$count_stmt = $conn->prepare($count_sql);
if (!empty($search)) {
    $count_stmt->bind_param('sss', $search_param, $search_param, $search_param);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch roles for dropdowns
$roles_sql = "SELECT * FROM roles ORDER BY role_name";
$roles_result = $conn->query($roles_sql);
$roles = [];
while ($role = $roles_result->fetch_assoc()) {
    $roles[] = $role;
}

// Handle user operations via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    // Add user
   // Add user
if ($_POST['action'] === 'add') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $state = trim($_POST['state']);
    $quarter = trim($_POST['quarter']);
    $password = $_POST['password'];
    $role_id = intval($_POST['role_id']);

    // Check if email exists
    $check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        $response['message'] = "Cet email est déjà utilisé.";
    } else {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // ✅ **Ensure role_id is selected correctly from roles table**
        $insert_sql = "INSERT INTO users (first_name, last_name, email, phone_number, country, state, quarter, password_hash, role_id) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param('ssssssssi', $first_name, $last_name, $email, $phone, $country, $state, $quarter, $password_hash, $role_id);

        if ($insert_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Utilisateur ajouté avec succès.";
        } else {
            $response['message'] = "Erreur lors de l'ajout de l'utilisateur: " . $conn->error;
        }
    }

    echo json_encode($response);
    exit;
}

    
   // Edit user
if ($_POST['action'] === 'edit') {
    $user_id = intval($_POST['user_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $state = trim($_POST['state']);
    $quarter = trim($_POST['quarter']);
    $role_id = intval($_POST['role_id']);

    // Check if email is already used by another user
    $check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND user_id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('si', $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        $response['message'] = "Cet email est déjà utilisé par un autre utilisateur.";
    } else {
        // Update user details
        $update_sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ?, 
                       country = ?, state = ?, quarter = ?, role_id = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('sssssssii', $first_name, $last_name, $email, $phone, $country, $state, $quarter, $role_id, $user_id);

        if ($update_stmt->execute()) {
            // Update password if provided
            if (!empty($_POST['password'])) {
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $pw_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                $pw_stmt = $conn->prepare($pw_sql);
                $pw_stmt->bind_param('si', $password_hash, $user_id);
                $pw_stmt->execute();
            }

            $response['success'] = true;
            $response['message'] = "Utilisateur mis à jour avec succès.";
        } else {
            $response['message'] = "Erreur lors de la mise à jour de l'utilisateur: " . $conn->error;
        }
    }

    echo json_encode($response);
    exit;
}

    
    // Delete user
   // Delete user
if ($_POST['action'] === 'delete') {
    $user_id = intval($_POST['user_id']);

    // Check if user exists
    $check_sql = "SELECT COUNT(*) as count FROM users WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] == 0) {
        $response['message'] = "Utilisateur non trouvé.";
    } else {
        // ✅ **Ensure foreign key constraints are handled properly**
        $delete_sql = "DELETE FROM users WHERE user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $user_id);

        if ($delete_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Utilisateur supprimé avec succès.";
        } else {
            $response['message'] = "Erreur lors de la suppression de l'utilisateur. Il peut y avoir des données liées à cet utilisateur.";
        }
    }

    echo json_encode($response);
    exit;
}

    // Get user details
    else if ($_POST['action'] === 'get_user') {
        $user_id = intval($_POST['user_id']);
        
        $user_sql = "SELECT * FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param('i', $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user = $user_result->fetch_assoc();
            $response['success'] = true;
            $response['user'] = $user;
        } else {
            $response['message'] = "Utilisateur non trouvé.";
        }
        
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs | TechPro Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Add your custom styles here */
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Utilisateurs</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus me-1"></i> Ajouter un utilisateur
                        </button>
                    </div>
                </div>
                
                <!-- Alerts container -->
                <div id="alertsContainer"></div>
                
                <!-- Search form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, prénom ou email..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Liste des utilisateurs</h5>
                            <span class="badge bg-primary"><?php echo $total_records; ?> utilisateurs</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Date d'inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['user_id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone_number'] ?? 'N/A'); ?></td>
                                            <td><span class="badge bg-info"><?php echo htmlspecialchars($row['role_name']); ?></span></td>
                                            <td>
                                                <?php if(isset($row['is_active']) && $row['is_active'] == 1): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($row['registration_date'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- View button -->
                                                    <button type="button" class="btn btn-sm btn-info view-user" data-id="<?php echo $row['user_id']; ?>" data-toggle="tooltip" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <!-- Edit button -->
                                                    <button type="button" class="btn btn-sm btn-warning edit-user" data-id="<?php echo $row['user_id']; ?>" data-toggle="tooltip" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <!-- Delete button -->
                                                    <button type="button" class="btn btn-sm btn-danger delete-user" data-id="<?php echo $row['user_id']; ?>" data-name="<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>" data-toggle="tooltip" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Aucun utilisateur trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Loading spinner -->
                        <div class="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="mt-2">Chargement des données...</p>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-4">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>" aria-label="Précédent">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?>" aria-label="Suivant">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- ADD USER MODAL -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Ajouter un utilisateur</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_first_name" class="form-label required-field">Prénom</label>
                                <input type="text" class="form-control" id="add_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_last_name" class="form-label required-field">Nom</label>
                                <input type="text" class="form-control" id="add_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_email" class="form-label required-field">Email</label>
                                <input type="email" class="form-control" id="add_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="add_phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="add_phone" name="phone">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="add_country" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="add_country" name="country" value="Cameroon">
                            </div>
                            <div class="col-md-4">
                                <label for="add_state" class="form-label">Région</label>
                                <input type="text" class="form-control" id="add_state" name="state">
                            </div>
                            <div class="col-md-4">
                                <label for="add_quarter" class="form-label">Quartier</label>
                                <input type="text" class="form-control" id="add_quarter" name="quarter">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_password" class="form-label required-field">Mot de passe</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="add_password" name="password" required>
                                    <i class="fas fa-eye password-toggle" data-target="add_password"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="add_confirm_password" class="form-label required-field">Confirmer le mot de passe</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="add_confirm_password" name="confirm_password" required>
                                    <i class="fas fa-eye password-toggle" data-target="add_confirm_password"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="add_role" class="form-label required-field">Rôle</label>
                                <select class="form-select" id="add_role" name="role_id" required>
    <?php foreach ($roles as $role): ?>
        <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
    <?php endforeach; ?>
</select>

                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Les champs marqués d'un <span class="text-danger">*</span> sont obligatoires.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- VIEW USER MODAL -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">Détails de l'utilisateur</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Informations personnelles</h6>
                            <table class="table table-striped">
                                <tr>
                                    <th>Nom complet</th>
                                    <td id="view_full_name"></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td id="view_email"></td>
                                </tr>
                                <tr>
                                    <th>Téléphone</th>
                                    <td id="view_phone"></td>
                                </tr>
                                <tr>
                                    <th>Rôle</th>
                                    <td id="view_role"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Adresse</h6>
                            <table class="table table-striped">
                                <tr>
                                    <th>Pays</th>
                                    <td id="view_country"></td>
                                </tr>
                                <tr>
                                    <th>Région</th>
                                    <td id="view_state"></td>
                                </tr>
                                <tr>
                                    <th>Quartier</th>
                                    <td id="view_quarter"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6>Informations système</h6>
                            <table class="table table-striped">
                                <tr>
                                    <th>ID utilisateur</th>
                                    <td id="view_user_id"></td>
                                </tr>
                                <tr>
                                    <th>Date d'inscription</th>
                                    <td id="view_registration_date"></td>
                                </tr>
                                <tr>
                                    <th>Statut</th>
                                    <td id="view_status"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-warning edit-user" data-id="1" data-toggle="modal" data-target="#editUserModal">
         <i class="fas fa-edit"></i> Modifier
     </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- EDIT USER MODAL -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_first_name" class="form-label required-field">Prénom</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_last_name" class="form-label required-field">Nom</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label required-field">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="edit_phone" name="phone">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="edit_country" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="edit_country" name="country">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_state" class="form-label">Région</label>
                                <input type="text" class="form-control" id="edit_state" name="state">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_quarter" class="form-label">Quartier</label>
                                <input type="text" class="form-control" id="edit_quarter" name="quarter">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Nouveau mot de passe</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="edit_password" name="password">
                                    <i class="fas fa-eye password-toggle" data-target="edit_password"></i>
                                </div>
                                <small class="form-text text-muted">Laissez vide pour conserver le mot de passe actuel</small>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                                    <i class="fas fa-eye password-toggle" data-target="edit_confirm_password"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_role" class="form-label required-field">Rôle</label>
                                <select class="form-select" id="edit_role" name="role_id" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Les champs marqués d'un <span class="text-danger">*</span> sont obligatoires.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- DELETE USER CONFIRMATION MODAL -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="delete_user_name"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Cette action est irréversible!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger confirm-delete" id="confirm_delete">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
    // Edit User Button Click
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('id'); // Get user ID from the button

        // Show loading spinner
        $('.loading-spinner').show();

        // Fetch user details via AJAX
        $.ajax({
            url: '', // Current page or API endpoint
            method: 'POST',
            data: {
                action: 'get_user',
                user_id: userId
            },
            dataType: 'json',
            success: function(response) {
                $('.loading-spinner').hide();

                if (response.success) {
                    const user = response.user;

                    // Populate the edit form with user details
                    $('#edit_user_id').val(user.user_id);
                    $('#edit_first_name').val(user.first_name);
                    $('#edit_last_name').val(user.last_name);
                    $('#edit_email').val(user.email);
                    $('#edit_phone').val(user.phone_number);
                    $('#edit_country').val(user.country);
                    $('#edit_state').val(user.state);
                    $('#edit_quarter').val(user.quarter);
                    $('#edit_role').val(user.role_id);

                    // Clear password fields
                    $('#edit_password').val('');
                    $('#edit_confirm_password').val('');

                    // Show the modal
                    $('#editUserModal').modal('show');
                } else {
                    alert(response.message); // Show error message
                }
            },
            error: function(xhr, status, error) {
                $('.loading-spinner').hide();
                alert('Une erreur est survenue: ' + error); // Show AJAX error
            }
        });
    });

    // Edit User Form Submission
    $('#editUserForm').submit(function(e) {
        e.preventDefault(); // Prevent default form submission

        // Password validation
        const password = $('#edit_password').val();
        const confirmPassword = $('#edit_confirm_password').val();

        if (password && password !== confirmPassword) {
            alert('Les mots de passe ne correspondent pas.');
            return;
        }

        // Prepare form data
        const formData = {
            action: 'edit',
            user_id: $('#edit_user_id').val(),
            first_name: $('#edit_first_name').val(),
            last_name: $('#edit_last_name').val(),
            email: $('#edit_email').val(),
            phone: $('#edit_phone').val(),
            country: $('#edit_country').val(),
            state: $('#edit_state').val(),
            quarter: $('#edit_quarter').val(),
            password: password,
            role_id: $('#edit_role').val()
        };

        // Show loading spinner
        $('.loading-spinner').show();

        // Submit form data via AJAX
        $.ajax({
            url: '', // Current page or API endpoint
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('.loading-spinner').hide();

                if (response.success) {
                    alert(response.message); // Show success message
                    $('#editUserModal').modal('hide'); // Close modal
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert(response.message); // Show error message
                }
            },
            error: function(xhr, status, error) {
                $('.loading-spinner').hide();
                alert('Une erreur est survenue: ' + error); // Show AJAX error
            }
        });
    });
});
            // View User Button Click
            $(document).on('click', '.view-user', function() {
                const userId = $(this).data('id');
                
                // Show loading spinner
                $('.loading-spinner').show();
                
                // Get user details via AJAX
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: {
                        action: 'get_user',
                        user_id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.loading-spinner').hide();
                        
                        if (response.success) {
                            const user = response.user;
                            
                            // Fill the modal with user details
                            $('#view_full_name').text(user.first_name + ' ' + user.last_name);
                            $('#view_email').text(user.email);
                            $('#view_phone').text(user.phone_number || 'N/A');
                            $('#view_country').text(user.country || 'N/A');
                            $('#view_state').text(user.state || 'N/A');
                            $('#view_quarter').text(user.quarter || 'N/A');
                            $('#view_user_id').text(user.user_id);
                            $('#view_registration_date').text(formatDateTime(user.registration_date));
                            $('#view_status').html(user.is_active == 1 ? 
                                '<span class="badge bg-success">Actif</span>' : 
                                '<span class="badge bg-danger">Inactif</span>');
                            
                            // Store user ID for edit functionality
                            $('.edit-from-view').data('id', user.user_id);
                            
                            // Show modal
                            $('#viewUserModal').modal('show');
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('.loading-spinner').hide();
                        showAlert('danger', 'Une erreur est survenue: ' + error);
                    }
                });
            });
            
            // Edit from View Button Click
            $('.edit-from-view').click(function() {
                const userId = $(this).data('id');
                $('#viewUserModal').modal('hide');
                
                // Trigger edit user with delay to allow modal transitions
                setTimeout(function() {
                    $('.edit-user[data-id="' + userId + '"]').click();
                }, 500);
            });
            
           // Edit User Button Click
$(document).on('click', '.edit-user', function() {
    const userId = $(this).data('id');
    
    // Show loading spinner
    $('.loading-spinner').show();
    
    // Get user details via AJAX
    $.ajax({
        url: '',
        method: 'POST',
        data: {
            action: 'get_user',
            user_id: userId
        },
        dataType: 'json',
        success: function(response) {
            $('.loading-spinner').hide();
            
            if (response.success) {
                const user = response.user;
                
                // Fill the edit form with user details
                $('#edit_user_id').val(user.user_id);
                $('#edit_first_name').val(user.first_name);
                $('#edit_last_name').val(user.last_name);
                $('#edit_email').val(user.email);
                $('#edit_phone').val(user.phone_number);
                $('#edit_country').val(user.country);
                $('#edit_state').val(user.state);
                $('#edit_quarter').val(user.quarter);
                $('#edit_role').val(user.role_id);
                
                // Clear password fields
                $('#edit_password').val('');
                $('#edit_confirm_password').val('');
                
                // Show modal
                $('#editUserModal').modal('show');
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('.loading-spinner').hide();
            showAlert('danger', 'Une erreur est survenue: ' + error);
        }
    });
});

// Delete User Button Click
$(document).on('click', '.delete-user', function() {
    const userId = $(this).data('id');
    const userName = $(this).data('name');
    
    // Set user name in confirmation modal
    $('#delete_user_name').text(userName);
    
    // Set user ID for delete confirmation
    $('#confirm_delete').data('id', userId);
    
    // Show modal
    $('#deleteUserModal').modal('show');
});
         // Edit User Form Submit
$('#editUserForm').submit(function(e) {
    e.preventDefault();
    
    // Password validation
    const password = $('#edit_password').val();
    const confirmPassword = $('#edit_confirm_password').val();
    
    if (password && password !== confirmPassword) {
        showAlert('danger', 'Les mots de passe ne correspondent pas.');
        return;
    }
    
    // Get form data
    const formData = {
        action: 'edit',
        user_id: $('#edit_user_id').val(),
        first_name: $('#edit_first_name').val(),
        last_name: $('#edit_last_name').val(),
        email: $('#edit_email').val(),
        phone: $('#edit_phone').val(),
        country: $('#edit_country').val(),
        state: $('#edit_state').val(),
        quarter: $('#edit_quarter').val(),
        password: password,
        role_id: $('#edit_role').val()
    };
    
    // Show loading spinner
    $('.loading-spinner').show();
    
    // Send AJAX request
    $.ajax({
        url: '',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $('.loading-spinner').hide();
            
            if (response.success) {
                // Show success message
                showAlert('success', response.message);
                
                // Close modal
                $('#editUserModal').modal('hide');
                
                // Reload page after 1.5 seconds
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                // Show error message
                showAlert('danger', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('.loading-spinner').hide();
            showAlert('danger', 'Une erreur est survenue: ' + error);
        }
    });
});

// Confirm Delete User
$('#confirm_delete').click(function() {
    const userId = $(this).data('id');
    
    // Show loading spinner
    $('.loading-spinner').show();
    
    // Send AJAX request
    $.ajax({
        url: '',
        method: 'POST',
        data: {
            action: 'delete',
            user_id: userId
        },
        dataType: 'json',
        success: function(response) {
            $('.loading-spinner').hide();
            
            if (response.success) {
                // Show success message
                showAlert('success', response.message);
                
                // Close modal
                $('#deleteUserModal').modal('hide');
                
                // Reload page after 1.5 seconds
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                // Show error message
                showAlert('danger', response.message);
                
                // Close modal
                $('#deleteUserModal').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            $('.loading-spinner').hide();
            showAlert('danger', 'Une erreur est survenue: ' + error);
            $('#deleteUserModal').modal('hide');
        }
    });
});
            
            // Delete User Button Click
            $(document).on('click', '.delete-user', function() {
                const userId = $(this).data('id');
                const userName = $(this).data('name');
                
                // Set user name in confirmation modal
                $('#delete_user_name').text(userName);
                
                // Set user ID for delete confirmation
                $('#confirm_delete').data('id', userId);
                
                // Show modal
                $('#deleteUserModal').modal('show');
            });
            
            // Confirm Delete User
            $('#confirm_delete').click(function() {
                const userId = $(this).data('id');
                
                // Show loading spinner
                $('.loading-spinner').show();
                
                // Send AJAX request
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        user_id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('.loading-spinner').hide();
                        
                        if (response.success) {
                            // Show success message
                            showAlert('success', response.message);
                            
                            // Close modal
                            $('#deleteUserModal').modal('hide');
                            
                            // Reload page after 1.5 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            // Show error message
                            showAlert('danger', response.message);
                            
                            // Close modal
                            $('#deleteUserModal').modal('hide');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('.loading-spinner').hide();
                        showAlert('danger', 'Une erreur est survenue: ' + error);
                        $('#deleteUserModal').modal('hide');
                    }
                });
            });
            
            // Function to show alert messages
            function showAlert(type, message) {
                const alertHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                $('#alertsContainer').html(alertHTML);
                
                // Auto-dismiss after 5 seconds
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }
            
            // Function to format date and time
            function formatDateTime(dateTimeString) {
                const date = new Date(dateTimeString);
                
                // Format: DD/MM/YYYY HH:MM
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                
                return `${day}/${month}/${year} ${hours}:${minutes}`;
            }
            
            // Clear form when add modal is closed
            $('#addUserModal').on('hidden.bs.modal', function () {
                $('#addUserForm')[0].reset();
            });
            
            // Clear form when edit modal is closed
            $('#editUserModal').on('hidden.bs.modal', function () {
                $('#editUserForm')[0].reset();
            });
        });
    </script>
</body>
</html>