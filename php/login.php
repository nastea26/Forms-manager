<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../login.html.html?error=BadReq');
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

if(!isset($email)||!isset($password))
{
    header('Location: ../register.html');
    exit();
}

if(strlen($email)<8 || strlen($email)>100 || strlen($password)<5 || strlen($password)>35){
    if(strlen($email)<8 || strlen($email)>100)$error = "Email-Length";
    if(strlen($password)<5 || strlen($password)>35)$error = "Password-Length";
    header('Location: ../register.html?error=BadValues'.$error);
    exit();
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    header('Location: ../register.html?error=BadEmail');
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

include 'connect.php';
$safeEmail = mysqli_real_escape_string($conn, $email);

