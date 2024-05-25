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
        $convo_id = $userData['convo_id'];
        $chatMate = $userData['chatMate'];
        $recent_read_status = "Unread";
        if ($currentUserID != '' && $chatMate != '') {
            $validateDuplicate = $operations->checkConvoDuplication($currentUserID, $chatMate);
            if ($validateDuplicate) {
                //verify there is currently existing conversation 
            
                $result = $operations->openConversation($convo_id, $chatMate, $currentUserID);

                if ($result) {
                    echo $result;

                }else {
                    echo "Start messaging each other!";
                }
                    
                
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
