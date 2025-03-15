<?php
require_once '../DatabaseConnection/db_config.php';
session_start();

// Fetch roles for dropdown
$roles_sql = "SELECT * FROM roles ORDER BY role_name";
$roles_result = $conn->query($roles_sql);
$roles = [];
while ($role = $roles_result->fetch_assoc()) {
    $roles[] = $role;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Utilisateur</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="text-center">Ajouter un Utilisateur</h3>
    <form id="addUserForm">
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" class="form-control" id="phone" name="phone">
        </div>
        <div class="form-group">
            <label>Rôle</label>
            <select class="form-control" id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['role_id']; ?>">
                        <?php echo htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
    </form>
</div>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    $('#addUserForm').submit(function(e) {
        e.preventDefault();
        
        // Check if passwords match
        if ($('#password').val() !== $('#confirm_password').val()) {
            alert('Les mots de passe ne correspondent pas.');
            return;
        }

        // AJAX request to add user
        $.ajax({
            url: 'user_actions.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = "users1.php"; // Redirect to user list
                } else {
                    alert("Erreur: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error: ", xhr.responseText);
                alert("Erreur AJAX: " + error);
            }
        });
    });
});
</script>

</body>
</html>
