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

$formLink = $_POST['form_link'] ?? null;
if (!$formLink) {
    echo "Form link is required.";
    exit();
}

// Fetch form ID using the form link
$formQuery = "SELECT id FROM forms WHERE link = ?";
$formResult = $database->searchQuery($formQuery, [$formLink])->fetch_assoc();

if (!$formResult) {
    echo "Invalid form link.";
    exit();
}

$formId = $formResult['id'];

// Get form title and description
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($title) || empty($description)) {
    echo "Form title and description are required.";
    exit();
}

// Handle is_active (publish form slider)
$isActive = isset($_POST['is_active']) ? 1 : 0;

// Handle available_for_non_users and PIN
$availableForNonUsers = isset($_POST['available_for_non_users']) ? 1 : 0;
$pin = (isset($_POST['pin_enabled']) && !empty($_POST['pin'])) ? trim($_POST['pin']) : null;

// Validate PIN if provided
if (!is_null($pin) && (!ctype_digit($pin) || strlen($pin) < 4 || strlen($pin) > 12)) {
    echo "Invalid PIN. It must be a numeric value between 4 and 12 digits.";
    exit();
}

// Update form title, description, active status, availability, and PIN
$updateFormQuery = "UPDATE forms SET title = ?, description = ?, is_active = ?, available_for_non_users = ?, pin = ? WHERE id = ?";
$database->searchQuery($updateFormQuery, [$title, $description, $isActive, $availableForNonUsers, $pin, $formId]);

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
            $submittedOptions = $question['options'] ?? []; // Accessing 'options' from the $_POST array
            $existingOptionsQuery = "SELECT id, option_text FROM choices WHERE question_id = ?";
            $existingOptions = $database->searchQuery($existingOptionsQuery, [$questionId])->fetch_all(MYSQLI_ASSOC);

            // Map existing options for easier comparison
            $existingOptionsMap = [];
            foreach ($existingOptions as $option) {
                $existingOptionsMap[$option['id']] = $option['option_text'];
            }

            $processedOptionIds = [];

            foreach ($submittedOptions as $index => $optionText) {
                $optionText = trim($optionText); // Ensure option text is sanitized
                if (empty($optionText)) {
                    continue; // Skip empty options
                }

                $optionId = array_search($optionText, $existingOptionsMap); // Check if option exists
                if ($optionId) {
                    unset($existingOptionsMap[$optionId]); // Mark this as processed
                    $processedOptionIds[] = $optionId;
                } else {
                    // Add new option if it doesn't exist
                    $addOptionQuery = "INSERT INTO choices (question_id, option_text) VALUES (?, ?)";
                    $database->searchQuery($addOptionQuery, [$questionId, $optionText]);
                }
            }

            // Delete any options that were not processed (removed by the user)
            foreach (array_keys($existingOptionsMap) as $optionId) {
                $deleteOptionQuery = "DELETE FROM choices WHERE id = ?";
                $database->searchQuery($deleteOptionQuery, [$optionId]);
            }
        } else {
            // If the question is no longer of type multiple_choice or checkbox, delete all associated options
            $deleteOptionsQuery = "DELETE FROM choices WHERE question_id = ?";
            $database->searchQuery($deleteOptionsQuery, [$questionId]);
        }
    } else {
        // Add a new question
        $questionId = $formHandler->addQuestion($formId, $questionText, $questionType, $isRequired);

        if (!$questionId) {
            error_log("Failed to add question. Form ID: $formId, Question Text: $questionText");
            echo "Failed to add question.";
            exit();
        }

        // Add options for new multiple-choice or checkbox questions
        if (in_array($questionType, ['multiple_choice', 'checkbox'])) {
            $options = $question['choices'] ?? [];

            foreach ($options as $choice) {
                $optionText = isset($choice['text']) && is_string($choice['text']) ? trim($choice['text']) : null;

                if (!empty($optionText)) {
                    $formHandler->addOption($questionId, $optionText);
                }
            }
        }
    }
}


// Delete removed questions
if (!empty($deletedQuestions)) {
    $formHandler->deleteQuestions($deletedQuestions);
}

header("Location: dashboard.php");
exit();
