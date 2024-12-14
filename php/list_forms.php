<?php
require_once 'db.php';
include 'Form.php';
$formHandler = new Form($database);
$forms = $formHandler->getActiveForms();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Active Forms</title>
</head>
<body>
    <h1>Available Forms</h1>
    <ul>
        <?php foreach ($forms as $form): ?>
            <li>
                <a href="view_form.php?id=<?= $form['id'] ?>"><?= htmlspecialchars($form['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
