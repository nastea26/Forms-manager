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
    <link rel="stylesheet" href="styles/regLog.css">
    <!-- <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f3f4f6;
        }

        #login-page {
            display: flex;
            width: 70%;
            max-width: 1000px;
            height: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .welcome-section {
            background: linear-gradient(135deg, #007bff, #2575fc);
            color: white;
            padding: 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }

        .welcome-section h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        .welcome-section p {
            margin-top: 20px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .form-section {
            flex: 1.5;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-section h2 {
            margin: 0 0 20px;
            font-size: 2rem;
            color: #333;
        }

        .form-section p {
            margin-bottom: 20px;
            font-size: 1rem;
            color: #666;
        }

        .form-container {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            margin-bottom: 8px;
            font-size: 1rem;
            color: #333;
        }

        .text-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-button {
            background-color: #2575fc;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-button:hover {
            background-color: #1e63d1;
        }

        .form-footer {
            margin-top: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .form-footer a {
            color: #2575fc;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .form-footer a:hover {
            color: #1e63d1;
        }

        .error-message {
            color: red;
            font-size: 0.8rem;
            margin-bottom: 10px;
        }
    </style>-->
</head>

<body>
    <div id="login-page">
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
                <span>New User? <a href="register.php">Signup</a></span>
                <span><a href="#">Forgot your password?</a></span>
            </div>
        </div>
    </div>
</body>

</html>