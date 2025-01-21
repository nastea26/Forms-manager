<?php
require_once 'db.php';
include 'Form.php';

// Function to get recent forms for a specific user
function getRecentForms($userId)
{
    global $database; // Assuming $database is your DB connection
    $formHandler = new Form($database);

    // Assuming getUserForms returns an array of form objects or associative arrays
    return $formHandler->getUserForms($userId);
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

        // Check if forms exist and return as expected format
        if (!empty($recentForms)) {
            echo json_encode(['forms' => $recentForms]);  // Wrap the result in a 'forms' key
        } else {
            echo json_encode(['forms' => []]);  // Return an empty array if no forms are found
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Failed to fetch forms']);
    }
}
