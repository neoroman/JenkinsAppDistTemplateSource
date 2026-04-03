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

/**
 * Same table/card shell as sendmail_release / sendmail_domestic; top band #4587D8.
 */
function buildStyledEmailHtml(string $headerTitle, string $headerSubtitle, string $innerContentHtml): string {
    $ht = htmlspecialchars($headerTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $hs = htmlspecialchars($headerSubtitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $footerCo = htmlspecialchars(L::copywrite_company, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $html = '<!DOCTYPE html><html lang="ko"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $ht . '</title></head>';
    $html .= '<body style="margin:0;padding:0;background-color:#eef2f7;">';
    $html .= '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#eef2f7;padding:20px 12px;">';
    $html .= '<tr><td align="center">';
    $html .= '<table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;border:1px solid #dbe0e8;overflow:hidden;">';
    $html .= '<tr><td style="background:#4587D8;padding:22px 26px;color:#ffffff;">';
    $html .= '<div style="font-size:18px;font-weight:700;letter-spacing:-0.02em;line-height:1.3;">' . $ht . '</div>';
    $html .= '<div style="font-size:13px;opacity:0.92;margin-top:6px;">' . $hs . '</div>';
    $html .= '</td></tr>';
    $html .= '<tr><td style="padding:22px 26px 8px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;font-size:15px;line-height:1.55;color:#1f2937;">';
    $html .= $innerContentHtml;
    $html .= '</td></tr>';
    $html .= '<tr><td style="padding:22px 26px;background:#f8fafc;border-top:1px solid #e5e7eb;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;">';
    $html .= '<p style="margin:0 0 10px;color:#374151;font-size:15px;">감사합니다.</p>';
    $html .= '<p style="margin:0;font-size:13px;color:#6b7280;line-height:1.5;">App Development Team<br />' . $footerCo . '</p>';
    $html .= '</td></tr>';
    $html .= '</table></td></tr></table></body></html>';
    return $html;
}

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
        if (!preg_match('/<\s*html[\s>]/i', $body)) {
            $escaped = nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $inner = '<div style="color:#374151;">' . $escaped . '</div>';
            $body = buildStyledEmailHtml($subject, date('Y.m.d'), $inner);
        }
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Plain text version of the email body

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>