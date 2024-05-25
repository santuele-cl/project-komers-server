<?php
    // Include the file for all database-related operations
    require_once('../DBOperations.php');

    // Retrieve user data from the session
    $userDataSession = $operations->getUserData();

    // Extract user ID from the session data
    $userID = $userDataSession['userID'];
    

    //insertComments($currentUser_id, $artwork_mother_id,  $comment, $commenter_firstName, $commenter_lastName, $commenter_profilePicture){
    $result = $operations->getAllConvo($userID);
    // Check the selected task type
    if (!$result) {
        // Echo error
        echo "Start messaging other people";

    }else{

        echo $result;
    }
    

