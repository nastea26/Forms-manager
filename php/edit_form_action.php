<?php
require 'db.php';
require 'Form.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize Form instance
$formHandler = new Form($database);

$formId = $_POST['form_id'] ?? null;
if (!$formId) {
    echo "Form ID is required.";
    exit();
}

// Get form title and description
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($title) || empty($description)) {
    echo "Form title and description are required.";
    exit();
}

$isActive = isset($_POST['is_active']) ? 1 : 0;

// Update form title, description, and active status
$updateFormQuery = "UPDATE forms SET title = ?, description = ?, is_active = ? WHERE id = ?";
$database->searchQuery($updateFormQuery, [$title, $description, $isActive, $formId]);

// Process questions
$questions = $_POST['questions'] ?? [];
$deletedQuestions = [];

$validTypes = ['text', 'multiple_choice', 'checkbox'];

foreach ($questions as $index => $question) {
    $questionId = $question['id'] ?? null;
    $questionText = trim($question['text'] ?? '');
    $questionType = in_array($question['type'] ?? 'text', $validTypes) ? $question['type'] : 'text';
    $isRequired = isset($question['required']) ? 1 : 0;
    if (isset($question['delete']) && $question['delete'] == 1) {
        if ($questionId) {
            $deletedQuestions[] = $questionId;
        }
        continue; // Skip deleted questions
    }

    if (empty($questionText)) {
        continue; // Skip empty questions
    }

    if ($questionId) {
        // Update existing question
        $updateQuestionQuery = "UPDATE questions SET question_text = ?, answer_type = ?, is_required = ? WHERE id = ?";
        $database->searchQuery($updateQuestionQuery, [$questionText, $questionType, $isRequired, $questionId]);

        // Delete existing options if type changes to text
        if (!in_array($questionType, ['multiple_choice', 'checkbox'])) {
            $deleteOptionsQuery = "DELETE FROM choices WHERE question_id = ?";
            $database->searchQuery($deleteOptionsQuery, [$questionId]);
        }
    } else {
        // Add a new question using addQuestion
        $questionId = $formHandler->addQuestion($formId, $questionText, $questionType, $isRequired);
    }

    // Add options for multiple-choice or checkbox questions
    if (in_array($questionType, ['multiple_choice', 'checkbox'])) {
        $options = $question['options'] ?? [];
        foreach ($options as $optionText) {
            $optionText = trim($optionText);
            if (!empty($optionText)) {
                $formHandler->addOption($questionId, $optionText);
            }
        }
    }
}

// Delete removed questions using deleteQuestions method
if (!empty($deletedQuestions)) {
    $formHandler->deleteQuestions($deletedQuestions);
}

header("Location: dashboard.php");
exit();
