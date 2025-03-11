// Wait for the document to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
  // Sidebar Toggle Functionality
  const toggleSidebar = document.getElementById("toggleSidebar");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");
  const fullLogo = document.getElementById("fullLogo");
  const miniLogo = document.getElementById("miniLogo");

  toggleSidebar.addEventListener("click", function () {
    sidebar.classList.toggle("sidebar-collapsed");
    mainContent.classList.toggle("main-content-expanded");
    toggleSidebar.classList.toggle("toggle-sidebar-collapsed");

    // Toggle logo display
    if (sidebar.classList.contains("sidebar-collapsed")) {
      fullLogo.style.display = "none";
      miniLogo.style.display = "block";
    } else {
      fullLogo.style.display = "block";
      miniLogo.style.display = "none";
    }
  });

  // Navigation Links Click Handler
  const navLinks = document.querySelectorAll(".nav-link");
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      // Prevent default only if href is # to prevent page reload
      if (this.getAttribute("href") === "#") {
        e.preventDefault();
      }

      // Remove active class from all links
      navLinks.forEach((l) => l.classList.remove("active"));

      // Add active class to clicked link
      this.classList.add("active");

      // Update page title
      const pageTitle = document.getElementById("pageTitle");
      if (pageTitle) {
        pageTitle.textContent = this.querySelector("span").textContent;
      }
    });
  });

  // Role-based UI elements
  function updateUIForRole(role) {
    // Update role indicator
    const userRoleText = document.getElementById("userRoleText");
    const userRoleHeader = document.getElementById("userRole");
    const userName = document.getElementById("userName");

    // Set default role elements
    if (!role) role = "admin";

    // Update role text in UI
    if (userRoleText) {
      userRoleText.textContent = formatRoleName(role);
    }

    if (userRoleHeader) {
      userRoleHeader.textContent = formatRoleName(role);
    }

    // Update user name based on role
    if (userName) {
      switch (role) {
        case "admin":
          userName.textContent = "Admin User";
          break;
        case "super_admin":
          userName.textContent = "Super Admin";
          break;
        case "client":
          userName.textContent = "Client User";
          break;
        case "vendeur":
          userName.textContent = "Vendeur Sales";
          break;
        case "gestionnaire_stock":
          userName.textContent = "Stock Manager";
          break;
        case "fournisseur":
          userName.textContent = "Supplier Partner";
          break;
        case "responsable_financier":
          userName.textContent = "Finance Manager";
          break;
        case "livreur":
          userName.textContent = "Delivery Person";
          break;
        case "support_client":
          userName.textContent = "Support Agent";
          break;
        default:
          userName.textContent = "User";
      }
    }

    // Show/hide menu items based on role
    const navSections = document.querySelectorAll(".nav-section");
    navSections.forEach((section) => {
      const roles = section.getAttribute("data-roles");
      if (roles) {
        const roleArray = roles.split(",");
        if (roleArray.includes(role) || roleArray.includes("all")) {
          section.style.display = "block";
        } else {
          section.style.display = "none";
        }
      }
    });

    // Show/hide nav links based on role
    const allNavLinks = document.querySelectorAll(".nav-link");
    allNavLinks.forEach((link) => {
      const roles = link.getAttribute("data-roles");
      if (roles) {
        const roleArray = roles.split(",");
        if (roleArray.includes(role) || roleArray.includes("all")) {
          link.style.display = "flex";
        } else {
          link.style.display = "none";
        }
      }
    });

    // Show/hide finance verification card based on role
    const financeCard = document.getElementById("financeSectionCard");
    if (financeCard) {
      if (
        role === "responsable_financier" ||
        role === "admin" ||
        role === "super_admin"
      ) {
        financeCard.style.display = "block";
      } else {
        financeCard.style.display = "none";
      }
    }

    // Format charts based on role
    initCharts(role);
  }

  // Format role name for display
  function formatRoleName(role) {
    switch (role) {
      case "admin":
        return "Administrateur";
      case "super_admin":
        return "Super Administrateur";
      case "client":
        return "Client";
      case "vendeur":
        return "Vendeur";
      case "gestionnaire_stock":
        return "Gestionnaire Stock";
      case "fournisseur":
        return "Fournisseur";
      case "responsable_financier":
        return "Responsable Financier";
      case "livreur":
        return "Livreur";
      case "support_client":
        return "Support Client";
      default:
        return "Utilisateur";
    }
  }

  // Role Switcher for Demo
  // Add a role selector dropdown to the interface
  const roleSelector = document.createElement("div");
  roleSelector.className = "position-fixed bottom-0 end-0 p-3";
  roleSelector.innerHTML = `
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="roleSwitcherButton" data-bs-toggle="dropdown" aria-expanded="false">
                Changer de Rôle (Démo)
            </button>
            <ul class="dropdown-menu" aria-labelledby="roleSwitcherButton">
                <li><a class="dropdown-item" href="#" data-role="admin">Administrateur</a></li>
                <li><a class="dropdown-item" href="#" data-role="super_admin">Super Administrateur</a></li>
                <li><a class="dropdown-item" href="#" data-role="client">Client</a></li>
                <li><a class="dropdown-item" href="#" data-role="vendeur">Vendeur</a></li>
                <li><a class="dropdown-item" href="#" data-role="gestionnaire_stock">Gestionnaire Stock</a></li>
                <li><a class="dropdown-item" href="#" data-role="fournisseur">Fournisseur</a></li>
                <li><a class="dropdown-item" href="#" data-role="responsable_financier">Responsable Financier</a></li>
                <li><a class="dropdown-item" href="#" data-role="livreur">Livreur</a></li>
                <li><a class="dropdown-item" href="#" data-role="support_client">Support Client</a></li>
            </ul>
        </div>
    `;
  document.body.appendChild(roleSelector);

  // Add event listeners to role selector items
  const roleSwitchers = document.querySelectorAll("[data-role]");
  roleSwitchers.forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const role = this.getAttribute("data-role");
      updateUIForRole(role);
    });
  });

  // Charts initialization
  function initCharts(role) {
    // Sales Chart
    const salesChartCtx = document.getElementById("salesChart");
    if (salesChartCtx) {
      // Clear previous chart if it exists
      if (window.salesChart) {
        window.salesChart.destroy();
      }

      // Chart data - can be customized based on role
      const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
      ];
      const salesData = [
        24000, 31000, 27000, 35000, 42000, 39000, 28000, 36000, 46000, 49000,
        53000, 57000,
      ];

      window.salesChart = new Chart(salesChartCtx, {
        type: "line",
        data: {
          labels: months.slice(0, 3), // Show only first 3 months of the year
          datasets: [
            {
              label: "Ventes (€)",
              backgroundColor: "rgba(54, 162, 235, 0.2)",
              borderColor: "rgba(54, 162, 235, 1)",
              data: salesData.slice(0, 3), // Show only first 3 months of data
              tension: 0.4,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return context.parsed.y.toFixed(2) + " €";
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return value / 1000 + "k €";
                },
              },
            },
          },
        },
      });
    }

    // Products Chart
    const productsChartCtx = document.getElementById("productsChart");
    if (productsChartCtx) {
      // Clear previous chart if it exists
      if (window.productsChart) {
        window.productsChart.destroy();
      }

      // Top products data
      window.productsChart = new Chart(productsChartCtx, {
        type: "doughnut",
        data: {
          labels: [
            "Laptop Pro X1",
            "Gaming PC Elite",
            "MacBook Pro M2",
            "Ultrabook Air",
            "Autres",
          ],
          datasets: [
            {
              data: [30, 25, 20, 15, 10],
              backgroundColor: [
                "rgba(255, 99, 132, 0.8)",
                "rgba(54, 162, 235, 0.8)",
                "rgba(255, 206, 86, 0.8)",
                "rgba(75, 192, 192, 0.8)",
                "rgba(153, 102, 255, 0.8)",
              ],
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "right",
            },
          },
        },
      });
    }
  }

  // Initialize UI for admin role by default
  updateUIForRole("admin");

  // Initialize Bootstrap tooltips and popovers
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });
});
