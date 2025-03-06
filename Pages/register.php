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

// Initialize variables to prevent undefined index errors
$first_name = $last_name = $email = $phone_number = $country = $state = $quarter = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone_number = sanitize_input($_POST['phone_number'] ?? '');
    $country = sanitize_input($_POST['country'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $quarter = sanitize_input($_POST['quarter'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Basic validation (minimal)
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $errors[] = "Veuillez remplir tous les champs obligatoires.";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
    try {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Log the attempt for debugging
        error_log("Attempting to register user: $email");
        
        // Prepare and execute insert statement
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone_number, country, state, quarter, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $errors[] = "Erreur de préparation de la requête: " . $conn->error;
        } else {
            $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone_number, $country, $state, $quarter, $password_hash);
            
            if ($stmt->execute()) {
                // Redirect to login page
                header("Location: login.php");
                exit();
            } else {
                error_log("Execute failed: " . $stmt->error);
                $errors[] = "L'inscription a échoué. Veuillez réessayer. Erreur: " . $stmt->error;
            }
        }
    } catch (Exception $e) {
        error_log("Registration exception: " . $e->getMessage());
        $errors[] = "Une erreur s'est produite lors de l'inscription: " . $e->getMessage();
    }
}
}

// Page-specific title
$pageTitle = "TechPro - Inscription";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/register.css">
    <link rel="stylesheet" href="Styles/styles.css">
    
</head>
<body>
    <?php include 'header.php'; ?>
<div class="main-content"> 
   <section class="registration-section">
    <div class="registration-container">
        <h2>Inscription</h2>
        
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
        
        <!-- Progress indicators -->
        <div class="registration-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Informations personnelles</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Localisation</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Sécurité</div>
            </div>
        </div>
        
        <!-- This is just the form section part of your register.php file -->
<form class="registration-form" method="POST" action="">
    <!-- Personal Information Section -->
    <div class="form-section active" id="section-1">
        <h3 class="section-title"><i class="fas fa-user"></i> Informations personnelles</h3>
        
        <div class="form-group">
            <label for="first_name">Prénom</label>
            <input type="text" id="first_name" name="first_name" 
                   value="<?= htmlspecialchars($first_name) ?>">
        </div>
        
        <div class="form-group">
            <label for="last_name">Nom de famille</label>
            <input type="text" id="last_name" name="last_name" 
                   value="<?= htmlspecialchars($last_name) ?>">
        </div>
        
        <div class="form-group full-width">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($email) ?>">
        </div>
        
        <div class="form-group full-width">
            <label for="phone_number">Numéro de téléphone</label>
            <input type="tel" id="phone_number" name="phone_number" 
                   value="<?= htmlspecialchars($phone_number) ?>">
        </div>
    </div>
    
    <!-- Location Information Section -->
    <div class="form-section" id="section-2">
        <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Localisation</h3>
        
        <div class="form-group">
            <label for="country">Pays</label>
            <input type="text" id="country" name="country" 
                   value="<?= htmlspecialchars($country) ?>">
        </div>
        
        <div class="form-group">
            <label for="state">État/Région</label>
            <input type="text" id="state" name="state" 
                   value="<?= htmlspecialchars($state) ?>">
        </div>
        
        <div class="form-group full-width">
            <label for="quarter">Quartier</label>
            <input type="text" id="quarter" name="quarter" 
                   value="<?= htmlspecialchars($quarter) ?>">
        </div>
    </div>
    
    <!-- Security Information Section -->
    <div class="form-section" id="section-3">
        <h3 class="section-title"><i class="fas fa-lock"></i> Sécurité du compte</h3>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>
    </div>
    
    <!-- Navigation buttons -->
    <div class="form-navigation full-width">
        <button type="button" id="prev-btn" class="nav-btn" disabled>
            <i class="fas fa-arrow-left"></i> Précédent
        </button>
        
        <button type="button" id="next-btn" class="nav-btn">
            Suivant <i class="fas fa-arrow-right"></i>
        </button>
        
        <button type="submit" id="submit-btn" class="register-btn" style="display: none;">
            <i class="fas fa-user-plus"></i> S'inscrire
        </button>
    </div>
</form>
    </div>
 </section>
</div>
    <?php include 'footer.php'; ?>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.form-section');
    const steps = document.querySelectorAll('.step');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    let currentSection = 0;
    
    // Function to show current section
    function showSection(sectionIndex) {
        sections.forEach((section, index) => {
            section.classList.remove('active');
            steps[index].classList.remove('active', 'completed');
            
            if (index < sectionIndex) {
                steps[index].classList.add('completed');
            } else if (index === sectionIndex) {
                steps[index].classList.add('active');
            }
        });
        
        sections[sectionIndex].classList.add('active');
        
        // Update buttons
        prevBtn.disabled = sectionIndex === 0;
        
        if (sectionIndex === sections.length - 1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'flex';
        } else {
            nextBtn.style.display = 'flex';
            submitBtn.style.display = 'none';
        }
    }
    
    // Initialize first section
    showSection(currentSection);
    
    // Event listeners for navigation
    nextBtn.addEventListener('click', function() {
        if (currentSection < sections.length - 1) {
            currentSection++;
            showSection(currentSection);
        }
    });
    
    prevBtn.addEventListener('click', function() {
        if (currentSection > 0) {
            currentSection--;
            showSection(currentSection);
        }
    });
});
</script>
</body>
</html>