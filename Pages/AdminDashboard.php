<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechPro - Gestion des Ventes d'Ordinateurs</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="Styles/dashboard.css">
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="toggle-sidebar" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <span id="fullLogo">TechPro</span>
            <span id="miniLogo" style="display: none;">TP</span>
        </div>
        
        <div class="role-indicator" id="role-indicator">
            <i class="fas fa-id-badge me-2"></i> <span id="userRoleText">Administrateur</span>
        </div>
        
        <!-- Common navigation for all roles -->
        <div class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="#dashboard" class="nav-link active" data-roles="all">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de Bord</span>
            </a>
            <a href="#profile" class="nav-link" data-roles="all">
                <i class="fas fa-user"></i>
                <span>Mon Profil</span>
            </a>
        </div>
        
        <!-- Admin and Super Admin section -->
        <div class="nav-section" data-roles="admin,super_admin">
            <div class="nav-section-title">Administration</div>
            <a href="#users" class="nav-link" data-roles="admin,super_admin">
                <i class="fas fa-users-cog"></i>
                <span>Gestion Utilisateurs</span>
            </a>
            <a href="#roles" class="nav-link" data-roles="super_admin">
                <i class="fas fa-user-tag"></i>
                <span>Gestion des Rôles</span>
            </a>
            <a href="#settings" class="nav-link" data-roles="admin,super_admin">
                <i class="fas fa-cogs"></i>
                <span>Paramètres Système</span>
            </a>
        </div>
        
        <!-- Products section -->
        <div class="nav-section" data-roles="admin,super_admin,gestionnaire_stock,vendeur">
            <div class="nav-section-title">Produits</div>
            <a href="#products" class="nav-link" data-roles="admin,super_admin,gestionnaire_stock,vendeur">
                <i class="fas fa-laptop"></i>
                <span>Produits</span>
            </a>
            <a href="#categories" class="nav-link" data-roles="admin,super_admin,gestionnaire_stock">
                <i class="fas fa-tags"></i>
                <span>Catégories</span>
            </a>
            <a href="#inventory" class="nav-link" data-roles="admin,gestionnaire_stock">
                <i class="fas fa-boxes"></i>
                <span>Inventaire</span>
            </a>
        </div>
        
        <!-- Sales section -->
        <div class="nav-section" data-roles="admin,vendeur,responsable_financier">
            <div class="nav-section-title">Ventes</div>
            <a href="#orders" class="nav-link" data-roles="admin,vendeur,responsable_financier">
                <i class="fas fa-shopping-cart"></i>
                <span>Commandes</span>
            </a>
            <a href="#new-sale" class="nav-link" data-roles="vendeur">
                <i class="fas fa-cash-register"></i>
                <span>Nouvelle Vente</span>
            </a>
            <a href="#verifications" class="nav-link" data-roles="responsable_financier">
                <i class="fas fa-check-double"></i>
                <span>Vérifications</span>
            </a>
        </div>
        
        <!-- Finance section -->
        <div class="nav-section" data-roles="admin,responsable_financier">
            <div class="nav-section-title">Finances</div>
            <a href="#transactions" class="nav-link" data-roles="admin,responsable_financier">
                <i class="fas fa-money-bill-wave"></i>
                <span>Transactions</span>
            </a>
            <a href="#reports" class="nav-link" data-roles="admin,responsable_financier">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports</span>
            </a>
        </div>
        
        <!-- Suppliers section -->
        <div class="nav-section" data-roles="admin,gestionnaire_stock,fournisseur">
            <div class="nav-section-title">Fournisseurs</div>
            <a href="#suppliers" class="nav-link" data-roles="admin,gestionnaire_stock">
                <i class="fas fa-truck"></i>
                <span>Fournisseurs</span>
            </a>
            <a href="#product-supply" class="nav-link" data-roles="fournisseur">
                <i class="fas fa-dolly"></i>
                <span>Approvisionnement</span>
            </a>
        </div>
        
        <!-- Delivery section -->
        <div class="nav-section" data-roles="admin,livreur">
            <div class="nav-section-title">Livraisons</div>
            <a href="#deliveries" class="nav-link" data-roles="admin,livreur">
                <i class="fas fa-shipping-fast"></i>
                <span>Gestion Livraisons</span>
            </a>
        </div>
        
        <!-- Support section -->
        <div class="nav-section" data-roles="admin,support_client">
            <div class="nav-section-title">Support</div>
            <a href="#tickets" class="nav-link" data-roles="admin,support_client">
                <i class="fas fa-ticket-alt"></i>
                <span>Tickets Support</span>
            </a>
        </div>
        
        <!-- Client section -->
        <div class="nav-section" data-roles="client">
            <div class="nav-section-title">Client</div>
            <a href="#shop" class="nav-link" data-roles="client">
                <i class="fas fa-store"></i>
                <span>Boutique</span>
            </a>
            <a href="#my-orders" class="nav-link" data-roles="client">
                <i class="fas fa-box"></i>
                <span>Mes Commandes</span>
            </a>
            <a href="#support" class="nav-link" data-roles="client">
                <i class="fas fa-headset"></i>
                <span>Support</span>
            </a>
        </div>
        
        <!-- Logout link for all -->
        <div class="nav-section">
            <a href="#logout" class="nav-link text-danger" data-roles="all">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="navbar-top d-flex justify-content-between align-items-center">
            <h4 id="pageTitle">Tableau de Bord</h4>
            <div class="d-flex align-items-center">
                <div class="dropdown me-3">
                    <a href="#" class="text-secondary position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width: 320px;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0">Notifications</h6>
                        </div>
                        <div class="p-2">
                            <a href="#" class="dropdown-item py-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle p-2 me-3">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold">Nouvelle commande #1234</p>
                                        <small class="text-muted">Il y a 5 minutes</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item py-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-white rounded-circle p-2 me-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold">Stock faible pour "Laptop Pro X1"</p>
                                        <small class="text-muted">Il y a 1 heure</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item py-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle p-2 me-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold">Nouveau client inscrit</p>
                                        <small class="text-muted">Il y a 2 heures</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="p-2 border-top text-center">
                            <a href="#notifications" class="text-decoration-none">Voir toutes les notifications</a>
                        </div>
                    </div>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                        <img src="https://via.placeholder.com/40" alt="User Avatar" class="user-avatar me-2">
                        <div>
                            <div class="fw-bold" id="userName">Admin User</div>
                            <small class="text-muted" id="userRole">Administrateur</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#profile"><i class="fas fa-user me-2"></i> Mon profil</a></li>
                        <li><a class="dropdown-item" href="#settings"><i class="fas fa-cog me-2"></i> Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#logout"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div id="dashboardContent">
            <!-- Stats Cards Row -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card blue">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">56</div>
                                <div class="stat-label">Ventes Aujourd'hui</div>
                            </div>
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card green">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">24,590 €</div>
                                <div class="stat-label">Revenu Total</div>
                            </div>
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card orange">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">385</div>
                                <div class="stat-label">Produits en Stock</div>
                            </div>
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card red">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-value">12</div>
                                <div class="stat-label">Stock à Réappro</div>
                            </div>
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0">Commandes Récentes</h5>
                    <a href="#all-orders" class="btn btn-sm btn-light">Voir Tout</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Produits</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD-5263</td>
                                    <td>Dupont Jean</td>
                                    <td>Laptop Pro X1</td>
                                    <td>45,999.99 €</td>
                                    <td>11 Mars 2025</td>
                                    <td><span class="badge bg-success">Livré</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item" href="#view"><i class="fas fa-eye me-2"></i> Voir</a></li>
                                                <li><a class="dropdown-item" href="#edit"><i class="fas fa-edit me-2"></i> Modifier</a></li>
                                                <li><a class="dropdown-item text-danger" href="#delete"><i class="fas fa-trash me-2"></i> Supprimer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-5262</td>
                                    <td>Mbarga Paul</td>
                                    <td>Gaming PC Elite</td>
                                    <td>57,999.99 €</td>
                                    <td>10 Mars 2025</td>
                                    <td><span class="badge bg-warning text-dark">En cours</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                                <li><a class="dropdown-item" href="#view"><i class="fas fa-eye me-2"></i> Voir</a></li>
                                                <li><a class="dropdown-item" href="#edit"><i class="fas fa-edit me-2"></i> Modifier</a></li>
                                                <li><a class="dropdown-item text-danger" href="#delete"><i class="fas fa-trash me-2"></i> Supprimer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-5261</td>
                                    <td>Abena Marie</td>
                                    <td>Ultrabook Air, Gaming Mouse Pro</td>
                                    <td>35,499.98 €</td>
                                    <td>10 Mars 2025</td>
                                    <td><span class="badge bg-info text-dark">En préparation</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                                                <li><a class="dropdown-item" href="#view"><i class="fas fa-eye me-2"></i> Voir</a></li>
                                                <li><a class="dropdown-item" href="#edit"><i class="fas fa-edit me-2"></i> Modifier</a></li>
                                                <li><a class="dropdown-item text-danger" href="#delete"><i class="fas fa-trash me-2"></i> Supprimer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-5260</td>
                                    <td>Fotso Eric</td>
                                    <td>MacBook Pro M2</td>
                                    <td>56,999.99 €</td>
                                    <td>09 Mars 2025</td>
                                    <td><span class="badge bg-secondary">En attente</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton4" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
                                                <li><a class="dropdown-item" href="#view"><i class="fas fa-eye me-2"></i> Voir</a></li>
                                                <li><a class="dropdown-item" href="#edit"><i class="fas fa-edit me-2"></i> Modifier</a></li>
                                                <li><a class="dropdown-item text-danger" href="#delete"><i class="fas fa-trash me-2"></i> Supprimer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#ORD-5259</td>
                                    <td>Kamga Sophie</td>
                                    <td>Mini PC Creator, Mechanical Keyboard Elite</td>
                                    <td>34,999.98 €</td>
                                    <td>09 Mars 2025</td>
                                    <td><span class="badge bg-danger">Annulé</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton5" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
                                                <li><a class="dropdown-item" href="#view"><i class="fas fa-eye me-2"></i> Voir</a></li>
                                                <li><a class="dropdown-item" href="#edit"><i class="fas fa-edit me-2"></i> Modifier</a></li>
                                                <li><a class="dropdown-item text-danger" href="#delete"><i class="fas fa-trash me-2"></i> Supprimer</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Row with Two Cards: Low Stock Alert and Payment Verifications -->
            <div class="row mt-4">
                <!-- Low Stock Alert Card -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between">
                            <h5 class="mb-0">Alertes Stock</h5>
                            <a href="#inventory" class="btn btn-sm btn-dark">Gérer</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Stock Actuel</th>
                                            <th>Seuil</th>
                                            <th>Fournisseur</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Laptop Pro X1</td>
                                            <td><span class="badge bg-danger">3</span></td>
                                            <td>5</td>
                                            <td>HP Cameroon</td>
                                            <td><button class="btn btn-sm btn-warning">Commander</button></td>
                                        </tr>
                                        <tr>
                                            <td>Gaming PC Elite</td>
                                            <td><span class="badge bg-danger">2</span></td>
                                            <td>5</td>
                                            <td>Dell Technologies</td>
                                            <td><button class="btn btn-sm btn-warning">Commander</button></td>
                                        </tr>
                                        <tr>
                                            <td>Mechanical Keyboard Elite</td>
                                            <td><span class="badge bg-warning text-dark">4</span></td>
                                            <td>5</td>
                                            <td>Lenovo Group</td>
                                            <td><button class="btn btn-sm btn-warning">Commander</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Payment Verifications Card (For Finance Manager) -->
                <div class="col-md-6" id="financeSectionCard">
                    <div class="card">
                        <div class="card-header bg-success text-white d-flex justify-content-between">
                            <h5 class="mb-0">Paiements à Vérifier</h5>
                            <a href="#verifications" class="btn btn-sm btn-light">Tout Voir</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Commande</th>
                                            <th>Client</th>
                                            <th>Montant</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-5261</td>
                                            <td>Abena Marie</td>
                                            <td>35,499.98 €</td>
                                            <td>10 Mars 2025</td>
                                            <td>
                                                <button class="btn btn-sm btn-success">Vérifier</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-5260</td>
                                            <td>Fotso Eric</td>
                                            <td>56,999.99 €</td>
                                            <td>09 Mars 2025</td>
                                            <td>
                                                <button class="btn btn-sm btn-success">Vérifier</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-5258</td>
                                            <td>Nkeng Pierre</td>
                                            <td>24,999.99 €</td>
                                            <td>08 Mars 2025</td>
                                            <td>
                                                <button class="btn btn-sm btn-success">Vérifier</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Performance Charts Row -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Performance des Ventes (2025)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" style="width: 100%; height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Produits Populaires</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="productsChart" style="width: 100%; height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Latest Activities -->
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Activités Récentes</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle p-2 me-3">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div>
                                    <p class="mb-0">Nouvelle commande #ORD-5263 par Dupont Jean</p>
                                    <small class="text-muted">Il y a 5 minutes par Vendeur Sales</small>
                                </div>
                            </div>
                            <span><i class="fas fa-chevron-right text-muted"></i></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning text-white rounded-circle p-2 me-3">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div>
                                    <p class="mb-0">Mise à jour de l'inventaire pour "Laptop Pro X1"</p>
                                    <small class="text-muted">Il y a 30 minutes par Stock Manager</small>
                                </div>
                            </div>
                            <span><i class="fas fa-chevron-right text-muted"></i></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info text-white rounded-circle p-2 me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <p class="mb-0">Nouveau client enregistré: Kamga Sophie</p>
                                    <small class="text-muted">Il y a 2 heures</small>
                                </div>
                            </div>
                            <span><i class="fas fa-chevron-right text-muted"></i></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded-circle p-2 me-3">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div>
                                    <p class="mb-0">Paiement vérifié pour la commande #ORD-5259</p>
                                    <small class="text-muted">Il y a 4 heures par Finance Manager</small>
                                </div>
                            </div>
                            <span><i class="fas fa-chevron-right text-muted"></i></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <div class="bg-warning text-white rounded-circle p-2 me-3">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <p class="mb-0">Mise à jour de l'inventaire pour "Laptop Pro X1"</p>
                    <small class="text-muted">Il y a 30 minutes par Stock Manager</small>
                </div>
            </div>
            <span><i class="fas fa-chevron-right text-muted"></i></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <div class="bg-info text-white rounded-circle p-2 me-3">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="mb-0">Nouveau client enregistré: Kamga Sophie</p>
                    <small class="text-muted">Il y a 2 heures</small>
                </div>
            </div>
            <span><i class="fas fa-chevron-right text-muted"></i></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <div class="bg-success text-white rounded-circle p-2 me-3">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <p class="mb-0">Paiement vérifié pour la commande #ORD-5259</p>
                    <small class="text-muted">Il y a 4 heures par Finance Manager</small>
                </div>
            </div>
            <span><i class="fas fa-chevron-right text-muted"></i></span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle p-2 me-3">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <p class="mb-0">Livraison effectuée pour la commande #ORD-5257</p>
                    <small class="text-muted">Il y a 6 heures par Delivery Person</small>
                </div>
            </div>
            <span><i class="fas fa-chevron-right text-muted"></i></span>
        </li>
    </ul>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Custom JS -->
<script src="DasboardScript.js"></script>

</body>
</html>