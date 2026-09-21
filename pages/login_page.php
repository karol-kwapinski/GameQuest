<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameQuest</title>
    <link rel="stylesheet" href="../styles/styles_for_login_page.css">
</head>
<body>
    <?php
    session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <header>
        <div class="top-bar">
            <div class="logo">
                <a href = "../index.php">GameQuest</a>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <h3>Login</h3>
            <form id="login_form" action="../util/login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="text" id="email" placeholder="Enter your email address" name="email" required>
                <div class="password-group">
                    <input type="password" id="password" placeholder="Enter your password" name="password" required>
                    <button type="button" id="toggle_password" class="toggle-password" aria-label="Show Password">
                        <img src="images/eye_closed.png" id="toggle_password_icon" alt="Toggle Password">
                    </button>
                </div>
                <button type="submit" name="btn">Login</button>
            </form>
            <a href="register_page.php" class="register-link">Register</a>
            <?php
                if (isset($_SESSION['login_error'])) {
                    echo "<p class='error-message'>{$_SESSION['login_error']}</p>";
                    unset($_SESSION['login_error']);
                }
            ?>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-about">
                <h3>About Us</h3>
                <p>We are a leading online store offering a wide range of products at competitive prices.</p>
            </div>
            <div class="footer-links">
                <h3>Useful Links</h3>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms and Conditions</a></li>
                    <li><a href="#">Return Policy</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact</h3>
                <p>Email: contact@gamequest.com</p>
                <p>Phone: +48 123 456 789</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 GameQuest. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById("login_form").addEventListener("submit", function (e) {
            const email = document.getElementById("email").value.trim();
            const password = document.getElementById("password").value;

            if (!email || !password) {
                e.preventDefault();
                alert("All fields are required.");
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
    </script>
</body>
</html>
