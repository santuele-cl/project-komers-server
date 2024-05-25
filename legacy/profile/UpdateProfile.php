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

        $currentUserID = $userDataSession['userID'];
        $userFirstName = $userData['userFirstName'];
        $userLastName = $userData['userLastName'];
        $userBirthdate = $userData['userBirthdate'];
        $userGender = $userData['userGender'];
        
        echo $currentUserID . " " . $userFirstName . " " . $userLastName ." ". $userBirthdate . " " . $userGender;
        //insertComments($currentUser_id, $artwork_mother_id,  $comment, $commenter_firstName, $commenter_lastName, $commenter_profilePicture){
        $operations->updateUserProfile($currentUserID, $userFirstName, $userLastName, $userBirthdate, $userGender);
        // Check the selected task type
        
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
