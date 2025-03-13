<?php 
session_start(); 
require_once '../DatabaseConnection/db_config.php';  

// Redirect if user is not logged in 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}  

// Fetch user details 
$user_id = $_SESSION['user_id']; 
$query = "SELECT first_name, last_name, email, phone_number, country, state, quarter FROM users WHERE user_id = ?"; 
$stmt = $conn->prepare($query); 
$stmt->bind_param("i", $user_id); 
$stmt->execute(); 
$result = $stmt->get_result(); 
$user = $result->fetch_assoc();  

// Close DB connection 
$stmt->close(); 
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --text-color: #333;
            --light-bg: #f5f5f5;
            --border-color: #ddd;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--light-bg);
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .profile-header h2 {
            margin-bottom: 5px;
            font-size: 24px;
        }
        
        .profile-container {
            padding: 30px;
        }
        
        #message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        
        .success-message {
            background-color: rgba(46, 204, 113, 0.2);
            border-left: 4px solid var(--success-color);
            color: #27ae60;
        }
        
        .error-message {
            background-color: rgba(231, 76, 60, 0.2);
            border-left: 4px solid var(--error-color);
            color: #c0392b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        
        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .profile-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h2>Mon Profil</h2>
            <p>Gérez vos informations personnelles</p>
        </div>
        
        <div class="profile-container">
            <div id="message"></div>
            
            <form id="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Prénom:</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Nom:</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Téléphone:</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?= htmlspecialchars($user['phone_number']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="country">Pays:</label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="state">Région/État:</label>
                        <input type="text" id="state" name="state" value="<?= htmlspecialchars($user['state']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="quarter">Quartier:</label>
                        <input type="text" id="quarter" name="quarter" value="<?= htmlspecialchars($user['quarter']) ?>">
                    </div>
                </div>
                
                <div class="btn-container">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        $("#profile-form").submit(function(e){
            e.preventDefault();
            
            $.ajax({
                type: "POST",
                url: "update_profile.php",
                data: $(this).serialize(),
                success: function(response){
                    if(response.includes('succès')) {
                        $("#message").html(response).removeClass('error-message').addClass('success-message');
                    } else {
                        $("#message").html(response).removeClass('success-message').addClass('error-message');
                    }
                    
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: $("#message").offset().top - 20
                    }, 500);
                },
                error: function() {
                    $("#message").html("<p>Une erreur s'est produite. Veuillez réessayer.</p>").removeClass('success-message').addClass('error-message');
                }
            });
        });
    });
    </script>
</body>
</html>