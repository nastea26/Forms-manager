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
if (checkAccessToFrom($formHandler, $formId, "Edit Form")) exit();

// Fetch the form and its questions
$formResponses = $formHandler->getFormResponses($formId);
if ($formResponses) {
    include '../assets/editForbiden.php';
    formHasAnswers();
    exit();
}

$formData = $formHandler->getFormDetails($formId);
$formIsActive = $formData["is_active"];

if ($formIsActive) {
    include '../assets/editForbiden.php';
    formIsActive($database, $formId);
    exit();
}

if (!$formData) {
    echo "Form not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Form</title>
    <link rel="stylesheet" href="../styles/formMaker.css">
</head>

<?php include '../assets/header.php'; ?>

<body>
    <main>
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
                    <?php foreach ($formData['questions'] as $questionIndex => $question): ?>
                        <div class="question">
                            <input type="hidden" name="questions[<?= $questionIndex ?>][id]" value="<?= $question['id'] ?>">

                            <input type="text" class="question-text" name="questions[<?= $questionIndex ?>][text]"
                                value="<?= htmlspecialchars($question['question_text']) ?>" required>

                            <select class="question-type" name="questions[<?= $questionIndex ?>][type]"
                                onchange="handleTypeChange(this, <?= $questionIndex ?>)">
                                <option value="text" <?= $question['answer_type'] === 'text' ? 'selected' : '' ?>>Text</option>
                                <option value="multiple_choice" <?= $question['answer_type'] === 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                                <option value="checkbox" <?= $question['answer_type'] === 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                            </select>

                            <label class="required-toggle">
                                <input type="checkbox" name="questions[<?= $questionIndex ?>][required]"
                                    <?= $question['is_required'] ? 'checked' : '' ?>> Required
                            </label>

                            <div class="options">
                                <?php if (isset($question['choices']) && is_array($question['choices'])): ?>
                                    <?php foreach ($question['choices'] as $choiceIndex => $choice): ?>
                                        <div class="option">
                                            <input type="hidden" name="questions[<?= $questionIndex ?>][choices][<?= $choiceIndex ?>][id]" value="<?= $choice['id'] ?>">
                                            <input type="text" class="option-input" name="questions[<?= $questionIndex ?>][choices][<?= $choiceIndex ?>][text]"
                                                value="<?= htmlspecialchars($choice['option_text']) ?>" placeholder="Option Text" required>
                                            <button type="button" class="remove-option" onclick="removeOption(this)">×</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <button type="button" class="add-option" onclick="addOption(this, <?= $questionIndex ?>)">Add Option</button>

                            <label>
                                <input type="checkbox" name="questions[<?= $questionIndex ?>][delete]" value="1">
                                Delete this question
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="add-question" onclick="addQuestion()">+</button>

                <hr>

                <!-- Checkbox for setting form as active -->
                <div>
                    <label for="is_active">
                        <input type="checkbox" name="is_active" id="is_active" value="1" <?= $formIsActive ? 'checked' : '' ?>>
                        Set this form as active
                    </label>
                </div>

                <button type="submit" class="save-form">Save Changes</button>
            </form>
        </div>

        <script>
            let questionCount = <?= count($formData['questions']) ?>;

            function addQuestion() {
                questionCount++;
                const questionDiv = document.createElement('div');
                questionDiv.classList.add('question');
                questionDiv.innerHTML = `
                <input type="hidden" name="questions[${questionCount}][id]" value="">
                <input type="text" class="question-text" name="questions[${questionCount}][text]" placeholder="Question Text" required>
                <select class="question-type" name="questions[${questionCount}][type]" onchange="handleTypeChange(this, ${questionCount})">
                    <option value="text">Text</option>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="checkbox">Checkbox</option>
                </select>
                <label class="required-toggle">
                    <input type="checkbox" name="questions[${questionCount}][required]"> Required
                </label>
                <div class="options"></div>
                <button type="button" class="add-option" onclick="addOption(this, ${questionCount})">Add Option</button>
                <label>
                    <input type="checkbox" name="questions[${questionCount}][delete]" value="1"> Delete this question
                </label>
                `;
                document.getElementById('questions').appendChild(questionDiv);
            }


            function addOption(button, questionIndex) {
                const optionsDiv = button.previousElementSibling;
                const optionDiv = document.createElement('div');
                optionDiv.classList.add('option');
                optionDiv.innerHTML = `
                <input type="text" class="option-input" name="questions[${questionIndex}][choices][][text]" placeholder="Option Text" required>
                <button type="button" class="remove-option" onclick="removeOption(this)">×</button>
                `;
                optionsDiv.appendChild(optionDiv);
            }


            function removeOption(button) {
                const optionDiv = button.parentElement;
                optionDiv.remove();
            }

            function handleTypeChange(select, questionIndex) {
                const questionDiv = select.parentElement;
                const optionsDiv = questionDiv.querySelector('.options');
                const addOptionButton = questionDiv.querySelector('.add-option');

                optionsDiv.innerHTML = '';
                if (select.value === 'multiple_choice' || select.value === 'checkbox') {
                    addOptionButton.disabled = false;

                    const optionDiv = document.createElement('div');
                    optionDiv.classList.add('option');
                    optionDiv.innerHTML = `
                    <input type="text" class="option-input" name="questions[${questionIndex}][choices][][text]" placeholder="Option Text" required>
                    <button type="button" class="remove-option" onclick="removeOption(this)">×</button>
                    `;
                    optionsDiv.appendChild(optionDiv);
                } else {
                    addOptionButton.disabled = true;
                }
            }
        </script>
    </main>
</body>

</html>