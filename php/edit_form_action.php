<?php
require 'Form.php';
require 'db.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$formHandler = new Form($database);

// Validate the request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['form_id'])) {
    echo "Invalid request.";
    exit();
}

$formId = $_POST['form_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$questions = $_POST['questions'] ?? [];
$questionIds = $_POST['question_ids'] ?? [];
$deletedQuestions = $_POST['deleted_questions'] ?? [];

// Update the form's title and description
$updateFormQuery = "UPDATE forms SET title = ?, description = ? WHERE id = ?";
if (!$database->searchQuery($updateFormQuery, [$title, $description, $formId])) {
    echo "Failed to update form details.";
    exit();
}

// Process questions: update, add, or delete
foreach ($questions as $index => $questionText) {
    $questionId = $questionIds[$index] ?? null;

    if ($questionId && !in_array($questionId, $deletedQuestions)) {
        // Update existing question
        $updateQuestionQuery = "UPDATE questions SET question_text = ? WHERE id = ?";
        $database->searchQuery($updateQuestionQuery, [$questionText, $questionId]);
    } elseif (!$questionId) {
        // Add new question
        $formHandler->addQuestion($formId, $questionText, 'text', 1); // Defaulting to 'text' type and required=true
    }
}

// Remove questions flagged for deletion
if (!empty($deletedQuestions)) {
    $placeholders = implode(',', array_fill(0, count($deletedQuestions), '?'));
    $deleteQuestionsQuery = "DELETE FROM questions WHERE id IN ($placeholders)";
    $database->searchQuery($deleteQuestionsQuery, $deletedQuestions);
}

// Redirect to the dashboard or a success page
header('Location: dashboard.php');
exit();
