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
        
        $chatMate = $userData['chatMate'];
        $convoID = $userData['convoID'];
        $chatMateInfo = '';
        $result = $operations->getOtherUserInfo($chatMate);
        if ($result) {
            
            $chateMatePhoto = $result['profile_photo'];
            $chatMateFullname = ucfirst($result['first_name']).' '.ucfirst($result['last_name']);
            
            if (!$chateMatePhoto) {
                $chateMatePhoto = 'defaultProfilePicture.png';
            }
            
            $chatMateInfo .= <<<HTML
                                <img src="../images/profilePicture/$chateMatePhoto" alt="avatar" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover"/>
                                    <div>
                                        <input type="hidden" id="chatMateUserID" value="{$chatMate}">
                                        <input type="hidden" id="chatConvoID" value="{$convoID}">
                                        <p class="m-0 fw-bold mb-0">{$chatMateFullname}</p>
                                        <span class="text-muted fs-7" id="active_status">{$result['active_status']}</span>
                                    </div>
                                HTML;
            echo $chatMateInfo;
        }else {
            echo "User does not exist!";
        }

        
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}
