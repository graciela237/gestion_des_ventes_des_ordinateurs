/**
 * Add to Cart functionality
 * This script should be included in your product pages
 */
document.addEventListener("DOMContentLoaded", function () {
  // Add to cart buttons
  const addToCartButtons = document.querySelectorAll(".add-to-cart");

  if (addToCartButtons.length > 0) {
    addToCartButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();

        const productId = this.dataset.productId;
        const quantityInput = document.querySelector(
          `.quantity-input[data-product-id="${productId}"]`
        );
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

        addToCart(productId, quantity);
      });
    });
  }

  // Function to add product to cart
  function addToCart(productId, quantity) {
    // Check if user is logged in by looking for logged-in class on body
    // This class should be added in your PHP when the user is logged in
    if (!document.body.classList.contains("user-logged-in")) {
      showLoginNotification();
      return;
    }

    fetch("cart_actions.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=add&product_id=${productId}&quantity=${quantity}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update cart count
          const cartCountElements = document.querySelectorAll(".cart-count");
          cartCountElements.forEach((element) => {
            element.textContent = data.cartCount;
          });

          // Show success notification
          showCartNotification();
        } else {
          // Show error
          showErrorNotification(data.message || "Une erreur est survenue");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showErrorNotification("Problème de connexion au serveur");
      });
  }

  // Show notifications
  function showCartNotification() {
    const notification = document.getElementById("cart-notification");
    if (notification) {
      notification.classList.add("show");
      setTimeout(() => {
        notification.classList.remove("show");
      }, 3000);
    }
  }

  function showLoginNotification() {
    const notification = document.getElementById("login-notification");
    if (notification) {
      notification.classList.add("show");
      setTimeout(() => {
        notification.classList.remove("show");
      }, 3000);
    }
  }

  function showErrorNotification(message) {
    // Create error notification if it doesn't exist
    let errorNotification = document.getElementById("error-notification");

    if (!errorNotification) {
      errorNotification = document.createElement("div");
      errorNotification.id = "error-notification";
      errorNotification.innerHTML = `<i class="fas fa-exclamation-circle"></i> <span id="error-message"></span>`;
      document.body.appendChild(errorNotification);

      // Add styles
      errorNotification.style.position = "fixed";
      errorNotification.style.bottom = "20px";
      errorNotification.style.right = "20px";
      errorNotification.style.background = "#e74c3c";
      errorNotification.style.color = "white";
      errorNotification.style.padding = "15px 25px";
      errorNotification.style.borderRadius = "5px";
      errorNotification.style.boxShadow = "0 3px 10px rgba(0, 0, 0, 0.2)";
      errorNotification.style.zIndex = "1001";
      errorNotification.style.display = "none";
    }

    // Set message and show
    const errorMessage = document.getElementById("error-message");
    if (errorMessage) {
      errorMessage.textContent = message;
    }

    errorNotification.style.display = "block";
    setTimeout(() => {
      errorNotification.style.display = "none";
    }, 5000);
  }
});
