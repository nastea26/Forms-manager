<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../register.html?error=BadReq');
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];
$repeat_Password = $_POST['passwordRepeat'];

if(!isset($email)||!isset($password)||!isset($repeat_Password))
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

if($password!==$repeat_Password){
    header('Location: ../register.html?error=Mismatch');
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


include 'connect.php';
function valuesInUse($conn,$response){
    if($response){
        $row = mysqli_fetch_row(result: $response);
        if($row[0]>0)return true;
        return false;
    }
    header('Location: ../register.html?error=Query-Error');
    $conn->close();
    exit();
}

$safeEmail = mysqli_real_escape_string($conn, $email);

$emailQuery = "SELECT COUNT(*) FROM users WHERE email ='".$safeEmail."';";
$emailQueryRes = mysqli_query($conn,$emailQuery);
if(valuesInUse($conn,$emailQueryRes)){
    header('Location: ../register.html?error=InUse');
    $conn->close();
    exit();
}


$query = "INSERT INTO users (email,pass) VALUES(?,?)";
$stmt = mysqli_prepare($conn,$query);



if(!$stmt){
    header('Location: ../register.html');
    $conn->close();
    exit();
}

mysqli_stmt_bind_param($stmt, "ss", $safeEmail, $hashedPassword);
mysqli_stmt_execute($stmt);
$conn->close();
header('Location: ../');
exit();