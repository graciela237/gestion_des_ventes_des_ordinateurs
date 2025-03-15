<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Check if the user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID d'utilisateur invalide";
    header("Location: all_users.php");
    exit;
}

$user_id = $_GET['id'];

// Fetch user details
$sql = "SELECT u.*, r.role_name FROM users u 
        JOIN roles r ON u.role_id = r.role_id 
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Utilisateur non trouvé";
    header("Location: all_users.php");
    exit;
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'utilisateur | TechPro Admin</title>
    <?php include 'includes/header_scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Détails de l'utilisateur</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="all_users.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php include 'includes/alerts.php'; ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informations personnelles</h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <div class="d-inline-block rounded-circle bg-secondary text-white p-3" style="width: 100px; height: 100px; line-height: 70px; font-size: 2.5rem;">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                    </div>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                                <p class="card-text">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($user['role_name']); ?></span>
                                </p>
                                <p class="text-muted">
                                    Inscrit le <?php echo date('d/m/Y H:i', strtotime($user['registration_date'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Coordonnées</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="fw-bold">Email:</label>
                                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Téléphone:</label>
                                    <p><?php echo htmlspecialchars($user['phone_number']); ?></p>
                                </div>
                                <?php if(isset($user['address'])): ?>
                                <div class="mb-3">
                                    <label class="fw-bold">Adresse:</label>
                                    <p><?php echo htmlspecialchars($user['address']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Informations supplémentaires</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="fw-bold">Dernière connexion:</label>
                                    <p><?php echo isset($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Statut du compte:</label>
                                    <?php if(isset($user['is_active']) && $user['is_active'] == 1): ?>
                                        <p><span class="badge bg-success">Actif</span></p>
                                    <?php else: ?>
                                        <p><span class="badge bg-danger">Inactif</span></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>