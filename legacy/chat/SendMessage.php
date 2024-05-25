<?php
// Include the file for all database-related operations
require_once('../DBOperations.php');

// Retrieve user data from the session
$userDataSession = $operations->getUserData();

// Check if raw data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract user ID from the session data
    $sender_id = $userDataSession['userID'];
    $convo_id = $_POST['convoID'];
    $receiver_id = $_POST['receiver_id'];
    $message_content = $_POST['message_content'];
    $sender_read_status = "Read";
    $receiver_read_status = "Unread";

    
    // Insert message into the database
    $result = $operations->sendMessage($convo_id, $sender_id, $receiver_id, $message_content, $sender_read_status, $receiver_read_status);
    echo $result;
    // Check the result of the operation
    if ($result) {
        // Check if image files are uploaded
        if (isset($_FILES['imagesMessage'])) {
            uploadImages($convo_id, $sender_id, $receiver_id, $sender_read_status, $receiver_read_status);
        }else {
            echo "No image found";
        }

    } else {
        echo "Something went wrong, message didn't send";
    }
} else {
    // Output message if no data is received
    echo "No data received!";
}

function uploadImages($convo_id, $sender_id, $receiver_id, $sender_read_status, $receiver_read_status){
    echo "tanginaaaa";
    global $operations;
    
    $targetDir = "../../images/messagePicture/";// Change this to your desired upload directory
    $uploadOk = 1;
    
    // Loop through each uploaded file
    foreach($_FILES["imagesMessage"]["tmp_name"] as $key => $tmp_name) {
        $file_name = $_FILES["imagesMessage"]["name"][$key];
        $file_size = $_FILES["imagesMessage"]["size"][$key];
        $file_tmp = $_FILES["imagesMessage"]["tmp_name"][$key];
        $file_type = $_FILES["imagesMessage"]["type"][$key];
        
        // Check if file is an actual image
        $check = getimagesize($file_tmp);
        if($check === false) {
            echo "File $file_name is not an image.<br>";
            $uploadOk = 0;
        }
        
        // Check if file already exists
        if (file_exists($targetDir . $file_name)) {
            echo "File $file_name already exists.<br>";
            $uploadOk = 0;
        }
        
        // Check file size
        if ($file_size > 50000000) {
            echo "File $file_name is too large.<br>";
            $uploadOk = 0;
        }
        
        // Allow only certain file formats
        $allowedExtensions = array("jpg", "jpeg", "png");
        $fileExtension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            echo "File $file_name is not allowed. Only JPG, JPEG, PNG, and GIF files are allowed.<br>";
            $uploadOk = 0;
        }
        
        // If everything is ok, try to upload file
        if ($uploadOk == 1) {
            $time = time();
            $newFileName = $file_name . $time . uniqid() . '.' . $fileExtension; // Generate a unique file name
            $targetFile = $targetDir . $newFileName;
            if (move_uploaded_file($file_tmp, $targetFile)) {

                $result = $operations->sendMessage($convo_id, $sender_id, $receiver_id, $newFileName, $sender_read_status, $receiver_read_status);
                
                if ($result) {
                    echo "File $file_name has been uploaded successfully.\n";
                }
                
            } else {
                echo "Error uploading file $file_name.<br>";
            }
        }
    }
}
