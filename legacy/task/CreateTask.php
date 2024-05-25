<?php
// Include the file for all database-related operations
require_once('../DBOperations.php');

// Retrieve user data from the session
$userDataSession = $operations->getUserData();

// Get raw data from the request body
$rawData = file_get_contents('php://input');

// Check if raw data is received
if ($rawData) {
    // Decode the raw data from JSON format to associative array
    $userData = json_decode($rawData, true);

    // Check if JSON decoding was successful
    if ($userData) {
        // Extract user ID, task description, and task due date from decoded data
        $userID = $userDataSession['userID'];
        $taskDesc = $userData['taskDesc'];
        $taskDue = $userData['taskDueDate'];

        // Create a task using the extracted data
        $result = $operations->createTask($userID, $taskDue, $taskDesc);

        // Check if task creation was successful and output appropriate message
        if ($result) {
            echo "Task created successfully";
        } else {
            echo "Task creation failed";
        }
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
