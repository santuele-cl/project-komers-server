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
        $receiver_id = $userData['receiver_id'];
       
        // Extract user ID from the session data
        $sender_id = $userDataSession['userID'];
        $recent_read_status = "Unread";
            
        if ($sender_id != '' && $receiver_id != '') {
            $validateDuplicate = $operations->checkConvoDuplication($sender_id, $receiver_id);

            if (!$validateDuplicate) {
                $result = $operations->createConversation($sender_id,$receiver_id,$recent_read_status);
    
                if (!$result) {
                    // Echo error
                    echo "Something went wrong, comment didnt save";
                    
                }else {
                    echo "Convo created";
                }
            }else {
                echo "Duplicate Exist, proceed to chat.";
            }
        }
        

        
        // Check the selected task type
        
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
