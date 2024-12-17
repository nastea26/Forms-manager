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

if (!$formId) {
    header('Location: dashboard.php');
    exit();
}

$responses = $formHandler->getFormResponses($formId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Results</title>
    <link rel="stylesheet" href="../styles/dashboard_results.css">
</head>
<body>
    <div class="results-container">
        <h1 class="results-title">Form Results</h1>
        <a href="dashboard.php" class="button back-button">Back to My Forms</a>
        <ul class="responses-list">
            <?php foreach ($responses as $response): ?>
                <li class="response-item">
                    <h2 class="response-header">Response from <?= $response['respondednt_id'] ?: 'Guest' ?></h2>
                    <p class="response-date">Submitted on: <?= $response['created_at'] ?></p>
                    <ul class="answers-list">
                        <?php foreach ($response['answers'] as $answer): ?>
                            <li class="answer-item">
                                <strong class="question-text"><?= htmlspecialchars($answer['question_text']) ?>:</strong> 
                                <span class="answer-text"><?= htmlspecialchars($answer['answer_text']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>