<?php

// Include the file for all database-related operations
require_once('../DBOperations.php');
$operations = new MySite();

// Retrieve user data from the session
$userDataSession = $operations->getUserData();

// Capture raw data from the request
$rawData = file_get_contents('php://input');

// Check if raw data is received
if ($rawData) {
    
} else {
    // Handle case where no raw data is received
    // This could be a normal form submission without JavaScript

        $caption = $_POST['editProjectDesc'];
        $artwork_status = 'Posting';
        $artwork_tags = $_POST['selectedTag']; 
        $artwork_id = $_POST['artwork_unique_id']; 
        $currentUser = $userDataSession["userID"];

        $deleteImagesResult = deleteImages($artwork_id);
        if ($deleteImagesResult) {
            
            $results = $operations->updateArtWork($artwork_id, $caption, $artwork_tags);
            if ($results) {

                uploadImages($artwork_id, $currentUser);

            }
            
            

        }else {
            
        }
}

function deleteImages($artwork_mother_id){
    global $operations;
    $result = $operations->deleteArtWorkImages($artwork_mother_id);

    return $result;

}

//Function for uploading images of an artwork
function uploadImages($artwork_mother_id, $artWorkCreatorID){
 
    global $operations;
    
    $targetDir = "../../images/artworks/";// Change this to your desired upload directory
    $uploadOk = 1;
    
    // Loop through each uploaded file
    foreach($_FILES["editFileToUpload"]["tmp_name"] as $key => $tmp_name) {
        $file_name = $_FILES["editFileToUpload"]["name"][$key];
        $file_size = $_FILES["editFileToUpload"]["size"][$key];
        $file_tmp = $_FILES["editFileToUpload"]["tmp_name"][$key];
        $file_type = $_FILES["editFileToUpload"]["type"][$key];
        
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

                $result = $operations->uploadImages($artWorkCreatorID, $newFileName, $artwork_mother_id);

                if ($result) {
                    echo "File $file_name has been uploaded successfully.\n";
                }
                
                
            } else {
                echo "Error uploading file $file_name.<br>";
            }
        }
    }
}


