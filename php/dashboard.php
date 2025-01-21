<?php
require 'db.php';
require 'Form.php';
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$formHandler = new Form($database);
$forms = $formHandler->getUserForms($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Forms</title>
    <link rel="stylesheet" href="../styles/dashboard_results.css">
</head>
<?php include '../assets/header.php';  ?>

<body>
    <main>
        <div class="dashboard-container">
            <h1 class="dashboard-title">My Forms</h1>
            <a href="../create_form.php" class="button create-form-button">Create New Form</a>
            <ul class="forms-list">
                <?php foreach ($forms as $form): ?>
                    <li class="form-item">
                        <h2 class="form-title"><?= htmlspecialchars($form['title']) ?></h2>
                        <p class="form-description"><?= htmlspecialchars($form['description']) ?></p>
                        <div class="form-actions">
                            <a href="form_results.php?form_id=<?= $form['id'] ?>" class="button view-results-button">View Results</a>
                            <a href="edit_form.php?form_id=<?= $form['id'] ?>" class="button edit-form-button">Edit Form</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>
</body>


</html>