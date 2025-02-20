<?php
if (!isset($_SESSION)) session_start();

include 'Form.php';
include 'db.php';

$formHandler = new Form($database);
$form = $formHandler->getFormDetails($_GET['q']);

// Redirect to edit page if the form is inactive and the user is the creator
if (!$form['is_active'] && $form['user_id'] == $_SESSION['user_id']) {
    header('Location: edit_form.php?q=' . $form['link']);
    exit();
}

// Redirect inactive forms to list page for non-creators
if (!$form['is_active']) {
    header('Location: list_forms.php');
    exit();
}
//not avalible for guests and user not logged in
if (!$form['available_for_non_users'] && !isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
// Check if the current user is the creator
$isCreator = $form['user_id'] == $_SESSION['user_id'];

$respondentIDs = $formHandler->getRespondentIds($form['id']);
if (in_array($_SESSION['user_id'], $respondentIDs) && !$isCreator && isset($_SESSION['user_id'])) {
    echo "You've already subbmited a response for this form";
    exit();
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

$host = $_SERVER['HTTP_HOST'];

$path = dirname($_SERVER['SCRIPT_NAME']);

$formLink = $protocol . $host . $path . '/view_form.php?q=' . urlencode($form['link']);

?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($form['title']) ?></title>
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($form['title']) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($form['description']) ?>" />
    <meta property="og:image" content=" " />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/modal.css">
    <script type="module" src="../js/shareModal.js" defer></script>
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
        <?php if (isset($form['pin']) && !$isCreator): ?>
            <div id="pin-protection">
                <h2>This form is protected by a PIN</h2>
                <label for="form-pin">Enter PIN:</label>
                <input type="text" id="form-pin" maxlength="12" placeholder="Enter PIN">
                <span id="pin-error" class="error"></span>
            </div>

            <!-- Hide the form initially -->
            <div id="form-content" style="display: none;">
            <?php else: ?>
                <!-- Directly display the form if no PIN is required -->
                <div id="form-content">
                <?php endif; ?>

                <h1><?= htmlspecialchars($form['title']) ?></h1>
                <p><?= htmlspecialchars($form['description']) ?></p>

                <div class="form-buttons"> <?php if ($isCreator && $form['is_active']): ?>
                        <div class="edit-form-btn-wrapper">
                            <a href="edit_form.php?q=<?= $form['link'] ?>" class="edit-form-button">Edit Form</a>
                        </div>
                    <?php endif; ?>
                    <div class="share-form-btn-wrapper">
                        <button id="shareButton" class="share-form-button">Share</button>
                    </div>
                </div>

                <form action="submit_response.php" method="POST" onsubmit="validateForm(event)">
                    <input type="hidden" name="form_link" value="<?= $form['link'] ?>">
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
                </div>

                <script type="module">
                    import Modal from '../js/shareModal.js';
                    document.addEventListener('DOMContentLoaded', () => {
                        const formPin = <?= json_encode($form['pin'] ?? null) ?>;
                        const pinInput = document.getElementById('form-pin');
                        const pinError = document.getElementById('pin-error');
                        const formContent = document.getElementById('form-content');

                        if (pinInput) {
                            pinInput.addEventListener('input', () => {
                                const userInput = pinInput.value.trim();

                                if (userInput === formPin) {
                                    // If the input matches the PIN, show the form
                                    pinError.textContent = '';
                                    formContent.style.display = 'block';
                                    document.getElementById('pin-protection').style.display = 'none';
                                }
                            });
                        }
                        // Share button functionality
                        const shareButton = document.getElementById('shareButton');
                        shareButton.addEventListener('click', () => {
                            const shareLink = <?= json_encode($formLink) ?>; // Get the form link
                            const modal = new Modal(); // Assuming you have a Modal class
                            modal.initModal(); // Initialize the modal
                            modal.showModal(shareLink); // Show the modal with the share link
                        });
                    });
                </script>
    </main>
</body>

</html>