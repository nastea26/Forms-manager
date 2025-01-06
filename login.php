<?php
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
    <link rel="stylesheet" href="styles/regLog.css">
    <script src="js/regLog.js" defer></script>
</head>
<?php include 'assets/header.php'; ?>

<body>
    <div id="login-page">
        <h2 class="welcome-title">Welcome to Forms!</h2>
        <form action="../php/login.php" method="POST" class="form-container login-form">
            <label for="email" class="form-label">Email:</label>
            <input id="email" name="email" type="email" class="text-input email-input" placeholder="Enter your email" required>
            <span class="error-message email-error"></span>

            <label for="password" class="form-label">Password:</label>
            <input id="password" name="password" type="password" class="text-input password-input" placeholder="Enter your password" required>
            <span class="error-message password-error"></span>

            <input type="submit" value="Log in" class="form-button login-button">
            <span class="error-message form-error"></span>
        </form>
    </div>
</body>

</html>