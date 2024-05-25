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
        $expID = $userData['expID'];
        $previous_company = $userData['userJob'];
        $job_title = $userData['userCompany'];
        $start_date = $userData['userStartYear'];
        $end_date = $userData['userEndYear'];

        //insertComments($currentUser_id, $artwork_mother_id,  $comment, $commenter_firstName, $commenter_lastName, $commenter_profilePicture){
        $result = $operations->updateExperience($expID, $previous_company, $job_title, $start_date ,$end_date);
        // Check the selected task type
        if (!$result) {
            // Echo error
            echo "Something went wrong, comment didnt save";
            
        }else {
            echo "Experience Updated";
        }
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
