<?php
// Start session
session_start();

// Message handling logic
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageContent = trim($_POST['message'] ?? '');
    
    // Simple validation
    if (empty($name) || empty($email) || empty($subject) || empty($messageContent)) {
        $message = 'Veuillez remplir tous les champs du formulaire.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez fournir une adresse email valide.';
        $messageType = 'error';
    } else {
        // Instead of using mail() function, we'll save to database or file
        // For demonstration, we'll save to a log file in a safe location
        $logDir = dirname(__DIR__) . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/contact_messages.log';
        $timestamp = date('Y-m-d H:i:s');
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        
        $logMessage = "
==========================================================
Date: $timestamp
IP: $ipAddress
Name: $name
Email: $email
Subject: $subject
Message: 
$messageContent
==========================================================

";
        
        $logged = file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // In a production environment, you would configure a proper SMTP solution
        // For example, using PHPMailer library
        
        if ($logged !== false) {
            // Message was logged successfully
            $message = 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';
            $messageType = 'success';
            
            // Clear form data after successful submission
            $name = $email = $subject = $messageContent = '';
            
            // Store in session for modal display
            $_SESSION['modal_message'] = $message;
            $_SESSION['modal_type'] = 'success';
        } else {
            $message = "Une erreur s'est produite lors de l'envoi du message. Veuillez réessayer plus tard.";
            $messageType = 'error';
            
            // Store in session for modal display
            $_SESSION['modal_message'] = $message;
            $_SESSION['modal_type'] = 'error';
        }
    }
}

