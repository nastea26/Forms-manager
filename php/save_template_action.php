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
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";
// exit();

// Get form data
$title = $_POST['title'];
$description = $_POST['description'];

// Validate PIN length if provided

// Create the form
$formLink = $form->createTemplate($userId, $title, $description);
if (!$formLink) {
    die('Error creating form.');
}
$formDetails = $form->getTemplateByLink($formLink);

// Process questions (if any)
foreach ($_POST['questions'] as $index => $question) {
    $isRequired = isset($question['required']) ? 1 : 0;

    // Add question
    $questionId = $form->addTemplateQuestion($formDetails["id"], $question['text'], $question['type'], $isRequired);

    // Add options if they exist
    if (!empty($question['options'])) {
        foreach ($question['options'] as $optionIndex => $optionText) {
            $form->addTemplateChoice($questionId, $optionText);
        }
    }
}

// After successfully saving the form in save_form
// Get the current server's protocol (http or https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

// Get the server's host (e.g., localhost or example.com)
$host = $_SERVER['HTTP_HOST'];

// Get the directory path of the script (removes /php/save_form)
$path = dirname($_SERVER['SCRIPT_NAME']);

$formLink = $protocol . $host . $path . '/view_form.php?q=' . urlencode($formLink);

$_SESSION['sharePopup'] = false;
header('Location: ../');
exit();
