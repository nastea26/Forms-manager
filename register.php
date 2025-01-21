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
    <!--<style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f3f4f6;
        }

        #register-page {
            display: flex;
            width: 70%;
            max-width: 1000px;
            height: 600px;
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
    <div id="register-page">
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
                <span>Already have an account? <a href="login.php">Login</a></span>
            </div>
        </div>
    </div>
</body>

</html>