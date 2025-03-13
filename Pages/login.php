<?php
session_start();
require_once 'DatabaseConnection/db_config.php';

// Sanitize input function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables
$email = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $errors[] = "Veuillez entrer votre email et mot de passe.";
    } else {
        try {
            // First check if user exists in users table
            $stmt = $conn->prepare("SELECT u.user_id, u.email, u.password_hash, u.first_name, u.last_name, r.role_name, r.role_id 
                                    FROM users u 
                                    JOIN roles r ON u.role_id = r.role_id 
                                    WHERE u.email = ?");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                $errors[] = "Erreur de préparation de la requête: " . $conn->error;
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    // User found in users table
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $user['password_hash'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                        $_SESSION['user_role'] = $user['role_name'];
                        $_SESSION['role_id'] = $user['role_id'];
                        
                        // Redirect based on role
                        switch ($user['role_id']) {
                            case 1: // admin
                                header("Location: admin/dashboard.php");
                                break;
                            case 2: // client
                                header("Location: index.php");
                                break;
                            case 3: // vendeur
                                header("Location: vendeur/dashboard.php");
                                break;
                            case 4: // gestionnaire_stock
                                header("Location: stock/dashboard.php");
                                break;
                            case 5: 
                                header("Location: finance/dashboard.php");
                                break;
                        }
                        exit();
                    } else {
                        $errors[] = "Email ou mot de passe incorrect.";
                    }
                } else {
                    // If not found in users table, check admins table (for backward compatibility)
                    $stmt = $conn->prepare("SELECT admin_id, email, password_hash, first_name, last_name FROM admins WHERE email = ?");
                    
                    if (!$stmt) {
                        error_log("Prepare failed: " . $conn->error);
                        $errors[] = "Erreur de préparation de la requête: " . $conn->error;
                    } else {
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows === 1) {
                            $admin = $result->fetch_assoc();
                            
                            // Verify password
                            if (password_verify($password, $admin['password_hash'])) {
                                // Set session variables
                                $_SESSION['user_id'] = $admin['admin_id'];
                                $_SESSION['user_email'] = $admin['email'];
                                $_SESSION['user_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                                $_SESSION['user_role'] = 'admin'; // Default to admin role for backward compatibility
                                $_SESSION['role_id'] = 1; // Assuming admin role_id is 1
                                
                                // Redirect to admin dashboard
                                header("Location: admin/dashboard.php");
                                exit();
                            } else {
                                $errors[] = "Email ou mot de passe incorrect.";
                            }
                        } else {
                            $errors[] = "Email ou mot de passe incorrect.";
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Login exception: " . $e->getMessage());
            $errors[] = "Une erreur s'est produite lors de la connexion: " . $e->getMessage();
        }
    }
}

// Page-specific title
$pageTitle = "TechPro - Connexion";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/login.css">
    <link rel="stylesheet" href="Styles/styles.css">
    
</head>
<body>
    <?php include 'header.php'; ?>
<div class="main-content">
    <section class="login-section">
        
        <div class="login-container">
            <div class="login-content">
                <div class="login-welcome">
                    <h2>Bon retour parmi nous !</h2>
                    <p>Connectez-vous pour accéder à votre compte et profiter de tous nos services.</p>
                    <div class="welcome-illustration">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                
                <div class="login-form-container">
                    <h2>Connexion</h2>
                    
                    <?php
                    // Display errors if any
                    if (!empty($errors)) {
                        echo "<div class='error-message'>";
                        foreach ($errors as $error) {
                            echo "<p>" . htmlspecialchars($error) . "</p>";
                        }
                        echo "</div>";
                    }
                    ?>
                    
                    <form class="login-form" method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Votre adresse email">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Votre mot de passe">
                                <i class="fas fa-eye toggle-password" id="toggle-password"></i>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Se souvenir de moi</label>
                            </div>
                            <a href="forgot-password.php" class="forgot-password">Mot de passe oublié?</a>
                        </div>
                        
                        <button type="submit" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                    
                    <div class="alternative-login">
                        <p>Ou connectez-vous avec</p>
                        <div class="social-login">
                            <button class="social-btn google">
                                <i class="fab fa-google"></i>
                            </button>
                            <button class="social-btn facebook">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button class="social-btn linkedin">
                                <i class="fab fa-linkedin-in"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="register-link">
                        <p>Pas encore de compte? <a href="register.php">S'inscrire</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
     </div>

    <?php include 'footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>