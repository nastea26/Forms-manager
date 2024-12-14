<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false){
    header("Location: ../");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Untitled Form</title>
</head>
<body>
    <div class="create_form_header">
        <h1>Untitled Form</h1>
        <p>Form description</p>
    </div>
    
</body>
</html>