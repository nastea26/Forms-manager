<?php
require_once 'db.php';
include 'Form.php';

// Function to get both user-created and answered forms
function getRecentForms($userId)
{
    global $database;
    $formHandler = new Form($database);

    // Fetch user-created forms
    $userForms = $formHandler->getUserForms($userId);

    // Fetch answered forms
    $answeredForms = $formHandler->getAnsweredForms($userId);

    // Merge the two arrays
    $recentForms = array_merge($userForms, $answeredForms);

    // Sort by 'created_at' in descending order
    usort($recentForms, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    return $recentForms;
}
function fetchTemplates()
{
    global $database;
    $formHandler = new Form($database);
    return $formHandler->getTemplates();
}
// API handler
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetchRecentForms') {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User not logged in']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    try {
        $recentForms = getRecentForms($userId);
        $templates = fetchTemplates();
        // Return the combined list of forms
        echo json_encode(['forms' => $recentForms, 'templates' => $templates]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to fetch forms']);
    }
}
