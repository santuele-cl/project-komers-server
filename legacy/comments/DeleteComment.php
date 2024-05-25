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
        // Extract task details from the decoded data
        $commentID = $userData['deleteCommentID'];

        // Delete the task using the provided details
        $result = $operations->deleteComment($commentID);

        // Output the result of the delete task operation
        if ($result) {
            echo "Task deleted successfully";
        } else {
            echo "Task update failed";
        }
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data: " . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}

?>