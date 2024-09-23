<?php

// this is not good its just for testing
include 'conntect.php';
$email = $_POST['email'];
$password = $_POST['password'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$query = "INSERT INTO users (email,pass) VALUES(?,?)";
$stmt = mysqli_prepare($conn,$query);

if(!$stmt){
    header('Location: ../register.html');
    $conn->close();
    exit();
}

mysqli_stmt_bind_param($stmt, "ss", $email, $hashedPassword);
mysqli_stmt_execute($stmt);
$conn->close();
header('Location: ../index.html');
exit();