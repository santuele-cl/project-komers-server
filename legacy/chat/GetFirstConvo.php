<?php
// Include the file for all database-related operations
require_once('../DBOperations.php');

// Retrieve user data from the session
$userDataSession = $operations->getUserData();

    $currentUserID = $userDataSession['userID'];
    $convoHead = $operations->getFirstConvo($currentUserID);
    if ($convoHead) {
        $convoID = $convoHead['convo_id'];
        $chatMateID = '';
        if ($currentUserID == $convoHead['sender_id']) {
            $chatMateID = $convoHead['receiver_id'];

        }else {

            $chatMateID = $convoHead['sender_id'];
        }
        
        echo $convoID.",".$chatMateID;

    }else{

        echo "Start messaging each other!";
    }
        



