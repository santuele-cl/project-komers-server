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
    // Decode the raw data from JSON format to associative array
    $userData = json_decode($rawData, true);

    // Check if JSON decoding was successful
    if ($userData) {
    } else {
        // Output error message if JSON decoding failed
        echo "Error decoding JSON data: " . json_last_error_msg();
    }
} else {
    // Handle case where no raw data is received
    // This could be a normal form submission without JavaScript

    $artWorkCreatorID = $userDataSession['userID'];
    $creatorPicture = $userDataSession['profile_photo'];
    $creator_firstName = $userDataSession['firstname'];
    $creator_lastName = $userDataSession['lastname'];
    $priceValue = $_POST['projectPrice'];
    $currency = $_POST['priceCurrency'];
    $price = $currency . $priceValue;
    $caption = $_POST['artworkCaption'];
    $artwork_status =  $_POST['artwork_status'];
    $selectedTags = $_POST["selectedTag"];
    $postCount = $operations->getNumberOfArtworks();

    // Generate random letters
    $randomLetters = '';
    for ($i = 0; $i < 3; $i++) {
        $randomLetters .= chr(rand(65, 90)); // ASCII codes for uppercase letters (A-Z)
    }

    // Generate random 4-digit number
    $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    // Combine random letters and number
    $randomCombination = $randomLetters . $randomNumber;

    //We'll verify first if the generated random number for artwork is already taken or not
    do {
        $generated_artwork_unique_id = $randomCombination . "" . $postCount; //creating random id for user;

        $verifyArtwork_ID = $operations->verifyUserID($generated_artwork_unique_id);
    } while ($verifyArtwork_ID != 0);

    if ($verifyArtwork_ID == 0) {
        $artWorkCreation = $operations->createArtwork(
            $generated_artwork_unique_id,
            $artWorkCreatorID,
            $caption,
            $selectedTags,
            $creator_firstName,
            $creator_lastName,
            $creatorPicture,
            $artwork_status,
            $price

        );
    }

    if ($artWorkCreation) {
        // Process other form data
        uploadImages($generated_artwork_unique_id, $artWorkCreatorID);
    } else {
        echo "Something went wrong, creating artwork failed";
    }
}


//Function for uploading images of an artwork
function uploadImages($artwork_mother_id, $artWorkCreatorID)
{
    echo "Uploading artwork";
    global $operations;

    $targetDir = "../../images/artworks/"; // Change this to your desired upload directory
    $uploadOk = 1;

    // Loop through each uploaded file
    foreach ($_FILES["fileToUpload"]["tmp_name"] as $key => $tmp_name) {
        $file_name = $_FILES["fileToUpload"]["name"][$key];
        $file_size = $_FILES["fileToUpload"]["size"][$key];
        $file_tmp = $_FILES["fileToUpload"]["tmp_name"][$key];
        $file_type = $_FILES["fileToUpload"]["type"][$key];

        // Check if file is an actual image
        $check = getimagesize($file_tmp);
        if ($check === false) {
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
