<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, email, phone_number, country, state, quarter, registration_date, role_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Vérifier si l'utilisateur a été trouvé
if (!$user) {
    echo "Erreur : utilisateur introuvable.";
    exit();
}

// Mapper le rôle de l'utilisateur
$roles = [
    1 => 'Administrateur',
    2 => 'Client',
    3 => 'Vendeur',
    4 => 'Gestionnaire de Stock',
    5 => 'Responsable Financier'
];

$user_role = isset($roles[$user['role_id']]) ? $roles[$user['role_id']] : 'Inconnu';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de l'utilisateur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        .container { max-width: 600px; margin-top: 50px; }
        .card-header { background-color: #007bff; color: white; }
        .btn-edit { margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <h4>Profil de l'utilisateur</h4>
        </div>
        <div class="card-body">
            <p><strong>Nom :</strong> <?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
            <p><strong>Pays :</strong> <?php echo htmlspecialchars($user['country']); ?></p>
            <p><strong>État / Région :</strong> <?php echo htmlspecialchars($user['state']); ?></p>
            <p><strong>Quartier :</strong> <?php echo htmlspecialchars($user['quarter']); ?></p>
            <p><strong>Date d'inscription :</strong> <?php echo date('d/m/Y H:i', strtotime($user['registration_date'])); ?></p>
            <p><strong>Rôle :</strong> <?php echo $user_role; ?></p>
            <button class="btn btn-warning btn-block btn-edit" data-toggle="modal" data-target="#editProfileModal">Modifier le Profil</button>
        </div>
    </div>
</div>

<!-- MODAL : Modifier le profil -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le Profil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProfileForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Pays</label>
                        <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($user['country']); ?>">
                    </div>
                    <div class="form-group">
                        <label>État / Région</label>
                        <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Quartier</label>
                        <input type="text" name="quarter" class="form-control" value="<?php echo htmlspecialchars($user['quarter']); ?>">
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

<script>
    $(document).ready(function() {
        $('#editProfileForm').submit(function(e) {
            e.preventDefault();
            $.post('profile_api.php', $(this).serialize(), function(response) {
                alert(response.message);
                if (response.success) {
                    location.reload();
                }
            }, 'json');
        });
    });
</script>

</body>
</html>
