<?php
if (!isset($_SESSION)) session_start();

if (empty($_SESSION["CSRF_Token"])) {
    $_SESSION["CSRF_Token"] = bin2hex(random_bytes(32));
}

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
    <title>Register</title>
    <link rel="stylesheet" href="styles/regLog.css">
    <script src="js/regLog.js" defer></script>
</head>
<?php include 'assets/header.php'; ?>

<body>
    <div id="register-page">
        <h2 class="welcome-title">Create Your Account</h2>
        <form action="php/register.php" method="POST" class="form-container register-form">
            <label for="email" class="form-label">Email:</label>
            <input id="email" type="email" name="email" class="text-input email-input" placeholder="Enter your email" required>
            <span class="error-message email-error"></span>

            <label for="password" class="form-label">Password:</label>
            <input id="password" type="password" name="password" class="text-input password-input" placeholder="Enter your password" required>
            <span class="error-message password-error"></span>

            <label for="passwordRepeat" class="form-label">Repeat Password:</label>
            <input id="passwordRepeat" type="password" name="passwordRepeat" class="text-input password-repeat-input" placeholder="Repeat your password" required>
            <span class="error-message repeat-password-error"></span>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION["CSRF_Token"] ?>">
            <button type="submit" class="form-button register-button">Register</button>
            <span class="error-message form-error"></span>
        </form>
    </div>
</body>

</html>