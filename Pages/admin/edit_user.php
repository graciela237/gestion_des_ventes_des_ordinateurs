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
$sql = "SELECT * FROM users WHERE user_id = ?";
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

// Fetch all roles for the dropdown
$roles_sql = "SELECT * FROM roles ORDER BY role_name";
$roles_result = $conn->query($roles_sql);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $role_id = intval($_POST['role_id']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis";
    } 
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format d'email invalide";
    } 
    else {
        // Check if email already exists for other users
        $email_check = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        $stmt = $conn->prepare($email_check);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $email_result = $stmt->get_result();
        
        if ($email_result->num_rows > 0) {
            $_SESSION['error'] = "Cet email est déjà utilisé par un autre utilisateur";
        } else {
            // Update user
            $update_sql = "UPDATE users SET 
                            first_name = ?, 
                            last_name = ?, 
                            email = ?, 
                            phone_number = ?, 
                            role_id = ?,
                            is_active = ?
                          WHERE user_id = ?";
            
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssssiis", $first_name, $last_name, $email, $phone_number, $role_id, $is_active, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Les informations de l'utilisateur ont été mises à jour avec succès";
                header("Location: view_user.php?id=" . $user_id);
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'utilisateur | TechPro Admin</title>
    <?php include 'includes/header_scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Modifier l'utilisateur</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="all_users.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <a href="view_user.php?id=<?php echo $user_id; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php include 'includes/alerts.php'; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informations de l'utilisateur</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone_number" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">Rôle <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <?php if ($roles_result && $roles_result->num_rows > 0): ?>
                                            <?php while($role = $roles_result->fetch_assoc()): ?>
                                                <option value="<?php echo $role['role_id']; ?>" <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo ($user['is_active'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Compte actif
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                    </button>
                                    <a href="view_user.php?id=<?php echo $user_id; ?>" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times me-1"></i> Annuler
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include 'includes/footer_scripts.php'; ?>
</body>
</html>