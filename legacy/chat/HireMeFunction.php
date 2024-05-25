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
        $chatMate = $userData['chatMate'];
        $convoExist = $operations->checkConvoDuplication($currentUserID, $chatMate);

        if ($convoExist) {
            $convoInfo = $operations->getConvoInfo($currentUserID, $chatMate);
            $convoID = $convoInfo['convo_id'];

            if ($convoID) {
                $operations->updateRecentReadtime($convoID);

                echo "true";
                
            }else {
                echo "Something went wrong creation of convo failed.";
            }

        }else {
            $result = $operations->hireMeFunction($currentUserID,$chatMate);
            
            if ($result) {

                echo "true";
                

            }else {

                echo "Something went wrong creation of convo failed.";
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
