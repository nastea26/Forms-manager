<?php
if (!isset($_SESSION)) session_start();
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$emailError = "";
$passwordError = "";
$generalError = "";

// Check for empty fields
if (empty($email) || empty($password)) {
    $generalError = "One or more input fields did not meet the requirements.";
}

// Validate length
if (strlen($email) < 8 || strlen($email) > 100) {
    $emailError = "Email length must be between 8 and 100 characters.";
}
if (strlen($password) < 5 || strlen($password) > 35) {
    $passwordError = "Password length must be between 5 and 35 characters.";
}

// If any error exists so far, redirect back with errors
if (!empty($emailError) || !empty($passwordError) || !empty($generalError)) {
    $_SESSION["loginEmailError"] = $emailError;
    $_SESSION["loginPasswordError"] = $passwordError;
    $_SESSION["loginGeneralError"] = $generalError;
    header('Location: ../login.php');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["loginEmailError"] = "Invalid email address format.";
    header('Location: ../login.php');
    exit();
}

include 'db.php';
$safeEmail = $database->mysqliSanitizeString($email);

$response = $database->searchQuery("SELECT * FROM users WHERE email=?", [$safeEmail]);
if (!$response) {
    $_SESSION["loginGeneralError"] = "There seems to be a problem on our server right now. Please try again later.";
    header('Location: ../login.php');
    exit();
}
$users = $database->sqlResponseToArray($response);
if (!isset($users[0]["email"])) {
    $_SESSION["loginEmailError"] = "An account with this email address does not exist.";
    header('Location: ../login.php');
    exit();
}
if (password_verify($password, $users[0]["pass"])) {
    $_SESSION["loggedIn"] = true;
    $_SESSION["user_id"] = $users[0]["id"];
    // Clear any login error messages
    unset($_SESSION["loginEmailError"]);
    unset($_SESSION["loginPasswordError"]);
    unset($_SESSION["loginGeneralError"]);
    header('Location: ../');
    exit();
}
// If we get here, the password was incorrect.
$_SESSION["loginPasswordError"] = "An account with this email address and password combination does not exist.";
header('Location: ../login.php');
exit();
