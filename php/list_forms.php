<?php
require_once 'db.php';
include 'Form.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$formHandler = new Form($database);
$forms = $formHandler->getActiveForms();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Forms</title>
    <link rel="stylesheet" href="../styles/listForms.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/footer.css">
</head>
<?php include '../assets/header.php';
createHeader("../");
?>

<body>
    <main>
        <h1>Available Forms:</h1>
        <ul>
            <?php foreach ($forms as $form): ?>
                <li class="list-form-item">
                    <a href="view_form.php?q=<?= $form['link'] ?>" class="list-form-link">
                        <div class="list-form-title"><?= htmlspecialchars($form['title']) ?></div>
                        <div class="list-form-description"><strong>Description:</strong> <?= htmlspecialchars($form['description']) ?></div>
                        <div class="list-form-created-at"><strong>Created At:</strong>
                            <?= date('m/d/Y ga', strtotime($form['created_at'])) ?>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
    <?php
    include '../assets/footer.php';
    ?>
</body>

</html>