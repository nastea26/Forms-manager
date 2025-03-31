<?php
require 'db.php';
require 'Form.php';
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$formHandler = new Form($database);
$forms = $formHandler->getUserForms($_SESSION['user_id']);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

$host = $_SERVER['HTTP_HOST'];

$path = dirname($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Forms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../styles/modal.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/dashboard_results.css">
    <script type="module" src="../js/shareModal.js" defer></script>
</head>
<script type="module" defer>
    import Modal from '../js/shareModal.js';

    document.addEventListener('DOMContentLoaded', () => {
        const shareButtons = document.querySelectorAll('.share-button'); // Select all share buttons

        shareButtons.forEach(button => {
            button.addEventListener('click', () => {
                const shareLink = button.dataset.link; // Get the form link from the data attribute
                const modal = new Modal(); // Assuming you have a Modal class
                modal.initModal(); // Initialize the modal
                modal.showModal(shareLink); // Show the modal with the share link
            });
        });
    });
</script>

<body class="dashboard-body">
    <?php include '../assets/header.php';
    createHeader("../");
    ?>
    <main>
        <div class="dashboard-wrapper">
            <div class="dashboard-container">
                <h1 class="dashboard-title">My Forms</h1>
                <a href="../create_form.php" class="button create-form-button">Create New Form</a>
                <ul class="forms-list">
                    <?php foreach ($forms as $form): ?>
                        <?php $formLink = $protocol . $host . $path . '/view_form.php?q=' . urlencode($form['link']); ?>
                        <li class="form-item">
                            <h2 class="form-title"><?= htmlspecialchars($form['title']) ?></h2>
                            <p class="form-description"><?= htmlspecialchars($form['description']) ?></p>
                            <div class="form-actions">
                                <a href="form_results.php?q=<?= $form['link'] ?>" class="button view-results-button">View Results</a>
                                <a href="edit_form.php?q=<?= $form['link'] ?>" class="button edit-form-button">Edit Form</a>
                                <button data-link=<?= $formLink ?> class="button share-button">Share Form</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </main>
    <?php
    include '../assets/footer.php';
    ?>
</body>


</html>