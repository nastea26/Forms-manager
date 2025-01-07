<?php
require 'Form.php';
require 'db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$formHandler = new Form($database);

// Get the form ID from the URL
$formId = $_GET['form_id'] ?? null;

if (!$formId) {
    echo "Form ID is required.";
    exit();
}


include 'checkUserFormAccess.php';
if (checkAccessToFrom($formHandler, $formId, "Form Results")) exit();

// Fetch the form and its questions
$formData = $formHandler->getFormDetails($formId);

if (!$formData) {
    echo "Form not found.";
    exit();
}

include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Form</title>
    <link rel="stylesheet" href="../styles/formMaker.css">
</head>

<body>
    <div id="form-builder">
        <h1>Edit Form</h1>

        <form method="POST" action="edit_form_action.php" class="form">
            <!-- Hidden form ID -->
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($formId) ?>">

            <label for="title">Form Title:</label>
            <input type="text" name="title" id="title" class="form-title"
                value="<?= htmlspecialchars($formData['title']) ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" class="form-description" required><?= htmlspecialchars(trim($formData['description'])) ?></textarea>

            <h2>Questions</h2>
            <div id="questions" class="questions-container">
                <?php foreach ($formData['questions'] as $question): ?>
                    <div class="question">
                        <input type="hidden" name="question_ids[]" value="<?= $question['id'] ?>">
                        <label for="question_<?= $question['id'] ?>">Question:</label>
                        <input type="text" name="questions[]" id="question_<?= $question['id'] ?>"
                            class="question-text" value="<?= htmlspecialchars($question['question_text']) ?>" required>
                        <label>
                            <input type="checkbox" name="deleted_questions[]" value="<?= $question['id'] ?>">
                            Delete this question
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" id="add-question">+</button>
            <button type="submit" class="save-form">Save Changes</button>
        </form>
    </div>

    <script>
        // JavaScript to handle adding/removing questions dynamically
        document.getElementById('add-question').addEventListener('click', function() {
            const questionDiv = document.createElement('div');
            questionDiv.classList.add('question');

            questionDiv.innerHTML = `
                <input type="hidden" name="question_ids[]" value="">
                <label>Question:</label>
                <input type="text" name="questions[]" class="question-text" value="" required>
                <label>
                    <input type="checkbox" name="deleted_questions[]" value="" disabled>
                    Delete this question
                </label>
            `;

            document.getElementById('questions').appendChild(questionDiv);
        });

        // Enable removing dynamically added questions
        document.querySelectorAll('.remove-question').forEach(button => {
            button.addEventListener('click', function() {
                button.closest('.question').remove();
            });
        });
    </script>
</body>

</html>