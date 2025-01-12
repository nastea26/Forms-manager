<?php
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Form</title>
    <link rel="stylesheet" href="styles/formMaker.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<?php include 'assets/header.php'; ?>

<body>
    <main>
        <div id="form-builder">
            <h1>Create a New Form</h1>
            <form id="form" method="POST" action="php/save_form.php">
                <input type="text" class="form-title" name="title" placeholder="Form Title" required>
                <textarea class="form-description" name="description" placeholder="Form Description"></textarea>
                <div id="questions" class="questions-container"></div>
                <button type="button" id="add-question" onclick="addQuestion()">+</button>
                <button type="submit" class="save-form">Save Form</button>
            </form>
        </div>
    </main>
    <script>
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const questionDiv = document.createElement('div');
            questionDiv.classList.add('question');
            questionDiv.innerHTML = `
            <div class="question-header">
                <input type="text" class="question-text" name="questions[${questionCount}][text]" placeholder="Question Text" required>
                <button type="button" class="remove-question" onclick="removeQuestion(this)">
                    <i class="fa fa-trash"></i>
                </button>
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
            <button type="button" class="add-option" onclick="addOption(this)" disabled>Add Option</button>
            `;
            document.getElementById('questions').appendChild(questionDiv);

            const typeSelect = questionDiv.querySelector('.question-type');
            handleTypeChange(typeSelect, questionCount);
        }


        function removeQuestion(button) {
            const questionDiv = button.closest('.question');
            questionDiv.remove();
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
            button.parentElement.remove();
        }

        function handleTypeChange(select, questionIndex) {
            const questionDiv = select.parentElement;
            const optionsDiv = questionDiv.querySelector('.options');
            const addOptionButton = questionDiv.querySelector('.add-option');

            // Clear existing options
            optionsDiv.innerHTML = '';

            if (select.value === 'multiple_choice' || select.value === 'checkbox') {
                addOptionButton.disabled = false;

                // Automatically add one option
                addOption(addOptionButton, questionIndex);
            } else {
                addOptionButton.disabled = true;
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            addQuestion();
        });
    </script>
</body>

</html>