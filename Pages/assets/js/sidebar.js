document.addEventListener("DOMContentLoaded", function () {
  // Set theme based on role
  const currentPath = window.location.pathname;
  let role = "default";

  if (currentPath.includes("/admin/")) {
    role = "admin";
  } else if (currentPath.includes("/finance/")) {
    role = "finance";
  } else if (currentPath.includes("/vendeur/")) {
    role = "vendeur";
  } else if (currentPath.includes("/stock/")) {
    role = "stock";
  }

  // Set role data attribute on body
  document.body.setAttribute("data-role", role);

  // Get elements
  const sidebar = document.getElementById("sidebar");
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const body = document.body;

  // Check if sidebar state is saved in localStorage
  const sidebarCollapsed = localStorage.getItem("sidebarCollapsed") === "true";

  // Set initial state based on localStorage or default to not collapsed
  if (sidebarCollapsed) {
    body.classList.add("sidebar-collapsed");
  }

  // Desktop sidebar toggle function
  function toggleSidebar() {
    if (window.innerWidth <= 768) {
      // Mobile behavior
      body.classList.toggle("sidebar-shown");
      localStorage.setItem(
        "sidebarShown",
        body.classList.contains("sidebar-shown")
      );
    } else {
      // Desktop behavior
      body.classList.toggle("sidebar-collapsed");
      localStorage.setItem(
        "sidebarCollapsed",
        body.classList.contains("sidebar-collapsed")
      );
    }
  }

  // Toggle sidebar when clicking the toggle button
  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", toggleSidebar);
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", function (event) {
    if (
      window.innerWidth <= 768 &&
      !sidebar.contains(event.target) &&
      body.classList.contains("sidebar-shown")
    ) {
      body.classList.remove("sidebar-shown");
    }
  });

  // Handle window resize
  window.addEventListener("resize", function () {
    if (window.innerWidth > 768) {
      // Remove mobile specific classes when returning to desktop
      body.classList.remove("sidebar-shown");
    }
  });
});
