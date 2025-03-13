// Définition des descriptions pour chaque module
const moduleDescriptions = {
  // Modules communs
  dashboard:
    "Vue d'ensemble de l'activité du système avec statistiques clés, graphiques de performance et notifications importantes.",
  profile:
    "Gérez vos informations personnelles, préférences et paramètres de sécurité.",
  logout: "Déconnexion sécurisée du système.",

  // Modules administratifs
  users:
    "Gestion complète des utilisateurs: création, modification, suppression et attribution des rôles.",
  roles:
    "Définition et configuration des rôles système et leurs autorisations associées.",
  settings:
    "Configuration des paramètres globaux du système et personnalisation de l'interface.",

  // Modules produits
  products:
    "Catalogue complet des produits disponibles avec détails, prix et disponibilité.",
  categories:
    "Organisation hiérarchique des produits par catégories et sous-catégories.",
  inventory:
    "Suivi des stocks en temps réel, historique des mouvements et alertes de réapprovisionnement.",

  // Modules ventes
  orders:
    "Gestion des commandes clients: création, suivi et mise à jour du statut.",
  "new-sale":
    "Interface de création de nouvelles ventes avec sélection produits et options de paiement.",
  verifications:
    "Vérification et validation des paiements clients avant traitement des commandes.",

  // Modules finances
  transactions:
    "Suivi de toutes les transactions financières avec historique détaillé et rapports.",
  reports:
    "Génération de rapports financiers et analyses de performance des ventes.",

  // Modules fournisseurs
  suppliers:
    "Gestion des partenaires fournisseurs avec coordonnées et historique de collaboration.",
  "product-supply": "Gestion des approvisionnements et réceptions de produits.",

  // Modules livraisons
  deliveries:
    "Planification et suivi des livraisons clients avec statuts en temps réel.",

  // Modules support
  tickets:
    "Gestion des tickets de support client: création, attribution et résolution.",

  // Modules client
  shop: "Interface d'achat en ligne avec filtres produits et processus de commande simplifié.",
  "my-orders": "Suivi de vos commandes personnelles et historique d'achats.",
  support:
    "Accès au support client pour résoudre vos problèmes ou poser des questions.",
};

// Fonction pour initialiser l'interface en fonction du rôle de l'utilisateur
function initializeUserInterface(userRole) {
  // Masquer toutes les sections de navigation
  document.querySelectorAll(".nav-section").forEach((section) => {
    section.style.display = "none";
  });

  // Masquer tous les liens de navigation
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.style.display = "none";
  });

  // Afficher les sections et liens pertinents pour le rôle
  document
    .querySelectorAll(
      `.nav-section[data-roles*="all"], .nav-section[data-roles*="${userRole}"]`
    )
    .forEach((section) => {
      section.style.display = "block";
    });

  document
    .querySelectorAll(
      `.nav-link[data-roles*="all"], .nav-link[data-roles*="${userRole}"]`
    )
    .forEach((link) => {
      link.style.display = "flex";
    });

  // Mettre à jour les indicateurs de rôle
  document.getElementById("userRole").textContent = formatRoleName(userRole);
  document.getElementById("userRoleText").textContent =
    formatRoleName(userRole);

  // Activer la navigation par défaut
  activateNavigation();
}

// Fonction pour formater le nom du rôle
function formatRoleName(role) {
  const roleMapping = {
    admin: "Administrateur",
    super_admin: "Super Administrateur",
    vendeur: "Vendeur",
    gestionnaire_stock: "Gestionnaire Stock",
    responsable_financier: "Responsable Financier",
    livreur: "Livreur",
    support_client: "Support Client",
    fournisseur: "Fournisseur",
    client: "Client",
  };

  return (
    roleMapping[role] ||
    role.charAt(0).toUpperCase() + role.slice(1).replace(/_/g, " ")
  );
}

