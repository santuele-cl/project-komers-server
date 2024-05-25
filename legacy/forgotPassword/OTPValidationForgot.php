<?php

    //file for all database related operation
    require_once('../DBOperations.php');

    // Get raw JSON data from the request
    $rawData = file_get_contents('php://input'); 


    // Decode JSON data into PHP associative array
    $userData = json_decode($rawData, true);

    // Check if JSON data was successfully decoded
    if ($userData) {
        // Retrieve OTP value and email from JSON data
        $otpValue = $userData['otp'];
        $email = $userData['email'];

        // Call the validateOTP function from DBOperations
        $result = $operations->validateOTP($otpValue, $email);

        // Check if OTP validation was successful
        if ($result > 0) {
            // Delete OTP after it's been used
            $operations->deleteOTP($email);

            // Update user's verification status to "Verified"
            $status = "Verified";
            $operations->updateVerificationStatus($status, $email);

            // Respond with "true" indicating successful verification
            echo "true";

        } else {
            // Respond with "false" indicating failed verification
            echo "false";
        }
    } else {
        // Error message if JSON data couldn't be decoded
        echo "Error decoding JSON data!" . json_last_error_msg();
    }

?>
