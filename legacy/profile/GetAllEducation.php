<?php

    //file for all database related operation
    require_once('../DBOperations.php');

    // Get raw JSON data from the request
    $rawData = file_get_contents('php://input'); 

    // Retrieve user data from session
    $userDataSession = $operations->getUserData();

     // Decode JSON data into PHP associative array
     $userData = json_decode($rawData, true);

     // Check if JSON data was successfully decoded
     if ($userData) {
         // Retrieve OTP value and email from JSON data
        $user = $userData['user'];
         
        $experience = $operations->showAllEducation($user);

        // Check if OTP validation was successful
        if ($experience) {
            
            // Respond with "true" indicating successful verification
            echo $experience;

        } else {
            // Respond with "false" indicating failed verification
            echo "No Education Yet";
        }

     } else {
         // Error message if JSON data couldn't be decoded
         echo "Error decoding JSON data!" . json_last_error_msg();
     }

    
?>
