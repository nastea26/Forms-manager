<?php
session_start();
include 'db.php';
include 'Form.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$form = new Form($database);
$userId = $_SESSION['user_id'];
$formId = $form->createForm($userId, $_POST['title'], $_POST['description']);

foreach ($_POST['questions'] as $question) {

    $questionId = $form->addQuestion($formId, $question['text'], $question['type'], isset($question['required']));

    if (isset($question['options'])) {
        foreach ($question['options'] as $option) {
            $form->addOption($questionId, $option);
        }
    }
}

header('Location:../');