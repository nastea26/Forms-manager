<?php
if (!isset($_SESSION)) session_start();
include 'db.php';
include 'Form.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$form = new Form($database);
$userId = $_SESSION['user_id'];

// Get whether the form should be published
$publish = isset($_POST['publish']) ? 1 : 0;

// Create the form and get the form ID
$formId = $form->createForm($userId, $_POST['title'], $_POST['description'], $publish);

if (!$formId) {
    die('Error creating form.');
}

// Loop through questions
foreach ($_POST['questions'] as $index => $question) {
    $isRequired = isset($question['required']) ? 1 : 0;

    // Add question
    $questionId = $form->addQuestion($formId, $question['text'], $question['type'], $isRequired);

    if (!$questionId) {
        include '../assets/header.php';
        echo "<main>";
        error_log("Failed to add question #{$index}");
        echo "</main>";
        continue;
    }

    // Add options if they exist
    if (!empty($question['options'])) {
        foreach ($question['options'] as $optionIndex => $optionText) {
            $result = $form->addOption($questionId, $optionText);
            if (!$result) {
                error_log("Failed to add option #{$optionIndex} for question #{$index}");
            }
        }
    }
}

header('Location: ../');
