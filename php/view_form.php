<?php
include 'Form.php';
include 'db.php';
$formHandler = new Form($database);
$form = $formHandler->getFormDetails($_GET['id']);

include '../assets/header.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($form['title']) ?></title>
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <h1><?= htmlspecialchars($form['title']) ?></h1>
    <p><?= htmlspecialchars($form['description']) ?></p>
    <form action="submit_response.php" method="POST">
        <input type="hidden" name="form_id" value="<?= $form['id'] ?>">
        <?php foreach ($form['questions'] as $question): ?>
            <p class="question">
                <?= htmlspecialchars($question['question_text']) ?>
                <?php if ($question['is_required']): ?> <span>*</span><?php endif; ?>
            </p>
            <?php if ($question['answer_type'] == 'text'): ?>
                <input type="text" name="answers[<?= $question['id'] ?>]">
            <?php elseif ($question['answer_type'] == 'big_text'): ?>
                <textarea name="answers[<?= $question['id'] ?>]"></textarea>
            <?php elseif (in_array($question['answer_type'], ['multiple_choice', 'checkbox'])): ?>
                <?php foreach ($question['choices'] as $choice): ?>
                    <label>
                        <input type="<?= $question['answer_type'] == 'multiple_choice' ? 'radio' : 'checkbox' ?>"
                            name="answers[<?= $question['id'] ?>][]"
                            value="<?= htmlspecialchars($choice['option_text']) ?>">
                        <?= htmlspecialchars($choice['option_text']) ?>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit">Submit</button>
    </form>
</body>

</html>