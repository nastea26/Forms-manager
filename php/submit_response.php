<?php
require 'Form.php';
require 'db.php';

if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$formHandler = new Form($database);
$formLink = $_POST['form_link'];
$respondentId = $_SESSION['user_id'] ?? null;

// Retrieve form details
$formDetails = $formHandler->getFormDetails($formLink);
$respondentIDs = $formHandler->getRespondentIds($formDetails['id']);
// Check if the respondent is the creator of the form
if ($formDetails['user_id'] == $respondentId) {
    die('Error: You cannot submit responses to your own form.');
}
if (in_array($respondentId, $respondentIDs)) {
    die('Error: You have already submitted responses to this form.');
}

// Validate required fields
$answers = $_POST['answers'];
foreach ($formDetails['questions'] as $question) {
    if ($question['is_required']) {
        $answer = $answers[$question['id']] ?? null;
        if (is_array($answer)) {
            if (empty(array_filter($answer))) {
                die('Error: Required question is missing.');
            }
        } elseif (empty($answer)) {
            die('Error: Required question is missing.');
        }
    }
}

// Process the response
if ($formHandler->submitResponse($formDetails['id'], $respondentId, $answers)) {
    $formHandler->increment_submission_count($formLink);
    header('Location: ../thank_you.php');
} else {
    header('Location: error.php');
}
exit;
