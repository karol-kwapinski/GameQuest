<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
  <link rel="stylesheet" href="../styles/styles_for_register.css">
</head>
<body>
  <div class="container">
    <h1>Registration</h1>
    <?php
    session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>

    <form id="register_form" action="../util/register.php" method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <div class="form-group">
        <input type="text" id="first_name" name="first_name" placeholder="Enter first name" required>
        <span id="first_name_error" class="error-message"></span>
      </div>

      <div class="form-group">
        <input type="text" id="last_name" name="last_name" placeholder="Enter last name" required>
        <span id="last_name_error" class="error-message"></span>
      </div>

      <div class="form-group">
        <input type="email" id="email" name="email" placeholder="Enter email" required>
        <span id="email_error" class="error-message"></span>
      </div>

      <div class="form-group password-group">
        <input type="password" id="password" name="password" placeholder="Enter password" required>
        <button type="button" id="toggle_password" class="toggle-password" aria-label="Show Password">
          <img src="images/eye_closed.png" id="toggle_password_icon" alt="Toggle Password">
        </button>
      </div>

      <div class="form-group password-group">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
        <button type="button" id="toggle_confirm_password" class="toggle-password" aria-label="Show Confirm Password">
          <img src="images/eye_closed.png" id="toggle_confirm_password_icon" alt="Toggle Confirm Password">
        </button>
      </div>

      <button type="submit">Register</button>
    </form>
  </div>

  <script>
    const nameRegex = /^[a-zA-ZżźćńółęąśŻŹĆĄŚĘŁÓŃ]+$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    document.getElementById("register_form").addEventListener("input", function (e) {
      const firstName = document.getElementById("first_name").value.trim();
      const lastName = document.getElementById("last_name").value.trim();
      const email = document.getElementById("email").value.trim();
      const firstNameError = document.getElementById("first_name_error");
      const lastNameError = document.getElementById("last_name_error");
      const emailError = document.getElementById("email_error");
      if (e.target.id === "first_name") {
        if (firstName === "") {
          firstNameError.textContent = "First name is required.";
          firstNameError.style.color = "red";
        } else if (!nameRegex.test(firstName)) {
          firstNameError.textContent = "First name can only contain letters.";
          firstNameError.style.color = "red";
        } else {
          firstNameError.textContent = "";
        }
      }

      if (e.target.id === "last_name") {
        if (lastName === "") {
          lastNameError.textContent = "Last name is required.";
          lastNameError.style.color = "red";
        } else if (!nameRegex.test(lastName)) {
          lastNameError.textContent = "Last name can only contain letters.";
          lastNameError.style.color = "red";
        } else {
          lastNameError.textContent = "";
        }
      }
      if (e.target.id === "email") {
      if (email === "") {
        emailError.textContent = "Email is required.";
        emailError.style.color = "red";
      } else if (!emailRegex.test(email)) {
        emailError.textContent = "Invalid email format.";
        emailError.style.color = "red";
      } else {
        emailError.textContent = "";
      }
    }
    });

    document.getElementById("register_form").addEventListener("submit", function (e) {
      const firstName = document.getElementById("first_name").value.trim();
      const lastName = document.getElementById("last_name").value.trim();
      const email = document.getElementById("email").value.trim();
      const firstNameError = document.getElementById("first_name_error");
      const lastNameError = document.getElementById("last_name_error");
      const emailError = document.getElementById("email_error");
      let isValid = true;

      if (!nameRegex.test(firstName)) {
        firstNameError.textContent = "First name can only contain letters.";
        firstNameError.style.color = "red";
        isValid = false;
      }

      if (!nameRegex.test(lastName)) {
        lastNameError.textContent = "Last name can only contain letters.";
        lastNameError.style.color = "red";
        isValid = false;
      }

      if (!emailRegex.test(email)) {
      emailError.textContent = "Invalid email format.";
      emailError.style.color = "red";
      isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
    function enablePasswordToggle(inputId, buttonId, iconId) {
      const passwordInput = document.getElementById(inputId);
      const toggleButton = document.getElementById(buttonId);
      const toggleIcon = document.getElementById(iconId);

      toggleButton.addEventListener("mousedown", function () {
        passwordInput.type = "text";
        toggleIcon.src = "images/eye_open.png";
      });

      toggleButton.addEventListener("mouseup", function () {
        passwordInput.type = "password";
        toggleIcon.src = "images/eye_closed.png";
      });

      toggleButton.addEventListener("touchstart", function () {
        passwordInput.type = "text";
        toggleIcon.src = "images/eye_open.png";
      });

      toggleButton.addEventListener("touchend", function () {
        passwordInput.type = "password";
        toggleIcon.src = "images/eye_closed.png";
      });
    }

    enablePasswordToggle("password", "toggle_password", "toggle_password_icon");
    enablePasswordToggle("confirm_password", "toggle_confirm_password", "toggle_confirm_password_icon");


  </script>
</body>
</html>
