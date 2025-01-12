<?php
require 'db.php';
require 'Form.php';
if (!isset($_SESSION)) session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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

foreach ($questions as $question) {
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

        // Handle options for multiple-choice or checkbox questions
        if (in_array($questionType, ['multiple_choice', 'checkbox'])) {
            $submittedOptions = $question['choices'] ?? [];

            // Fetch existing options from the database
            $existingOptionsQuery = "SELECT id, option_text FROM choices WHERE question_id = ?";
            $existingOptions = $database->searchQuery($existingOptionsQuery, [$questionId])->fetch_all(MYSQLI_ASSOC);

            // Convert existing options to associative arrays for comparison
            $existingOptionsMap = [];
            foreach ($existingOptions as $option) {
                $existingOptionsMap[$option['id']] = $option['option_text'];
            }

            // Determine which options to add, update, or delete
            $submittedOptionTexts = array_filter(array_map(function ($choice) {
                return isset($choice['text']) && is_string($choice['text']) ? trim($choice['text']) : null;
            }, $submittedOptions));
            $processedOptionIds = [];

            foreach ($submittedOptionTexts as $submittedText) {
                $existingOptionId = array_search($submittedText, $existingOptionsMap, true);

                if ($existingOptionId !== false) {
                    // Option exists, no change needed
                    $processedOptionIds[] = $existingOptionId;
                    unset($existingOptionsMap[$existingOptionId]); // Mark as processed
                } else {
                    // Add new option only if it doesn't already exist
                    $formHandler->addOption($questionId, $submittedText);
                }
            }

            // Remove any options that were not processed (i.e., removed by the user)
            foreach ($existingOptionsMap as $optionId => $optionText) {
                $deleteOptionQuery = "DELETE FROM choices WHERE id = ?";
                $database->searchQuery($deleteOptionQuery, [$optionId]);
            }
        } else {
            // Delete existing options if type changes to text
            $deleteOptionsQuery = "DELETE FROM choices WHERE question_id = ?";
            $database->searchQuery($deleteOptionsQuery, [$questionId]);
        }
    } else {
        // Add a new question using addQuestion
        $questionId = $formHandler->addQuestion($formId, $questionText, $questionType, $isRequired);

        if (!$questionId) {
            error_log("Failed to add question. Form ID: $formId, Question Text: $questionText");
            echo "Failed to add question.";
            exit();
        }

        // Add options for new multiple-choice or checkbox questions
        if (in_array($questionType, ['multiple_choice', 'checkbox'])) {
            $options = $question['choices'] ?? [];

            // Map and filter the options to ensure valid strings
            $submittedOptionTexts = array_filter(array_map(function ($choice) {
                return isset($choice['text']) && is_string($choice['text']) ? trim($choice['text']) : null;
            }, $options));

            foreach ($submittedOptionTexts as $optionText) {
                if (!empty($optionText)) {
                    $result = $formHandler->addOption($questionId, $optionText);

                    if (!$result) {
                        error_log("Failed to add option. Question ID: $questionId, Option Text: $optionText");
                        echo "Failed to add option.";
                        exit();
                    }
                }
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
