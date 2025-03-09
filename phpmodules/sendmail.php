<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

if (!class_exists('i18n')) {
    if (file_exists(__DIR__ .'/../config.php')) {
        require_once(__DIR__ . '/../config.php');
    }
    else if (file_exists(__DIR__ .'/../../config.php')) {
        require_once(__DIR__ . '/../../config.php');
    }
}
  
global $documentRootPath, $isDebugMode;

if (file_exists("$documentRootPath/PHPMailer/PHPMailer/PHPMailer.php")) {
    require_once "$documentRootPath/PHPMailer/PHPMailer/PHPMailer.php";
    require_once "$documentRootPath/PHPMailer/PHPMailer/SMTP.php";
    require_once "$documentRootPath/PHPMailer/PHPMailer/Exception.php";
} else {
    die("PHPMailer not found");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $subject, $body, $attachments = []) {
    global $json;

    $mail = new PHPMailer(true); // Passing `true` enables exceptions

    try {
        // Server settings
        $mainUser = $json->{'mail'};
        $mail->CharSet = $mainUser->{'CharSet'};
        $mail->SMTPDebug = $mainUser->{'SMTPDebug'}; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $mainUser->{'Host'}; // Specify main and backup SMTP servers
        $mail->SMTPAuth = $mainUser->{'SMTPAuth'}; // Enable SMTP authentication
        $mail->Username = $mainUser->{'Username'}; // SMTP username
        $mail->Password = $mainUser->{'Password'}; // SMTP password
        $mail->SMTPSecure = $mainUser->{'SMTPSecure'}; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $mainUser->{'Port'}; // TCP port to connect to

        // Recipients
        $mail->setFrom(L::mail_from, L::mail_from_name);
        error_log("Sending email from: " . L::mail_from); // Debugging statement
        if (is_array($to)) {
            foreach ($to as $recipient) {
                $mail->addAddress($recipient); // Add a recipient
            }
        } else {
            $mail->addAddress($to); // Add a recipient
        }

        // Attachments
        foreach ($attachments as $filePath) {
            if (file_exists($filePath)) {
                $mail->addAttachment($filePath); // Add attachments
            }
        }

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Plain text version of the email body

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>