<?php
require_once '../DatabaseConnection/db_config.php';
header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'message' => 'Requête invalide'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // AJOUTER FOURNISSEUR
    if ($action === 'add') {
        if (!empty($_POST['nom_entreprise']) && !empty($_POST['nom_contact']) && !empty($_POST['email'])) {
            $nom_entreprise = trim($_POST['nom_entreprise']);
            $nom_contact = trim($_POST['nom_contact']);
            $email = trim($_POST['email']);
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');

            // Vérifier si l'email existe déjà
            $check_sql = "SELECT COUNT(*) as count FROM suppliers WHERE email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
            } else {
                // Insérer un nouveau fournisseur
                $insert_sql = "INSERT INTO suppliers (company_name, contact_name, email, phone_number, address) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('sssss', $nom_entreprise, $nom_contact, $email, $telephone, $adresse);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Fournisseur ajouté avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de l\'ajout du fournisseur.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
    }

    // MODIFIER FOURNISSEUR
    elseif ($action === 'edit') {
        if (!empty($_POST['id_fournisseur']) && !empty($_POST['nom_entreprise']) && !empty($_POST['nom_contact']) && !empty($_POST['email'])) {
            $id_fournisseur = intval($_POST['id_fournisseur']);
            $nom_entreprise = trim($_POST['nom_entreprise']);
            $nom_contact = trim($_POST['nom_contact']);
            $email = trim($_POST['email']);
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');

            // Vérifier si un autre fournisseur utilise le même email
            $check_sql = "SELECT COUNT(*) as count FROM suppliers WHERE email = ? AND supplier_id != ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('si', $email, $id_fournisseur);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Un autre fournisseur utilise déjà cet email.'];
            } else {
                // Mettre à jour le fournisseur
                $update_sql = "UPDATE suppliers SET company_name=?, contact_name=?, email=?, phone_number=?, address=? WHERE supplier_id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param('sssssi', $nom_entreprise, $nom_contact, $email, $telephone, $adresse, $id_fournisseur);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Fournisseur mis à jour avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour du fournisseur.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
    }

    // SUPPRIMER FOURNISSEUR
    elseif ($action === 'delete' && isset($_POST['id_fournisseur'])) {
        $id_fournisseur = intval($_POST['id_fournisseur']);

        // Vérifier si le fournisseur est lié à des produits
        $check_products_sql = "SELECT COUNT(*) as count FROM products WHERE supplier_id = ?";
        $stmt = $conn->prepare($check_products_sql);
        $stmt->bind_param('i', $id_fournisseur);
        $stmt->execute();
        $check_result = $stmt->get_result()->fetch_assoc();

        if ($check_result['count'] > 0) {
            $response = ['success' => false, 'message' => 'Impossible de supprimer ce fournisseur, des produits y sont associés.'];
        } else {
            // Supprimer le fournisseur
            $delete_sql = "DELETE FROM suppliers WHERE supplier_id=?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param('i', $id_fournisseur);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Fournisseur supprimé avec succès.'];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la suppression du fournisseur.'];
            }
        }
        $stmt->close();
    }
}

// Retourner la réponse JSON
echo json_encode($response);
exit;
?>
<?php
require_once '../DatabaseConnection/db_config.php';
header('Content-Type: application/json');
session_start();

$response = ['success' => false, 'message' => 'Requête invalide'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ✅ AJOUTER UN FOURNISSEUR
    if ($action === 'add') {
        if (!empty($_POST['nom_entreprise']) && !empty($_POST['nom_contact']) && !empty($_POST['email'])) {
            $nom_entreprise = trim($_POST['nom_entreprise']);
            $nom_contact = trim($_POST['nom_contact']);
            $email = trim($_POST['email']);
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');

            // Vérifier si l'email du fournisseur existe déjà
            $check_sql = "SELECT COUNT(*) as count FROM suppliers WHERE email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Cet email est déjà utilisé par un fournisseur.'];
            } else {
                // Insérer le nouveau fournisseur
                $insert_sql = "INSERT INTO suppliers (company_name, contact_name, email, phone_number, address) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param('sssss', $nom_entreprise, $nom_contact, $email, $telephone, $adresse);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Fournisseur ajouté avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de l\'ajout du fournisseur.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
    }

    // ✅ MODIFIER UN FOURNISSEUR
    elseif ($action === 'edit') {
        if (!empty($_POST['id_fournisseur']) && !empty($_POST['nom_entreprise']) && !empty($_POST['nom_contact']) && !empty($_POST['email'])) {
            $id_fournisseur = intval($_POST['id_fournisseur']);
            $nom_entreprise = trim($_POST['nom_entreprise']);
            $nom_contact = trim($_POST['nom_contact']);
            $email = trim($_POST['email']);
            $telephone = trim($_POST['telephone'] ?? '');
            $adresse = trim($_POST['adresse'] ?? '');

            // Vérifier si un autre fournisseur utilise cet email
            $check_sql = "SELECT COUNT(*) as count FROM suppliers WHERE email = ? AND supplier_id != ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param('si', $email, $id_fournisseur);
            $stmt->execute();
            $check_result = $stmt->get_result()->fetch_assoc();

            if ($check_result['count'] > 0) {
                $response = ['success' => false, 'message' => 'Un autre fournisseur utilise déjà cet email.'];
            } else {
                // Mettre à jour le fournisseur
                $update_sql = "UPDATE suppliers SET company_name=?, contact_name=?, email=?, phone_number=?, address=? WHERE supplier_id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param('sssssi', $nom_entreprise, $nom_contact, $email, $telephone, $adresse, $id_fournisseur);

                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Fournisseur mis à jour avec succès.'];
                } else {
                    $response = ['success' => false, 'message' => 'Erreur lors de la mise à jour du fournisseur.'];
                }
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'message' => 'Tous les champs sont obligatoires.'];
        }
    }

    // ✅ SUPPRIMER UN FOURNISSEUR
    elseif ($action === 'delete' && isset($_POST['id_fournisseur'])) {
        $id_fournisseur = intval($_POST['id_fournisseur']);

        // Vérifier si le fournisseur est lié à des produits
        $check_products_sql = "SELECT COUNT(*) as count FROM products WHERE supplier_id = ?";
        $stmt = $conn->prepare($check_products_sql);
        $stmt->bind_param('i', $id_fournisseur);
        $stmt->execute();
        $check_result = $stmt->get_result()->fetch_assoc();

        if ($check_result['count'] > 0) {
            $response = ['success' => false, 'message' => 'Impossible de supprimer ce fournisseur car des produits y sont associés.'];
        } else {
            // Supprimer le fournisseur
            $delete_sql = "DELETE FROM suppliers WHERE supplier_id=?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param('i', $id_fournisseur);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Fournisseur supprimé avec succès.'];
            } else {
                $response = ['success' => false, 'message' => 'Erreur lors de la suppression du fournisseur.'];
            }
        }
        $stmt->close();
    }
}

// ✅ RÉCUPÉRER TOUS LES FOURNISSEURS
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM suppliers ORDER BY company_name ASC";
    $result = $conn->query($query);
    $suppliers = [];

    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }

    $response = ['success' => true, 'data' => $suppliers];
}

// Retourner la réponse JSON
echo json_encode($response);
exit;
?>
