<?php

// Include the file for all database-related operations
require_once('../DBOperations.php');
$operations = new MySite();

// Retrieve user data from the session
$userDataSession = $operations->getUserData();

$targetDir = "../../images/coverPicture/";// Change this to your desired upload directory
$uploadOk = 1;


if(isset($_FILES["banner-default-btn"]["tmp_name"])){
    $file_name = $_FILES["banner-default-btn"]["name"];
    $file_size = $_FILES["banner-default-btn"]["size"];
    $file_tmp = $_FILES["banner-default-btn"]["tmp_name"];
    $file_type = $_FILES["banner-default-btn"]["type"];
    
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
            $currentUser = $userDataSession['userID'];

           $result =  $operations->updateCoverPicture($currentUser, $newFileName);
            
            if ($result) {
                echo "true";
            }else {
                echo "false";
            }
            
        } else {
            echo "Error uploading file $file_name.<br>";
        }
    }
}else {
    echo "No image Selected";
}

