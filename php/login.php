<?php
if (!isset($_SESSION)) session_start();
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

if (!isset($email) || !isset($password)) {
    header('Location: ../login.php');
    exit();
}

if (strlen($email) < 8 || strlen($email) > 100 || strlen($password) < 5 || strlen($password) > 35) {
    if (strlen($email) < 8 || strlen($email) > 100) $error = "Email-Length";
    if (strlen($password) < 5 || strlen($password) > 35) $error = "Password-Length";
    header('Location: ../login.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../login.php');
    exit();
}


include 'db.php';
$safeEmail = $database->mysqliSanitizeString($email);

$response = $database->searchQuery("SELECT * FROM users WHERE email=?", [$safeEmail]);
if (!isset($response)) {
    $_SESSION["loginNoResponse"] = True;
    header('Location:../login.php');
    exit();
}
$users = $database->sqlResponseToArray($response);
if (!isset($users[0]["email"])) {
    $_SESSION["noUserFound"] = True;
    header('Location:../login.php');
    exit();
}
if (password_verify($password, $users[0]["pass"])) {
    $_SESSION["loggedIn"] = True;
    $_SESSION["user_id"] = $users[0]["id"];
    header('Location:../?user_id=' . $_SESSION["user_id"]);
    exit();
}
$_SESSION["loginFail"] = True;
header('Location:../login.php');
exit();
