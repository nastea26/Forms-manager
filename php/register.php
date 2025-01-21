<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../register.php?error=BadReq');
    exit;
}
if (!isset($_SESSION)) session_start();

if (!(isset($_POST['csrf_token']) && hash_equals($_SESSION['CSRF_Token'], $_POST['csrf_token']))) die("Invalid CSRF token");


$email = $_POST['email'];
$password = $_POST['password'];
$repeat_Password = $_POST['passwordRepeat'];

if (!isset($email) || !isset($password) || !isset($repeat_Password)) {
    header('Location: ../register.php');
    exit();
}

if (strlen($email) < 8 || strlen($email) > 100 || strlen($password) < 5 || strlen($password) > 35) {
    if (strlen($email) < 8 || strlen($email) > 100) $error = "Email-Length";
    if (strlen($password) < 5 || strlen($password) > 35) $error = "Password-Length";
    header('Location: ../register.php?error=BadValues' . $error);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../register.php?error=BadEmail');
    exit();
}

if ($password !== $repeat_Password) {
    header('Location: ../register.php?error=Mismatch');
    exit();
}
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


include 'db.php';
$safeEmail = $database->mysqliSanitizeString($email);

$emailInUse = $database->searchQuery("SELECT COUNT(*) FROM users WHERE email='$safeEmail'", count: true);
if ($emailInUse[0] == true || $emailInUse[1] == "err") {
    header('Location:../register.php');
    exit();
}
$res  = $database->insertInto('users', ['email', 'pass'], [$safeEmail, $hashedPassword]);
if ($res) {
    header('Location: ../');
    exit();
}
header('Location:../register.php');
exit();
