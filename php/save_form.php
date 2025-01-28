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
$publish = isset($_POST['publish']) ? 1 : 0;
$allowNonUsers = isset($_POST['allow_non_users']) ? 1 : 0;
$pin = isset($_POST['pin']) && !empty($_POST['pin']) ? $_POST['pin'] : null;

// Validate PIN length if provided
if ($pin && (strlen($pin) < 4 || strlen($pin) > 12)) {
    die('Error: PIN must be between 4 and 12 characters.');
}

// Create the form
$formLink = $form->createForm($userId, $title, $description, $publish, $allowNonUsers, $pin);
if (!$formLink) {
    die('Error creating form.');
}
$formDetails = $form->getFormDetails($formLink);

// Process questions (if any)
foreach ($_POST['questions'] as $index => $question) {
    $isRequired = isset($question['required']) ? 1 : 0;

    // Add question
    $questionId = $form->addQuestion($formDetails["id"], $question['text'], $question['type'], $isRequired);

    // Add options if they exist
    if (!empty($question['options'])) {
        foreach ($question['options'] as $optionIndex => $optionText) {
            $form->addOption($questionId, $optionText);
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

$_SESSION['sharePopup'] = true;
$_SESSION['shareLink'] = $formLink;
header('Location: ../');
exit();
