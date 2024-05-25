<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//file for all database related operation
require_once('../DBOperations.php');

//required files for sending email
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

$rawData = file_get_contents('php://input');
echo $rawData;

//validation if any data is receieved
if ($rawData) {

    // Decode the json data into PHP associative array
    $userData = json_decode($rawData, true);


    //Validation if the data successfully decoded
    if ($userData) {

        //initializing and declaring values to the each varialble
        $email = strtolower($userData['email']);
        $password = $userData['password'];
        $firstName = strtolower($userData['firstName']);
        $lastName = strtolower($userData['lastName']);
        $phoneNumber = strtolower($userData['phoneNumber']);
        $selectedTags = $userData['selectedTag'];
        $user_type = "user";
        $encPassword = md5($password);
        $verificationStatus = "unverified";

        $existingEmail = $operations->verifyEmail($email);
        //verify first if the email already exist or not
        if ($existingEmail !== false) {
            $otp = sprintf("%06d", rand(0, 999999));
            $otp_type = "Registration";
            
            //when expecting a single row result only
            $user = $existingEmail[0];
            $otp_email = $user['email'];

            $operations->deleteOTP($otp_email);
            if ($operations) {

                $otpResult = $operations->generateOTP($email, $otp, $otp_type);

            } else {

                $otpResult = $operations->generateOTP($email, $otp, $otp_type);

            }

            //for sending OTP to the user but we will delete the existing OTP first before sending another one
            if ($otpResult) {
                $emailBody = emailBody($otp);
                $subject = 'OTP VERIFICATION';
                sendEmail($subject, $emailBody, $email);
            } else {
                echo "Something went wrong, Generating OTP failed";
            }

        } else {

            $userCount = $operations->getNumberOfUser();

            // Generate random letters
            $randomLetters = '';
            for ($i = 0; $i < 3; $i++) {
                $randomLetters .= chr(rand(65, 90)); // ASCII codes for uppercase letters (A-Z)
            }

            // Generate random 4-digit number
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Combine random letters and number
            $randomCombination = $randomLetters . $randomNumber;

            //We'll verify first if the generated random number is already taken or not
            do {
                $generatedUserID = $randomCombination . "" . $userCount; //creating random id for user;

                $verifyUserIDResult = $operations->verifyUserID($generatedUserID);

            } while ($verifyUserIDResult != 0);

            //making sure that the UNIQUE ID does not match to any existing on our database before inserting it to the database
            if ($verifyUserIDResult == 0) {

                //Inserting data to the database
                $registrationResult = $operations->registerUser(
                    $generatedUserID,
                    $user_type,
                    $encPassword,
                    $email,
                    $phoneNumber,
                    $firstName,
                    $lastName,
                    $verificationStatus
                );

            }

            // If selectedTags is an array, you can iterate over it
            foreach ($selectedTags as $tag) {
                
                $result = $operations->insertTags($generatedUserID,$tag);

                if (!$result) {
                    echo "Failed to insert tag: ". $tag;
                }

            }

        
            //check if the registration is complete and successfull before proceeding to generating OTP
            if ($registrationResult) {
                $otp = sprintf("%06d", rand(0, 999999));
                $otp_status = "Registration";
                $otp_email = $email;

                $operations->deleteOTP($otp_email);
                if ($operations) {

                    $otpResult = $operations->generateOTP($otp_email, $otp, $otp_status);

                } else {

                    $otpResult = $operations->generateOTP($otp_email, $otp, $otp_status);

                }
                //for sending OTP to the user but we will delete the existing OTP first before sending another one
                if ($otpResult) {
                    $emailBody = emailBody($otp);
                    $subject = 'OTP VERIFICATION';

                    sendEmail($subject, $emailBody, $email);
                } else {
                    echo "Something went wrong, generating OTP failed";
                }
            } else {
                echo "Something went wrong, registation failed";
            }
        }
    } else {
        //Error message if the $userData did not extracted
        echo "Error decoding JSON data!" . json_last_error_msg();
    }
} else {
    echo "No data received!";
}


//Function for sending email
function sendEmail($subject, $body, $email)
{

    $mail = new PHPMailer(true);

    //Server settings
    $mail->isSMTP();                              //Send using SMTP
    $mail->Host = 'smtp.gmail.com';       //Set the SMTP server to send through
    $mail->SMTPAuth = true;             //Enable SMTP authentication
    $mail->Username = 'makiartfinds@gmail.com';   //SMTP write your email
    $mail->Password = 'nfmukhjazbklcrpf';      //SMTP password
    $mail->SMTPSecure = 'ssl';            //Enable implicit SSL encryption
    $mail->Port = 465;

    //Recipients
    $mail->setFrom('makiartfinds@gmail.com','ARTFINDS'); // Sender Email and name
    $mail->addAddress($email);     //Add a recipient email  
    // $mail->addReplyTo($_POST["email"], $_POST["name"]); // reply to sender email

    //Content
    $mail->isHTML(true);               //Set email format to HTML
    $mail->Subject = $subject;   // email subject headings
    $mail->Body = $body; //email message

    // Success sent message alert
    $mail->send();

}

function emailBody($otp)
{
    return '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>OTP Email</title>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f4f4f4;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
                    <tr>
                        <td align="center" bgcolor="#ffffff" style="padding: 40px 0;">
                            <h2 style="color: #333333; font-family: Arial, sans-serif; font-size: 24px; margin: 0;">Your One-Time Password (OTP)</h2>
                            <p style="color: #666666; font-family: Arial, sans-serif; font-size: 16px; margin-top: 20px;">Please use the following OTP to complete your action:</p>
                            <div style="font-size: 28px; font-weight: bold; color: #007bff; margin-top: 20px;">' . $otp . '</div>
                            <p style="color: #666666; font-family: Arial, sans-serif; font-size: 14px; margin-top: 20px;">This OTP is valid for a single use and should not be shared with anyone.</p>
                            <p style="color: #666666; font-family: Arial, sans-serif; font-size: 14px;">If you did not request this OTP, please disregard this email.</p>
                            <p style="color: #999999; font-family: Arial, sans-serif; font-size: 12px; margin-top: 20px;">This email was sent automatically. Please do not reply.</p>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            ';
}

