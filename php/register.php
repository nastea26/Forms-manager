<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../login.php?action=register');
    exit;
}

if (!isset($_SESSION)) session_start();

// CSRF token validation
if (!(isset($_POST['csrf_token']) && hash_equals($_SESSION['CSRF_Token'], $_POST['csrf_token']))) {
    die("Invalid CSRF token");
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$repeat_Password = $_POST['passwordRepeat'] ?? '';

// Initialize error messages
$emailError = "";
$passwordError = "";
$repeatPasswordError = "";
$generalError = "";

// Validate input fields
if (empty($email) || empty($password) || empty($repeat_Password)) {
    $generalError = "All fields are required.";
}

if (strlen($email) < 8 || strlen($email) > 100) {
    $emailError = "Email length must be between 8 and 100 characters.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $emailError = "Invalid email address format.";
}

if (strlen($password) < 5 || strlen($password) > 35) {
    $passwordError = "Password length must be between 5 and 35 characters.";
}

if ($password !== $repeat_Password) {
    $repeatPasswordError = "Passwords do not match.";
}

// If there are any errors, store them in the session and redirect back
if (!empty($emailError) || !empty($passwordError) || !empty($repeatPasswordError) || !empty($generalError)) {
    $_SESSION["registerEmailError"] = $emailError;
    $_SESSION["registerPasswordError"] = $passwordError;
    $_SESSION["registerRepeatPasswordError"] = $repeatPasswordError;
    $_SESSION["registerGeneralError"] = $generalError;
    header('Location: ../login.php?action=register');
    exit();
}

// Include database connection
require_once 'db.php';
$database = new SqlEntity();

// Sanitize email
$safeEmail = $database->mysqliSanitizeString($email);

// Check if email is already in use
$emailCheckQuery = "SELECT COUNT(*) as count FROM users WHERE email = ?";

$emailCheckResult = $database->searchQuery($emailCheckQuery, [$safeEmail]);

// Check if the query was successful
if ($emailCheckResult) {
    // Fetch the result as an associative array
    $emailCount = $emailCheckResult->fetch_assoc();

    // Verify if the count is greater than 0
    if ($emailCount['count'] > 0) {
        $_SESSION["registerEmailError"] = "Email is already in use.";
        header('Location: ../login.php?action=register');
        exit();
    }
} else {
    // Handle query failure
    $_SESSION["registerGeneralError"] = "An error occurred while checking the email. Please try again later.";
    header('Location: ../login.php?action=register');
    exit();
}


// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user into the database
$insertResult = $database->insertInto('users', ['email', 'pass'], [$safeEmail, $hashedPassword]);

if ($insertResult) {
    // Registration successful, redirect to homepage or login page
    header('Location: ../');
    exit();
} else {
    // Database insertion error
    $_SESSION["registerGeneralError"] = "Registration failed due to a server error. Please try again later.";
    header('Location: ../login.php?action=register');
    exit();
}
