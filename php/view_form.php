<?php
if (!isset($_SESSION)) session_start();

include 'Form.php';
include 'db.php';

$formHandler = new Form($database);
$form = $formHandler->getFormDetails($_GET['id']);

// Redirect to edit page if the form is inactive and the user is the creator
if (!$form['is_active'] && $form['user_id'] == $_SESSION['user_id']) {
    header('Location: edit_form.php?form_id=' . $form['id']);
    exit();
}

// Redirect inactive forms to list page for non-creators
if (!$form['is_active']) {
    header('Location: list_forms.php');
    exit();
}

// Check if the current user is the creator
$isCreator = $form['user_id'] == $_SESSION['user_id'];

$respondentIDs = $formHandler->getRespondentIds($form['id']);
if (in_array($_SESSION['user_id'], $respondentIDs) && !$isCreator) {
    echo "You've already subbmited a response for this form";
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($form['title']) ?></title>
    <link rel="stylesheet" href="../styles/style.css">
    <script>
        function validateForm(event) {
            const isCreator = <?= json_encode($isCreator) ?>;
            if (isCreator) {
                event.preventDefault();
                alert('As the creator of this form, you cannot submit a response.');
                return false;
            }

            let isValid = true;
            let firstInvalidField = null;

            // Select all elements with the `data-required` attribute set to "1"
            const requiredFields = document.querySelectorAll('[data-required="1"]');

            requiredFields.forEach(field => {
                const errorSpan = document.getElementById(`error-${field.getAttribute('data-question-id')}`);

                // For checkboxes or radio buttons, check if any are checked
                if (
                    (field.type === 'radio' || field.type === 'checkbox') &&
                    !document.querySelector(`input[name="answers[${field.getAttribute('data-question-id')}][]"]:checked`)
                ) {
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;

                    if (errorSpan) {
                        errorSpan.textContent = 'This field is required.';
                        errorSpan.style.color = 'red';
                    }
                } else if (field.value.trim() === '') {
                    // For text inputs or textareas
                    isValid = false;
                    if (!firstInvalidField) firstInvalidField = field;

                    if (errorSpan) {
                        errorSpan.textContent = 'This field is required.';
                        errorSpan.style.color = 'red';
                    }
                } else {
                    // Clear the error if the field is valid
                    if (errorSpan) errorSpan.textContent = '';
                }
            });

            if (!isValid) {
                event.preventDefault();

                // Scroll to the first invalid field
                if (firstInvalidField) {
                    firstInvalidField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstInvalidField.focus();
                }
            }
        }

        // Add event listeners to dynamically hide errors when fields are interacted with
        document.addEventListener('DOMContentLoaded', () => {
            const requiredFields = document.querySelectorAll('[data-required="1"]');

            requiredFields.forEach(field => {
                // Clear error on input for text fields and textareas
                if (field.type === 'text' || field.tagName === 'TEXTAREA') {
                    field.addEventListener('input', () => {
                        const errorSpan = document.getElementById(`error-${field.getAttribute('data-question-id')}`);
                        if (errorSpan) errorSpan.textContent = '';
                    });
                }

                // Clear error on change for radio buttons and checkboxes
                if (field.type === 'radio' || field.type === 'checkbox') {
                    const questionId = field.getAttribute('data-question-id');
                    const relatedFields = document.querySelectorAll(`input[name="answers[${questionId}][]"]`);

                    relatedFields.forEach(option => {
                        option.addEventListener('change', () => {
                            const errorSpan = document.getElementById(`error-${questionId}`);
                            if (errorSpan) errorSpan.textContent = '';
                        });
                    });
                }
            });
        });
    </script>
</head>

<?php include '../assets/header.php'; ?>

<body>
    <main>
        <h1><?= htmlspecialchars($form['title']) ?></h1>
        <p><?= htmlspecialchars($form['description']) ?></p>


        <?php if ($isCreator && $form['is_active']): ?>
            <!-- Show Edit Form button for active forms -->
            <div class="edit-form-btn-wrapper">
                <a href="edit_form.php?form_id=<?= $form['id'] ?>" class="edit-form-button">Edit Form</a>
            </div>

        <?php endif; ?>

        <form action="submit_response.php" method="POST" onsubmit="validateForm(event)">
            <input type="hidden" name="form_id" value="<?= $form['id'] ?>">
            <?php foreach ($form['questions'] as $question): ?>
                <p class="question">
                    <?= htmlspecialchars($question['question_text']) ?>
                    <?php if ($question['is_required']): ?>
                        <span>*</span>
                    <?php endif; ?>
                </p>
                <?php if ($question['answer_type'] == 'text'): ?>
                    <input type="text" name="answers[<?= $question['id'] ?>]"
                        data-required="<?= $question['is_required'] ?>"
                        data-question-id="<?= $question['id'] ?>">
                    <span id="error-<?= $question['id'] ?>" class="error"></span>
                <?php elseif ($question['answer_type'] == 'big_text'): ?>
                    <textarea name="answers[<?= $question['id'] ?>]"
                        data-required="<?= $question['is_required'] ?>"
                        data-question-id="<?= $question['id'] ?>"></textarea>
                    <span id="error-<?= $question['id'] ?>" class="error"></span>
                <?php elseif (in_array($question['answer_type'], ['multiple_choice', 'checkbox'])): ?>
                    <?php foreach ($question['choices'] as $choice): ?>
                        <label>
                            <input type="<?= $question['answer_type'] == 'multiple_choice' ? 'radio' : 'checkbox' ?>"
                                name="answers[<?= $question['id'] ?>][]"
                                value="<?= htmlspecialchars($choice['option_text']) ?>"
                                data-required="<?= $question['is_required'] ?>"
                                data-question-id="<?= $question['id'] ?>">
                            <?= htmlspecialchars($choice['option_text']) ?>
                        </label>
                    <?php endforeach; ?>
                    <span id="error-<?= $question['id'] ?>" class="error"></span>
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit" <?= $isCreator ? 'disabled' : '' ?>>Submit</button>
        </form>
    </main>
</body>

</html>