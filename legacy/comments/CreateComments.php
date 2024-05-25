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
        $userFirstName = $userDataSession['firstname'];
        $userLastName = $userDataSession['lastname'];
        $userProfilePicture = $userDataSession['profile_photo'];
        $artwork_mother_id = $userData['artwork_mother_id'];
        $comment = $userData['comment'];

        //insertComments($currentUser_id, $artwork_mother_id,  $comment, $commenter_firstName, $commenter_lastName, $commenter_profilePicture){
        $result = $operations->insertComments($userID, 
                                              $artwork_mother_id, 
                                              $comment, 
                                              $userFirstName, 
                                              $userLastName, 
                                              $userProfilePicture);
        // Check the selected task type
        if (!$result) {
            // Echo error
            echo "Something went wrong, comment didnt save";
            
        }else {
            echo "Comment created";
        }
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
