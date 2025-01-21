<?php
if (!isset($_SESSION)) session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/test.css">
</head>

<body>
    <div id="auth-container">
        <div class="auth-page login-page">
            <div class="welcome-section">
                <h1>Welcome to...</h1>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            </div>
            <div class="form-section">
                <h2>Login</h2>
                <form action="php/login.php" method="POST" class="form-container login-form">
                    <label for="email" class="form-label">User Name</label>
                    <input id="email" name="email" type="email" class="text-input email-input" placeholder="Enter your email" required>
                    <span class="error-message email-error"></span>

                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="text-input password-input" placeholder="Enter your password" required>
                    <span class="error-message password-error"></span>

                    <div style="margin-bottom: 15px;">
                        <input type="checkbox" id="remember" name="remember" style="margin-right: 5px;">
                        <label for="remember">Remember me</label>
                    </div>

                    <input type="submit" value="LOGIN" class="form-button login-button">
                    <span class="error-message form-error"></span>
                </form>
                <div class="form-footer">
                    <span>New User? <a href="#" id="switch-to-register">Signup</a></span>
                    <span><a href="#">Forgot your password?</a></span>
                </div>
            </div>
        </div>

        <div class="auth-page register-page">
            <div class="welcome-section">
                <h1>Join Us</h1>
                <p>Create your account to enjoy exclusive benefits and stay connected.</p>
            </div>
            <div class="form-section">
                <h2>Create Your Account</h2>
                <form action="php/register.php" method="POST" class="form-container">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" name="email" class="text-input" placeholder="Enter your email" required>
                    <span class="error-message"></span>

                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="text-input" placeholder="Enter your password" required>
                    <span class="error-message"></span>

                    <label for="passwordRepeat" class="form-label">Repeat Password</label>
                    <input id="passwordRepeat" type="password" name="passwordRepeat" class="text-input" placeholder="Repeat your password" required>
                    <span class="error-message"></span>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['CSRF_Token']; ?>">
                    <button type="submit" class="form-button">Register</button>
                </form>
                <div class="form-footer">
                    <span>Already have an account? <a href="#" id="switch-to-login">Login</a></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const authContainer = document.getElementById("auth-container");
            const switchToRegister = document.getElementById("switch-to-register");
            const switchToLogin = document.getElementById("switch-to-login");

            switchToRegister.addEventListener("click", (e) => {
                e.preventDefault();
                authContainer.classList.add("flipped");
            });

            switchToLogin.addEventListener("click", (e) => {
                e.preventDefault();
                authContainer.classList.remove("flipped");
            });
        });
    </script>
</body>

</html>