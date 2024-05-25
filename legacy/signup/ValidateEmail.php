<?php

    //file for all database related operation
    require_once('../DBOperations.php');

    // Decode the json data into PHP associative array
    $rawData = file_get_contents('php://input');
    
    // Check if any data is received
    if ($rawData) {

        // Decode JSON data into PHP associative array
        $userData = json_decode($rawData, true);
    
        // Validation if the data was successfully decoded
        if ($userData) {
            // Retrieve the email from the JSON data
            $userEmail = $userData['email'];
            // Store the return value of verifyEmail function from DBOperations
            $isEmailTaken = $operations->verifyEmail($userEmail);

            // Check if the email exists in the database
            if (!empty($isEmailTaken)) {
                // Email is already taken
                echo "true";

            } else {
                // Email is not taken
                echo "false";
            }
        
        } else {
            // Error message if the $userData was not extracted
            echo "Error decoding JSON data!" . json_last_error_msg();
        }
    
    } else {
        // No data received
        echo "No data received!";
    }
?>
