<?php
require 'db.php';
require 'Form.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$formHandler = new Form($database);
$formId = $_GET['form_id'] ?? null;

// add something for when the parameter from_id isnt specified

include 'checkUserFormAccess.php';
if (checkAccessToFrom($formHandler, $formId, "Form Results")) exit();

if (!$formId) {
    header('Location: dashboard.php');
    exit();
}


// Get all form questions and their options
$questionsWithOptions = $formHandler->getQuestionsWithOptions($formId);

// Get all form responses
$responses = $formHandler->getFormResponses($formId);

// Prepare data structure combining options with response counts
$answers_array = [];
foreach ($questionsWithOptions as $question) {
    $questionText = $question['question_text'];
    $answers_array[$questionText] = [
        'responses' => [],
        'median' => null, // Placeholder for median
    ];

    if (!empty($question['options'])) {
        foreach ($question['options'] as $option) {
            $answers_array[$questionText]['responses'][$option] = 0; // Initialize option counts
        }
    }

    foreach ($responses as $response) {
        foreach ($response['answers'] as $answer) {
            if ($answer['question_text'] === $questionText) {
                if (!empty($question['options'])) {
                    if (isset($answers_array[$questionText]['responses'][$answer['answer_text']])) {
                        $answers_array[$questionText]['responses'][$answer['answer_text']]++;
                    }
                } else {
                    $answers_array[$questionText]['responses'][] = $answer['answer_text'];
                }
            }
        }
    }
}

include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Results</title>
    <link rel="stylesheet" href="../styles/dashboard_results.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="../js/results.js" defer data-questions="<?php echo htmlspecialchars(json_encode($answers_array), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <style>
        .question-slide,
        .chart-slide {
            display: none;
        }

        .question-slide.active,
        .chart-slide.active {
            display: block;
        }

        .slider-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .tooltip-icon {
            position: relative;
            cursor: pointer;
            display: inline-block;
            font-size: 1.2em;
            color: #007bff;
            font-weight: bold;
        }

        .tooltip-content {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            white-space: nowrap;
            transition: opacity 0.3s ease;
            z-index: 10;
        }

        .tooltip-icon:hover .tooltip-content {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<?php include '../assets/header.php'; ?>

<body>
    <main>
        <div class="slider-container">
            <h1 class="results-title">Form Results</h1>
            <a href="dashboard.php" class="button back-button">Back to My Forms</a>

            <div id="slider">
                <!-- Questions will be dynamically inserted here -->
            </div>

            <div class="navigation-buttons">
                <button id="prevButton" class="button" disabled>Previous</button>
                <button id="nextButton" class="button">Next</button>
            </div>
        </div>
    </main>
</body>

</html>