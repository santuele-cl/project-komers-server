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
        
        $userID = $userDataSession['userID'];
        $userBio = $userData['userBio'];
        $selectedSkills = $userData['sSkill'];
        $selectedTag = $userData['sTag'];

        $result1 = $operations->deleteUserSkills($userID);
        if ($result1) {

            $result2 = $operations->deleteUserTags($userID);

            if ($result2) {
                foreach($selectedSkills as $skill){
                    $result3 = $operations->setUserSkills($userID, $skill);

                    if (!$result3) {
                       echo "Failed to insert the skills.";
                    }
                }

                foreach($selectedTag as $tag){
                    $result4 = $operations->insertTags($userID, $tag);

                    if (!$result4) {
                       echo "Failed to insert the skills.";
                    }
                }

                $operations->updateUserBioProfile($userID, $userBio);

            }
        }
        
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
