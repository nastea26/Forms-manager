<?php
if (!isset($_SESSION)) session_start();

include 'Form.php';
include 'db.php';

$formHandler = new Form($database);
$form = $formHandler->getFormDetails($_GET['q']);

// Redirect to edit page if the form is inactive and the user is the creator
if (!$form['is_active'] && isset($_SESSION['user_id']) && $form['user_id'] == $_SESSION['user_id']) {
    header('Location: edit_form.php?q=' . $form['link']);
    exit();
}

// Redirect inactive forms to list page for non-creators
if (!$form['is_active']) {
    header('Location: list_forms.php');
    exit();
}

// If the form is not available for non-users and no user is logged in, force login
if (!$form['available_for_non_users'] && !isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if the current user is the creator
$isCreator = (isset($_SESSION['user_id']) && $form['user_id'] == $_SESSION['user_id']);

// Prevent duplicate responses (only for logged in users)
if (isset($_SESSION['user_id'])) {
    $respondentIDs = $formHandler->getRespondentIds($form['id']);
    if (in_array($_SESSION['user_id'], $respondentIDs) && !$isCreator) {
        echo "You've already submitted a response for this form";
        exit();
    }
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);
$formLink = $protocol . $host . $path . '/view_form.php?q=' . urlencode($form['link']);

// Determine required PIN length based on stored PIN
$pinLength = isset($form['pin']) ? strlen($form['pin']) : 0;
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
    <link rel="stylesheet" href="../styles/header.css">

    <script type="module" src="../js/shareModal.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Client-side form validation and share modal remain unchanged
        document.addEventListener('DOMContentLoaded', () => {
            const shareButton = document.getElementById('shareButton');
            if (shareButton) {
                shareButton.addEventListener('click', () => {
                    const shareLink = <?= json_encode($formLink) ?>;
                    const modal = new Modal();
                    modal.initModal();
                    modal.showModal(shareLink);
                });
            }
        });
    </script>
</head>
<?php include_once  '../assets/header.php';
createHeader("../");
?>

<body>
    <main>
        <?php if (isset($form['pin']) && !$isCreator): ?>
            <!-- Show PIN entry; do not output the full form HTML yet -->
            <div id="pin-protection">
                <h2>This form is protected by a PIN</h2>
                <div id="pin-inputs"></div>
                <span id="pin-error" class="error"></span>
            </div>
            <!-- Empty container for form content; will be filled via AJAX if PIN is correct -->
            <div id="form-content" style="display: none;"></div>
        <?php else: ?>
            <!-- No PIN protection required or user is creator; output form immediately -->
            <div id="form-content">
                <h1><?= htmlspecialchars($form['title']) ?></h1>
                <p><?= htmlspecialchars($form['description']) ?></p>
                <div class="form-buttons">
                    <?php if ($isCreator && $form['is_active']): ?>
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
                            <input type="text" name="answers[<?= $question['id'] ?>]" data-required="<?= $question['is_required'] ?>" data-question-id="<?= $question['id'] ?>">
                            <span id="error-<?= $question['id'] ?>" class="error"></span>
                        <?php elseif ($question['answer_type'] == 'big_text'): ?>
                            <textarea name="answers[<?= $question['id'] ?>]" data-required="<?= $question['is_required'] ?>" data-question-id="<?= $question['id'] ?>"></textarea>
                            <span id="error-<?= $question['id'] ?>" class="error"></span>
                        <?php elseif (in_array($question['answer_type'], ['multiple_choice', 'checkbox'])): ?>
                            <?php foreach ($question['choices'] as $choice): ?>
                                <label>
                                    <input type="<?= $question['answer_type'] == 'multiple_choice' ? 'radio' : 'checkbox' ?>" name="answers[<?= $question['id'] ?>][]" value="<?= htmlspecialchars($choice['option_text']) ?>" data-required="<?= $question['is_required'] ?>" data-question-id="<?= $question['id'] ?>">
                                    <?= htmlspecialchars($choice['option_text']) ?>
                                </label>
                            <?php endforeach; ?>
                            <span id="error-<?= $question['id'] ?>" class="error"></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button type="submit" <?= $isCreator ? 'disabled' : '' ?>>Submit</button>
                </form>
            </div>
        <?php endif; ?>
        <script type="module">
            document.addEventListener('DOMContentLoaded', () => {
                const pinProtection = document.getElementById('pin-protection');
                if (pinProtection) {
                    const pinLength = <?= json_encode($pinLength) ?>;
                    const pinInputsContainer = document.getElementById('pin-inputs');
                    // Generate square input fields
                    for (let i = 0; i < pinLength; i++) {
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.maxLength = 1;
                        input.classList.add('pin-input');
                        pinInputsContainer.appendChild(input);
                    }
                    const inputs = document.querySelectorAll('.pin-input');
                    let debounceTimer;
                    // Listen for input on each square
                    inputs.forEach((input, index) => {
                        input.addEventListener('input', () => {
                            // Remove invalid class when user starts typing
                            input.classList.remove('invalid');
                            clearTimeout(debounceTimer);
                            // Auto-advance focus if filled
                            if (input.value && index < inputs.length - 1) {
                                inputs[index + 1].focus();
                            }
                            debounceTimer = setTimeout(() => {
                                let pinValue = '';
                                let allFilled = true;
                                inputs.forEach(inp => {
                                    if (inp.value === '') {
                                        allFilled = false;
                                    }
                                    pinValue += inp.value;
                                });
                                if (allFilled) {
                                    $.ajax({
                                        url: 'fetch_form_content.php',
                                        method: 'GET',
                                        data: {
                                            form_link: <?= json_encode($form['link']) ?>,
                                            pin: pinValue
                                        },
                                        dataType: 'html',
                                        success: function(response) {
                                            if (response.trim() === "INVALID PIN") {
                                                // Add invalid class to all inputs
                                                inputs.forEach(inp => {
                                                    inp.classList.add('invalid');
                                                });
                                            } else {
                                                document.getElementById('form-content').innerHTML = response;
                                                document.getElementById('form-content').style.display = 'block';
                                                pinProtection.style.display = 'none';
                                            }
                                        },
                                        error: function() {
                                            document.getElementById('pin-error').textContent = "Error fetching form content.";
                                        }
                                    });
                                }
                            }, 100);
                        });
                    });
                }
            });
        </script>
    </main>
</body>

</html>