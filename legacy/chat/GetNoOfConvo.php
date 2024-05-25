<?php
    // Include the file for all database-related operations
    require_once('../DBOperations.php');

    // Retrieve user data from the session
    $userDataSession = $operations->getUserData();


    $currentUserID = $userDataSession['userID'];

    //insertComments($currentUser_id, $artwork_mother_id,  $comment, $commenter_firstName, $commenter_lastName, $commenter_profilePicture){
    $result = $operations->getNumberOfConvo($currentUserID);
    // Check the selected task type
    if (!$result) {
        // Echo error
        echo "Something went wrong, unable to retrieve comments";

    }else{

        echo $result;
    }
  

