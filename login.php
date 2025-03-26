<?php
if (!isset($_SESSION)) session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit();
}

if (!isset($_SESSION['CSRF_Token'])) {
    $_SESSION['CSRF_Token'] = bin2hex(random_bytes(32));
}

// Retrieve and clear any login errors
$loginEmailError = $_SESSION["loginEmailError"] ?? "";
$loginPasswordError = $_SESSION["loginPasswordError"] ?? "";
$loginGeneralError = $_SESSION["loginGeneralError"] ?? "";
unset($_SESSION["loginEmailError"], $_SESSION["loginPasswordError"], $_SESSION["loginGeneralError"]);

// Retrieve and clear any registration errors
$registerEmailError = $_SESSION["registerEmailError"] ?? "";
$registerPasswordError = $_SESSION["registerPasswordError"] ?? "";
$registerRepeatPasswordError = $_SESSION["registerRepeatPasswordError"] ?? "";
$registerGeneralError = $_SESSION["registerGeneralError"] ?? "";
unset($_SESSION["registerEmailError"], $_SESSION["registerPasswordError"], $_SESSION["registerRepeatPasswordError"], $_SESSION["registerGeneralError"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication</title>
    <script src="js/regLog.js" defer></script>
    <link rel="stylesheet" href="styles/login.css">
</head>

<body>
    <!-- Wave Background -->
    <div class="wave-container">
        <div class="wave wave-1">
            <svg viewBox="0 0 1440 300" xmlns="http://www.w3.org/2000/svg">
                <path fill="#003f8a" d="M0,160 C300,320 800,120 1340,500 L1440,400 L0,400 Z"></path>
            </svg>
        </div>
        <div class="wave wave-2">
            <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                <path fill="#005fba" d="M0,130 C400,300 1000,160 1400,500 L1440,400 L0,400 Z"></path>
            </svg>
        </div>
        <div class="wave wave-3">
            <svg viewBox="0 0 1440 360" xmlns="http://www.w3.org/2000/svg">
                <path fill="#007bff" d="M0,120 C500,280 1200,220 1500,500 L1440,400 L0,400 Z"></path>
            </svg>
        </div>
        <div class="wave wave-4">
            <svg viewBox="0 0 1440 360" xmlns="http://www.w3.org/2000/svg">
                <path fill="#a0c4ff" d="M0,80 C500,235 1200,190 1750,490 L1440,400 L0,400 Z"></path>
            </svg>
        </div>



    </div>

    <!-- Authentication Container -->
    <div id="auth-container">
        <!-- Login Page -->
        <div class="auth-page login-page">
            <div class="welcome-section">
                <h1>Welcome Back</h1>
                <p>Please log in to continue.</p>
            </div>
            <div class="form-section">
                <h2>Login</h2>
                <!-- General error message -->
                <?php if (!empty($loginGeneralError)) { ?>
                    <div class="error-message general-error"><?php echo $loginGeneralError; ?></div>
                <?php } ?>
                <form action="php/login.php" method="POST" class="form-container login-form">
                    <label for="login-email" class="form-label">Email</label>
                    <input id="login-email" name="email" type="email" class="text-input email-input" placeholder="Enter your email" required>
                    <span class="error-message email-error"><?php echo $loginEmailError; ?></span>

                    <label for="login-password" class="form-label">Password</label>
                    <input id="login-password" name="password" type="password" class="text-input password-input" placeholder="Enter your password" required>
                    <span class="error-message password-error"><?php echo $loginPasswordError; ?></span>

                    <!--<div class="form-footer-remember-me">
                        <input type="checkbox" id="remember" name="remember" class="remember-me">
                        <label for="remember" class="remember-me">Remember me</label>
                    </div> -->

                    <input type="submit" value="LOGIN" class="form-button login-button">
                </form>
                <div class="form-footer">
                    <span>New User? <a href="#" id="switch-to-register">Sign up</a></span>
                    <!--<span><a href="#">Forgot your password?</a></span>-->
                </div>
            </div>
        </div>

        <!-- Registration Page -->
        <div class="auth-page register-page">
            <div class="welcome-section">
                <h1>Join Us</h1>
                <p>Create an account to get started.</p>
            </div>
            <div class="form-section">
                <h2>Register</h2>
                <!-- General error message -->
                <?php if (!empty($registerGeneralError)) { ?>
                    <div class="error-message general-error"><?php echo $registerGeneralError; ?></div>
                <?php } ?>
                <form action="php/register.php" method="POST" class="form-container reg-form">
                    <label for="register-email" class="form-label">Email</label>
                    <input id="register-email" name="email" type="email" class="text-input email-reg" placeholder="Enter your email" required>
                    <span class="error-message email-reg-err"><?php echo $registerEmailError; ?></span>

                    <label for="register-password" class="form-label">Password</label>
                    <input id="register-password" name="password" type="password" class="text-input password-reg" placeholder="Enter your password" required>
                    <span class="error-message password-reg-err"><?php echo $registerPasswordError; ?></span>

                    <label for="register-password-repeat" class="form-label">Repeat Password</label>
                    <input id="register-password-repeat" name="passwordRepeat" type="password" class="text-input repeat-reg" placeholder="Repeat your password" required>
                    <span class="error-message repeat-reg-err"><?php echo $registerRepeatPasswordError; ?></span>

                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['CSRF_Token']; ?>">
                    <button type="submit" class="form-button">Register</button>
                </form>
                <div class="form-footer">
                    <span>Already have an account? <a href="#" id="switch-to-login">Log in</a></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Page flip animation between login and register
        document.addEventListener("DOMContentLoaded", () => {
            const authContainer = document.getElementById("auth-container");
            const switchToRegister = document.getElementById("switch-to-register");
            const switchToLogin = document.getElementById("switch-to-login");

            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get("action");

            if (action === "register") {
                authContainer.classList.add("flipped", "instant-flip");
                setTimeout(() => {
                    authContainer.classList.remove("instant-flip");
                }, 50);
            }

            switchToRegister.addEventListener("click", (e) => {
                e.preventDefault();
                authContainer.classList.add("flipped");
                history.pushState(null, "", "?action=register");
            });

            switchToLogin.addEventListener("click", (e) => {
                e.preventDefault();
                authContainer.classList.remove("flipped");
                history.pushState(null, "", "?action=login");
            });
        });
    </script>
</body>

</html>