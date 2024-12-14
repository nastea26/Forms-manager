<?php
require 'Form.php';
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "not logged in";
    exit();
}

$formHandler = new Form($database);

$formId = $_POST['form_id'];
$respondentId = $_SESSION['user_id'] ?? null; // Null for guest users
$answers = $_POST['answers'];

// Ensure answers are processed correctly (handles single or multi-answer scenarios)
if ($formHandler->submitResponse($formId, $respondentId, $answers)) {
    header('Location: ../thank_you.html');
} else {
    header('Location: error.php');
}
exit;
