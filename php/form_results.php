<?php
require 'db.php';
require 'Form.php';
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$formHandler = new Form($database);
$link = $_GET['q'] ?? null;
$form = $formHandler->getFormDetails($link);

// add something for when the parameter from_id isnt specified

include 'checkUserFormAccess.php';
if (checkAccessToFrom($formHandler, $form["id"], "Form Results")) exit();

if (!$link) {
    header('Location: dashboard.php');
    exit();
}


// Get all form questions and their options
$questionsWithOptions = $formHandler->getQuestionsWithOptions($form["id"]);

// Get all form responses
$responses = $formHandler->getFormResponses($form["id"]);

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
    <link rel="stylesheet" href="../styles/modal.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/dashboard_results.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script src="../js/results.js" defer data-questions="<?php echo htmlspecialchars(json_encode($answers_array), ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script type="module" src="../js/shareModal.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="result-body">
    <?php include_once  '../assets/header.php';
    createHeader("../");
    ?>
    <main>
        <div class="slider-container">
            <h1 class="results-title">Form Results</h1>
            <div class="redirect-wrapper">
                <a href="dashboard.php" class="button back-button">My Forms</a>
                <button id="shareButton" class="share-form-button">Share form</button>
            </div>
            <div id="slider">
                <!-- Questions will be dynamically inserted here -->
            </div>

            <div class="navigation-buttons">
                <button id="prevButton" class="button" disabled>Previous</button>
                <button id="nextButton" class="button">Next</button>
            </div>

            <!-- Share Button -->
        </div>
    </main>

    <script type="module">
        import Modal from '../js/shareModal.js';

        document.addEventListener('DOMContentLoaded', () => {
            const shareButton = document.getElementById('shareButton');
            const formLink = <?= json_encode($form['link']) ?>; // Assuming the form link is available in the `$form` array

            shareButton.addEventListener('click', () => {
                const fullFormLink = `${window.location.origin}/php/view_form.php?q=${formLink}`;
                const modal = new Modal();
                modal.initModal();
                modal.showModal(fullFormLink); // Show the modal with the share link
            });
        });
    </script>
    <?php
    include '../assets/footer.php';
    ?>
</body>

</html>