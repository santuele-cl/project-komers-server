<?php

// Include the file for all database-related operations
require_once('../DBOperations.php');

// Get raw data from the request body
$rawData = file_get_contents('php://input');

if (isset($_SESSION['userdata'])) {
   // Retrieve user data from the session
    $userDataSession = $operations->getUserData(); 
    
    // Check if raw data is received
    if ($rawData) {
        // Decode the raw data from JSON format to associative array
        $userData = json_decode($rawData, true);

        // Check if JSON decoding was successful
        if ($userData) {
            // Extract OTP and email from the decoded data
            $otpValue = $userData['otp'];
            $email = $userDataSession['email'];

            // Validate the OTP using the provided values
            $result = $operations->validateOTP($otpValue, $email);

            // Check if OTP validation was successful
            if ($result > 0) {
                // Delete OTP from the database
                $operations->deleteOTP($email);

                // Update the verification status of the user to "Verified"
                $status = "Verified";
                $operations->updateVerificationStatus($status, $email);

                // Output "true" if OTP validation and status update were successful
                echo "true";
            } else {
                // Output "false" if OTP validation failed
                echo "false";
            }
        } else {
            // Output error message if JSON decoding failed
            echo "Error decoding JSON data: " . json_last_error_msg();
        }
    } else {
        // Output message if no data is received
        echo "No data received!";
    }
    

}else {

        // Check if raw data is received
    if ($rawData) {
        // Decode the raw data from JSON format to associative array
        $userData = json_decode($rawData, true);

        // Check if JSON decoding was successful
        if ($userData) {
            // Extract OTP and email from the decoded data
            $otpValue = $userData['otp'];
            $email = $userData['email'];

            // Validate the OTP using the provided values
            $result = $operations->validateOTP($otpValue, $email);

            // Check if OTP validation was successful
            if ($result > 0) {
                // Delete OTP from the database
                $operations->deleteOTP($email);

                // Update the verification status of the user to "Verified"
                $status = "Verified";
                $operations->updateVerificationStatus($status, $email);

                // Output "true" if OTP validation and status update were successful
                echo "true";
            } else {
                // Output "false" if OTP validation failed
                echo "false";
            }
        } else {
            // Output error message if JSON decoding failed
            echo "Error decoding JSON data: " . json_last_error_msg();
        }
    } else {
        // Output message if no data is received
        echo "No data received!";
    }

}





