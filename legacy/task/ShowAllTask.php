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
        // Extract user ID from the session data
        $userID = $userDataSession['userID'];

        // Check the selected task type
        if ($userData['tasksSelected'] == "all") {
            // Get all tasks for the user
            $result = $operations->getAllTasks($userID);

            // Output the result or a message if no tasks are found
            if ($result) {
                echo $result;
            } else {
                echo "No Task Created";
            }
        } else if ($userData['tasksSelected'] == "In Progress" || $userData['tasksSelected'] == "Done") {
            // Get pending or completed tasks based on the selected task type
            $result = $operations->getAllPendingAndCompleteTasks($userID, $userData['tasksSelected']);

            // Output the result or a message if no tasks are found
            if ($result) {
                echo $result;
            } else {
                echo "No Task ".$userData['tasksSelected'];
            }
        } else {
            // Output an error message if the selected task type is incorrect
            echo "Something went wrong, selected task is incorrect: " . $userData['tasksSelected'];
        }
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
