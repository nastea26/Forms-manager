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
    <title>Login</title>
    <link rel="stylesheet" href="regLog.css">
</head>

<body>
    <div id="form-container">
        <div class="form-wrapper">
            <!-- Login Form -->
            <form class="login-form">
                <h2>Login</h2>
                <!-- Form Fields -->
                <button type="button" id="register-btn">Go to Register</button>
            </form>

            <!-- Register Form -->
            <form class="register-form">
                <h2>Register</h2>
                <!-- Form Fields -->
                <button type="button" id="login-btn">Go to Login</button>
            </form>
        </div>
    </div>

</body>

</html>