<?php
require 'Form.php';
require 'db.php';
if (!isset($_SESSION)) session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$formHandler = new Form($database);

// Get the form link from the URL
$link = $_GET['q'] ?? null;

if (!$link) {
    echo "Form link is required.";
    exit();
}
$formData = $formHandler->getFormByLink($link);
$formIsActive = $formData["is_active"];

include 'checkUserFormAccess.php';
if (checkAccessToFrom($formHandler, $formData["id"], "Edit Form")) exit();

// Fetch the form and its questions
if ($formData["submission_count"] > 0) {
    include '../assets/editForbiden.php';
    formHasAnswers();
    exit();
}


if ($formIsActive) {
    include '../assets/editForbiden.php';
    formIsActive($database, $link);
    exit();
}

if (!$formData) {
    echo "Form not found.";
    exit();
}

$questions = $formHandler->getQuestionsWithOptions($formData["id"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Form</title>
    <link rel="stylesheet" href="../styles/formMaker.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<?php include '../assets/header.php'; ?>

<body>
    <main>
        <div id="form-builder">
            <h1>Edit Form</h1>

            <form method="POST" action="edit_form_action.php" class="form">
                <!-- Hidden form link -->
                <input type="hidden" name="form_link" value="<?= htmlspecialchars($link) ?>">

                <label for="title">Form Title:</label>
                <input type="text" name="title" id="title" class="form-title"
                    value="<?= htmlspecialchars($formData['title']) ?>" required>

                <label for="description">Description:</label>
                <textarea name="description" id="description" class="form-description" required><?= htmlspecialchars(trim($formData['description'])) ?></textarea>

                <div>
                    <!-- Publish Form Slider -->
                    <label for="publish-slider" class="slider-label">
                        <span>Publish Form:</span>
                        <label class="switch">
                            <input type="checkbox" name="is_active" id="publish-slider" value="1" <?= $formIsActive ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </label>
                </div>

                <div>
                    <!-- Allow Non-Users Slider -->
                    <label for="non-user-access" class="slider-label">
                        <span>Allow users without accounts to respond:</span>
                        <label class="switch">
                            <input type="checkbox" id="non-user-access" name="available_for_non_users" value="1" <?= $formData['available_for_non_users'] ? 'checked' : '' ?>>
                            <span class="slider round"></span>
                        </label>
                    </label>
                </div>

                <div>
                    <!-- Secure with PIN Slider -->
                    <label for="secure-with-pin" class="slider-label">
                        <span>Secure form with PIN:</span>
                        <label class="switch">
                            <input type="checkbox" id="secure-with-pin" name="pin_enabled" value="1" <?= $formData['pin'] ? 'checked' : '' ?> onchange="togglePinInput(this)">
                            <span class="slider round"></span>
                        </label>
                    </label>
                </div>

                <div id="pin-input-container" style="display: <?= $formData['pin'] ? 'block' : 'none' ?>;">
                    <label for="form-pin">Enter PIN:</label>
                    <input type="text" id="form-pin" name="pin" maxlength="12" placeholder="Enter a 4-12 digit PIN" value="<?= htmlspecialchars($formData['pin']) ?>">
                </div>

                <h2>Questions</h2>
                <div id="questions" class="questions-container">
                    <?php foreach ($questions as $questionIndex => $question): ?>
                        <div class="question">
                            <div class="question-header">
                                <input type="hidden" name="questions[<?= $questionIndex ?>][id]" value="<?= $question['id'] ?>">
                                <input type="text" class="question-text" name="questions[<?= $questionIndex ?>][text]"
                                    value="<?= htmlspecialchars($question['question_text']) ?>" required>
                            </div>
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
                                <?php if (!empty($question['options']) && is_array($question['options'])): ?>
                                    <?php foreach ($question['options'] as $choiceIndex => $choice): ?>
                                        <div class="option">
                                            <input type="text" class="option-input" name="questions[<?= $questionIndex ?>][options][<?= $choiceIndex ?>]"
                                                value="<?= htmlspecialchars($choice) ?>" placeholder="Option Text" required>
                                            <button type="button" class="remove-option" onclick="removeOption(this)">
                                                <i class="fa fa-trash"></i>
                                            </button>
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
                <button type="submit" class="save-form">Save Changes</button>
            </form>
        </div>

        <script>
            let questionCount = <?= count($questions) ?>;

            function addQuestion() {
                questionCount++;
                const questionDiv = document.createElement('div');
                questionDiv.classList.add('question');
                questionDiv.innerHTML = `
                    <input type="hidden" name="questions[${questionCount}][id]" value="">
                    <div class="question-header">
                        <input type="text" class="question-text" name="questions[${questionCount}][text]" placeholder="Question Text" required>
                    </div>
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
                    <input type="text" class="option-input" name="questions[${questionIndex}][options][]" placeholder="Option Text" required>
                    <button type="button" class="remove-option" onclick="removeOption(this)">
                        <i class="fa fa-trash"></i>
                    </button>
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
                        <input type="text" class="option-input" name="questions[${questionIndex}][options][]" placeholder="Option Text" required>
                        <button type="button" class="remove-option" onclick="removeOption(this)">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                    optionsDiv.appendChild(optionDiv);
                } else {
                    addOptionButton.disabled = true;
                }
            }

            function togglePinInput(checkbox) {
                const pinInputContainer = document.getElementById('pin-input-container');
                const pinInput = document.getElementById('form-pin');

                if (checkbox.checked) {
                    pinInputContainer.style.display = 'block';
                } else {
                    pinInputContainer.style.display = 'none';
                    pinInput.value = ''; // Clear the PIN input value
                }
            }
        </script>
    </main>
</body>

</html>