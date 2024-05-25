<?php

    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    //file for all database related operation
    require_once('../DBOperations.php');

    $userData = $operations->getUserData();
    //required files for sending email
    require '../phpmailer/src/Exception.php';
    require '../phpmailer/src/PHPMailer.php';
    require '../phpmailer/src/SMTP.php';


    $otp = sprintf("%06d", rand(0, 999999));
    $otp_type = "Registration";
    
    //when expecting a single row result only
    $otp_email = $userData['email'];

    $operations->deleteOTP($otp_email);
    if ($operations) {

        $otpResult = $operations->generateOTP($otp_email, $otp, $otp_type);

    } else {

        $otpResult = $operations->generateOTP($otp_email, $otp, $otp_type);

    }

    //for sending OTP to the user but we will delete the existing OTP first before sending another one
    if ($otpResult) {
        $emailBody = emailBody($otp);
        $subject = 'OTP VERIFICATION';
        sendEmail($subject, $emailBody, $otp_email);
    } else {
        echo "Something went wrong, Generating OTP failed";
    }

    //Function for sending email
    function sendEmail($subject, $body, $otp_email)
    {

        try {
            $mail = new PHPMailer(true);
        
            //Server settings
            $mail->isSMTP();                              //Send using SMTP
            $mail->Host = 'smtp.gmail.com';               //Set the SMTP server to send through
            $mail->SMTPAuth = true;                        //Enable SMTP authentication
            $mail->Username = 'makiartfinds@gmail.com'; //SMTP username
            $mail->Password = 'nfmukhjazbklcrpf';         //SMTP password
            $mail->SMTPSecure = 'ssl';                     //Enable implicit TLS encryption
            $mail->Port = 465;
        
            //Recipients
            $mail->setFrom('makiartfinds@gmail.com', 'ARTFINDS'); //set from
            $mail->addAddress($otp_email);     //Recipient's email address
            $mail->isHTML(true);                           //Set email format to HTML
            $mail->Subject = $subject;                     //Email subject
            $mail->Body = $body;                           //Email body
        
            // Send the email
            $mail->send();
        
            // Success message
            echo 'Success';
        } catch (Exception $e) {
            // Error message
            echo "Error sending OTP: {$mail->ErrorInfo}";
        }
        

        

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