// Page-specific title
$pageTitle = "TechPro - Contact";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/styles.css">
    <style>
        .contact-header {
            background-color: #f8f9fa;
            padding: 60px 20px;
            text-align: center;
        }
        
        .contact-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 20px;
        }
        
        .contact-header p {
            max-width: 700px;
            margin: 0 auto;
            color: #666;
            line-height: 1.6;
        }
        
        .company-motto {
            font-size: 1.2rem;
            font-style: italic;
            color: #4285f4;
            margin-top: 15px;
            font-weight: 500;
        }
        
        .contact-content {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 50px;
        }
        
        .contact-info {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .contact-info h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        
        .info-icon {
            width: 50px;
            height: 50px;
            background-color: #4285f4;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .info-icon i {
            font-size: 20px;
        }
        
        .info-text h4 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 5px;
        }
        
        .info-text p {
            color: #666;
            line-height: 1.5;
        }
        
        .social-links {
            margin-top: 30px;
        }
        
        .social-links h4 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 15px;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            background-color: #eef2ff;
            color: #4285f4;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background-color: #4285f4;
            color: white;
        }
        
        .contact-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .contact-form h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            color: #333;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4285f4;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            padding: 12px 25px;
            font-size: 1rem;
            color: #fff;
            background-color: #4285f4;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .submit-btn:hover {
            background-color: #3367d6;
        }
        
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
        
        .message-box.error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            display: block;
        }
        
        .message-box.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            display: block;
        }
        
        .map-container {
            margin-top: 60px;
            height: 450px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .directions-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #4285f4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        
        .directions-btn:hover {
            background-color: #3367d6;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 10% auto;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .modal-icon {
            margin-right: 15px;
            font-size: 2rem;
        }
        
        .modal-icon.success {
            color: #10b981;
        }
        
        .modal-icon.error {
            color: #ef4444;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .modal-body {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
            margin-bottom: 20px;
        }
        
        .modal-footer {
            text-align: right;
        }
        
        .modal-btn {
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            background-color: #4285f4;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .modal-btn:hover {
            background-color: #3367d6;
        }
        
        .modal-btn.success {
            background-color: #10b981;
        }
        
        .modal-btn.success:hover {
            background-color: #059669;
        }
        
        .modal-btn.error {
            background-color: #ef4444;
        }
        
        .modal-btn.error:hover {
            background-color: #dc2626;
        }
        
        /* Media Queries */
        @media (max-width: 900px) {
            .contact-content {
                grid-template-columns: 1fr;
            }
            
            .contact-info {
                order: 2;
            }
            
            .contact-form {
                order: 1;
                margin-bottom: 30px;
            }
            
            .modal-content {
                margin: 20% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-header">
                <div class="modal-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="modal-title">Succès!</h3>
            </div>
            <div class="modal-body" id="successModalText">
                Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
            </div>
            <div class="modal-footer">
                <button class="modal-btn success">D'accord</button>
            </div>
        </div>
    </div>
    
    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-header">
                <div class="modal-icon error">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="modal-title">Erreur!</h3>
            </div>
            <div class="modal-body" id="errorModalText">
                Une erreur s'est produite lors de l'envoi du message. Veuillez réessayer plus tard.
            </div>
            <div class="modal-footer">
                <button class="modal-btn error">Fermer</button>
            </div>
        </div>
    </div>

   <section class="contact-header" id="contact">
    <h1>Contactez-nous</h1>
    <p>Vous avez des questions, des suggestions ou besoin d'assistance ? N'hésitez pas à nous contacter. Notre équipe est là pour vous aider.</p>
    <div class="company-motto">
        "Votre vision, notre expertise - Ensemble vers l'excellence technologique"
    </div>
</section>

<section class="contact-content">
    <aside class="contact-info">
        <h3>Informations de contact</h3>
        
        <div class="info-item">
            <div class="info-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="info-text">
                <h4>Notre adresse</h4>
                <p>Ndokoti, Carrefour Ndokoti<br>Douala, Cameroun</p>
                <a href="https://www.google.com/maps/dir//Carrefour+Ndokoti,+Douala" target="_blank" class="directions-btn">
                    <i class="fas fa-directions"></i> Obtenir l'itinéraire
                </a>
            </div>
        </div>
        
        <div class="info-item">
            <div class="info-icon">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div class="info-text">
                <h4>Téléphone</h4>
                <p>+237 690 653 943</p>
            </div>
        </div>
        
        <div class="info-item">
            <div class="info-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="info-text">
                <h4>Email</h4>
                <p>guyp5855@gmail.com</p>
            </div>
        </div>
        
        <div class="info-item">
            <div class="info-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="info-text">
                <h4>Contact Principal</h4>
                <p>GRACE KAKABI</p>
            </div>
        </div>
        
        <div class="info-item">
            <div class="info-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="info-text">
                <h4>Heures d'ouverture</h4>
                <p>Lun - Ven : 8h00 - 18h00<br>Sam : 9h00 - 16h00<br>Dim : Fermé</p>
            </div>
        </div>
        
        <div class="social-links">
            <h4>Suivez-nous</h4>
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </aside>
    
    <div class="contact-form">
        <h3>Envoyez-nous un message</h3>
        
        <?php if ($message): ?>
            <div class="message-box <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form action="contact.php" method="POST">
            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="subject">Sujet</label>
                <input type="text" id="subject" name="subject" class="form-control" value="<?= htmlspecialchars($subject ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" class="form-control" required><?= htmlspecialchars($messageContent ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Envoyer le message
            </button>
        </form>
    </div>
</section>

<div class="map-container">
    <!-- Updated to point to Ndokoti, Douala -->
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.753127242905!2d9.732552075986252!3d4.0469870789694!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1061126db8a9f555%3A0x6fa6c2b71a5f8be6!2sCarrefour%20Ndokoti%2C%20Douala!5e0!3m2!1sfr!2scm!4v1710445512362!5m2!1sfr!2scm" allowfullscreen="" loading="lazy"></iframe>
</div>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.querySelector('.contact-form form');
        const messageBox = document.querySelector('.message-box');
        const successModal = document.getElementById('successModal');
        const errorModal = document.getElementById('errorModal');
        const successModalText = document.getElementById('successModalText');
        const errorModalText = document.getElementById('errorModalText');
        
        // Modal functions
        function openModal(modal, message = null) {
            if (modal === successModal && message) {
                successModalText.textContent = message;
            } else if (modal === errorModal && message) {
                errorModalText.textContent = message;
            }
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        }
        
        function closeModal(modal) {
            modal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
        }
        
        // Close modal when clicking on X or the button
        document.querySelectorAll('.close-modal, .modal-btn').forEach(function(element) {
            element.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });
        
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        });
        
        // Check if we should show a modal (from session)
        <?php if (isset($_SESSION['modal_type']) && isset($_SESSION['modal_message'])): ?>
            <?php if ($_SESSION['modal_type'] === 'success'): ?>
                openModal(successModal, '<?= addslashes($_SESSION['modal_message']) ?>');
            <?php elseif ($_SESSION['modal_type'] === 'error'): ?>
                openModal(errorModal, '<?= addslashes($_SESSION['modal_message']) ?>');
            <?php endif; ?>
            <?php
            // Clear the session variables
            unset($_SESSION['modal_type']);
            unset($_SESSION['modal_message']);
            ?>
        <?php endif; ?>
        
        // Form validation
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const subject = document.getElementById('subject').value.trim();
                const message = document.getElementById('message').value.trim();
                
                // Client-side validation
                if (!name || !email || !subject || !message) {
                    e.preventDefault();
                    openModal(errorModal, 'Veuillez remplir tous les champs du formulaire.');
                } else if (!isValidEmail(email)) {
                    e.preventDefault();
                    openModal(errorModal, 'Veuillez fournir une adresse email valide.');
                } else {
                    // Show loading indicator
                    const submitBtn = document.querySelector('.submit-btn');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                        submitBtn.disabled = true;
                    }
                }
            });
        }
        
        // Auto-hide message box after some time
        if (messageBox && messageBox.style.display === 'block') {
            setTimeout(function() {
                messageBox.style.display = 'none';
            }, 5000);
        }
        
        // Helper function to validate email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });
</script>
</body>
</html>