// Fonction pour activer la navigation
function activateNavigation() {
  // Gérer les clics sur les liens de navigation
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      // Supprimer la classe active de tous les liens
      document.querySelectorAll(".nav-link").forEach((l) => {
        l.classList.remove("active");
      });

      // Ajouter la classe active au lien cliqué
      this.classList.add("active");

      // Récupérer l'ID du module depuis l'attribut href
      const moduleId = this.getAttribute("href").substring(1);

      // Mettre à jour le titre de la page
      document.getElementById("pageTitle").textContent =
        this.querySelector("span").textContent;

      // Afficher la description du module dans la zone de contenu
      displayModuleDescription(moduleId);
    });
  });
}

// Fonction pour afficher la description du module
function displayModuleDescription(moduleId) {
  const description =
    moduleDescriptions[moduleId] ||
    "Description non disponible pour ce module.";

  // Créer un contenu HTML pour la description
  const descriptionHTML = `
        <div class="module-description-container p-4">
            <div class="alert alert-info">
                <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Description du module</h4>
                <p>${description}</p>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Module: ${formatModuleName(
                      moduleId
                    )}</h5>
                    <p class="card-text">Ce module fait partie de l'interface utilisateur adaptée à votre rôle.</p>
                    <p>Utilisez la barre latérale pour naviguer entre les différentes fonctionnalités.</p>
                </div>
            </div>
        </div>
    `;

  // Injecter dans la zone de contenu principale
  document.getElementById("dashboardContent").innerHTML = descriptionHTML;
}

// Fonction pour formater le nom du module
function formatModuleName(moduleId) {
  const nameMapping = {
    dashboard: "Tableau de Bord",
    profile: "Mon Profil",
    users: "Gestion des Utilisateurs",
    roles: "Gestion des Rôles",
    settings: "Paramètres Système",
    products: "Produits",
    categories: "Catégories",
    inventory: "Inventaire",
    orders: "Commandes",
    "new-sale": "Nouvelle Vente",
    verifications: "Vérifications",
    transactions: "Transactions",
    reports: "Rapports",
    suppliers: "Fournisseurs",
    "product-supply": "Approvisionnement",
    deliveries: "Gestion des Livraisons",
    tickets: "Tickets Support",
    shop: "Boutique",
    "my-orders": "Mes Commandes",
    support: "Support",
  };

  return (
    nameMapping[moduleId] ||
    moduleId.charAt(0).toUpperCase() + moduleId.slice(1).replace(/-/g, " ")
  );
}

// Fonction pour simuler le login et initialiser l'interface
function simulateLogin(userRole) {
  // Masquer le formulaire de login
  document.getElementById("loginContainer").style.display = "none";

  // Afficher le tableau de bord
  document.getElementById("dashboardContainer").style.display = "block";

  // Initialiser l'interface utilisateur
  initializeUserInterface(userRole);

  // Activer le premier lien (tableau de bord)
  document.querySelector(".nav-link.active").click();
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  // Par défaut, masquer le tableau de bord et afficher le formulaire de login
  if (document.getElementById("dashboardContainer")) {
    document.getElementById("dashboardContainer").style.display = "none";
  }

  // Ajouter des écouteurs d'événements pour les boutons de simulation de rôle
  document.querySelectorAll(".role-login-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const role = this.getAttribute("data-role");
      simulateLogin(role);
    });
  });

  // Gestion de la bascule de la barre latérale
  const toggleButton = document.getElementById("toggleSidebar");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");
  const fullLogo = document.getElementById("fullLogo");
  const miniLogo = document.getElementById("miniLogo");

  if (toggleButton && sidebar && mainContent) {
    toggleButton.addEventListener("click", function () {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("expanded");

      if (sidebar.classList.contains("collapsed")) {
        document
          .querySelectorAll(".nav-link span, .nav-section-title")
          .forEach((el) => {
            el.style.display = "none";
          });
        if (fullLogo && miniLogo) {
          fullLogo.style.display = "none";
          miniLogo.style.display = "block";
        }
      } else {
        document
          .querySelectorAll(".nav-link span, .nav-section-title")
          .forEach((el) => {
            el.style.display = "block";
          });
        if (fullLogo && miniLogo) {
          fullLogo.style.display = "block";
          miniLogo.style.display = "none";
        }
      }
    });
  }
});
