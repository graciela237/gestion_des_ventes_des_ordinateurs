<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, phone_number, country, state, quarter, registration_date, role_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if user exists
if (!$user) {
    echo "Erreur : utilisateur introuvable.";
    exit();
}

// Map user roles
$roles = [
    1 => 'Administrateur',
    2 => 'Client',
    3 => 'Vendeur',
    4 => 'Gestionnaire de Stock',
    5 => 'Responsable Financier'
];

$user_role = isset($roles[$user['role_id']]) ? $roles[$user['role_id']] : 'Inconnu';

// Generate CSRF token for form security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilisateur | <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #ecf0f1;
            --text-color: #2c3e50;
            --light-gray: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
        }
        
        .container {
            max-width: 800px;
            margin-top: 50px;
            margin-bottom: 50px;
        }
        
        .profile-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: none;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-bottom: none;
        }
        
        .user-info {
            padding: 30px;
        }
        
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            min-width: 160px;
            color: var(--primary-color);
        }
        
        .info-value {
            flex-grow: 1;
        }
        
        .btn-edit {
            background-color: var(--secondary-color);
            border: none;
            padding: 10px 20px;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #777;
        }
        
        .modal-content {
            border-radius: 10px;
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-footer {
            border-top: none;
            padding: 20px;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
            border-color: var(--secondary-color);
        }
        
        .btn-success {
            background-color: #27ae60;
            border: none;
            padding: 10px 25px;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            border: none;
            padding: 10px 25px;
        }
        
        .icon {
            margin-right: 10px;
            color: var(--secondary-color);
            width: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .alert {
            display: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .role-badge {
            background-color: var(--secondary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="alert alert-success" id="successAlert" role="alert">
        <i class="fas fa-check-circle"></i> Vos informations ont été mises à jour avec succès.
    </div>
    
    <div class="alert alert-danger" id="errorAlert" role="alert">
        <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
    </div>
    
    <div class="card profile-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-user-circle"></i> Profil Utilisateur</h4>
                <span class="role-badge"><?php echo $user_role; ?></span>
            </div>
        </div>
        <div class="card-body user-info">
            <div class="info-item">
                <div class="info-label"><i class="fas fa-user icon"></i>Nom complet</div>
                <div class="info-value"><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-envelope icon"></i>Email</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-phone icon"></i>Téléphone</div>
                <div class="info-value"><?php echo htmlspecialchars($user['phone_number']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-globe icon"></i>Pays</div>
                <div class="info-value"><?php echo htmlspecialchars($user['country']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-map-marker-alt icon"></i>État / Région</div>
                <div class="info-value"><?php echo htmlspecialchars($user['state']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-map-pin icon"></i>Quartier</div>
                <div class="info-value"><?php echo htmlspecialchars($user['quarter']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fas fa-calendar-alt icon"></i>Date d'inscription</div>
                <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($user['registration_date'])); ?></div>
            </div>
            
            <div class="text-center mt-4">
                <button class="btn btn-primary btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-edit"></i> Modifier le Profil
                </button>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>© <?php echo date('Y'); ?> - Tous droits réservés</p>
    </div>
</div>

<!-- MODAL : Modifier le profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Modifier le Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProfileForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-group">
                        <label><i class="fas fa-user icon"></i>Prénom</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user icon"></i>Nom</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope icon"></i>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone icon"></i>Téléphone</label>
                        <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-globe icon"></i>Pays</label>
                        <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($user['country']); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt icon"></i>État / Région</label>
                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state']); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-pin icon"></i>Quartier</label>
                        <input type="text" name="quarter" class="form-control" value="<?php echo htmlspecialchars($user['quarter']); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editProfileForm').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'profile_api.php',
                data: $(this).serialize(),
                dataType: 'json',
                beforeSend: function() {
                    $('#successAlert').hide();
                    $('#errorAlert').hide();
                },
                success: function(response) {
                    if (response.success) {
                        $('#editProfileModal').modal('hide');
                        $('#successAlert').fadeIn();
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#errorMessage').text(response.message);
                        $('#errorAlert').fadeIn();
                    }
                },
                error: function() {
                    $('#errorMessage').text("Une erreur est survenue, veuillez réessayer plus tard.");
                    $('#errorAlert').fadeIn();
                }
            });
        });
    });
</script>

</body>
</html>