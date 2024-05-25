<?php
// Include the file for all database-related operations
require_once('../DBOperations.php');

// Retrieve user data from the session
$userDataSession = $operations->getUserData();

    $currentUserID = $userDataSession['userID'];
    $activeStatus = $operations->updateActiveStatus($currentUserID);

    if ($activeStatus) {
        
        echo "Online";

    }else{

        echo "Start messaging each other!";
    }
        



