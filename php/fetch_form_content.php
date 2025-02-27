<?php
session_start();
include 'Form.php';
include 'db.php';

if (!isset($_GET['form_link'], $_GET['pin'])) {
    echo "INVALID PIN";
    exit();
}

$form_link = $_GET['form_link'];
$submitted_pin = $_GET['pin'];

$formHandler = new Form($database);
$form = $formHandler->getFormDetails($form_link); // Create a method that fetches the form by link

// Check if the submitted PIN is correct
if ($form['pin'] !== $submitted_pin) {
    echo "INVALID PIN";
    exit();
}

// Build the form HTML (similar to what you had before)
// You can include the form title, description, and all questions. For brevity, here's a simple example:
ob_start();
?>
<h1><?= htmlspecialchars($form['title']) ?></h1>
<p><?= htmlspecialchars($form['description']) ?></p>
<div class="form-buttons">
    <?php if ($form['user_id'] == $_SESSION['user_id'] && $form['is_active']): ?>
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
    <button type="submit" <?= $form['user_id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>Submit</button>
</form>
<?php
$formHtml = ob_get_clean();
echo $formHtml;
