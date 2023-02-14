<?php
require_once('config.php');
global $documentRootPath, $frontEndProtocol, $frontEndPoint, $topPath;

if (isset($_GET["email"])) {
  $to=$_GET["email"];
}
else if (isset($_POST["email"])) {
  $to=$_POST["email"];
}
if (isset($to)) {
  if (strpos($to, ';') !== false) {
      $to = explode(";",$to);
  } else if (strpos($to, ',') !== false) {
    $to = explode(",",$to);
  } else if (strpos($to, ' ') !== false) {
    $to = explode(" ",$to);
  }
}
if (isset($_GET["subject1"]) || isset($_GET["subject2"])) {
  $subject=$_GET["subject1"]." ".$_GET["subject2"];
}
else if (isset($_POST["subject1"]) || isset($_POST["subject2"])) {
  $subject=$_POST["subject1"]." ".$_POST["subject2"];
}
if (isset($_GET["message_header"])) {
  $message_header=$_GET["message_header"];
}
else if (isset($_POST["message_header"])) {
  $message_header=$_POST["message_header"];
}
if (isset($_GET["message_description"])) {
  $message_description=$_GET["message_description"];
}
else if (isset($_POST["message_description"])) {
  $message_description=$_POST["message_description"];
}
if (isset($_GET["message_attachment"])) {
  $message_attachment=$_GET["message_attachment"];
}
else if (isset($_POST["message_attachment"])) {
  $message_attachment=$_POST["message_attachment"];
}
if (isset($_GET["attachment_path"])) {
  $attachment_path=$_GET["attachment_path"];
}
else if (isset($_POST["attachment_path"])) {
  $attachment_path=$_POST["attachment_path"];
}
if (isset($_GET["message_html"])) {
  $message_html=$_GET["message_html"];
}
else if (isset($_POST["message_html"])) {
  $message_html=$_POST["message_html"];
}
if (isset($_GET["html_header"])) {
  $html_header=$_GET["html_header"];
}
else if (isset($_POST["html_header"])) {
  $html_header=$_POST["html_header"];
}

if (file_exists("$documentRootPath/PHPMailer/PHPMailer/PHPMailer.php")) {
  // Import PHPMailer classes into the global namespace
  // These must be at the top of your script, not inside a function
  require("$documentRootPath/PHPMailer/PHPMailer/PHPMailer.php");
  require("$documentRootPath/PHPMailer/PHPMailer/SMTP.php");
  require("$documentRootPath/PHPMailer/PHPMailer/Exception.php");

  $mail = new PHPMailer\PHPMailer\PHPMailer(true);            // Passing `true` enables exceptions

  try {
      //Server settings
      $mainUser = $json->{'mail'};
      $mail->CharSet = $mainUser->{'CharSet'};
      $mail->SMTPDebug = $mainUser->{'SMTPDebug'};          // Enable verbose debug output
      $mail->isSMTP();                                      // Set mailer to use SMTP
      // -----------
      $mail->SMTPAuth = $mainUser->{'SMTPAuth'};            // Enable SMTP authentication
      $mail->Host = $mainUser->{'Host'};                    // Specify main and backup SMTP servers
      $mail->Password = $mainUser->{'Password'};            // SMTP password
      $mail->Username = $mainUser->{'Username'};            // SMTP username
      $mail->SMTPSecure = $mainUser->{'SMTPSecure'};        // Enable TLS encryption, `ssl` also accepted
      $mail->Port = $mainUser->{'Port'};                    // TCP port to connect to
      // -----------

      // To load the Korean version
      $mail->setLanguage('ko', $documentRootPath .'/PHPMailer/language');

      //Recipients
      $mail->setFrom(L::mail_from, L::mail_from_name);
      if (isset($to)) {
        if (is_array($to)) {
          foreach ($to as $recipient) {
            if ($recipient == L::mail_to) {
              $mail->addAddress($recipient, L::mail_to_name);     // Add a recipient
            }
            else {
              $mail->addAddress($recipient);               // Name is optional
            }
          }
        }
        else {
          $mail->addAddress($to);               // Name is optional
        }
      }
      else if (count(explode('|', L::mail_to)) > 0) {
        $r_emails = explode('|', L::mail_to);
        $r_name = explode('|', L::mail_to_name);
        for($i=0; $i<count($r_emails); $i++) {
          $mail->addAddress($r_emails[$i], $r_name[$i]);
        }
      }
      else {
        $reci_email = trim('|', L::mail_to);
        $reci_name = trim('|', L::mail_to_name);
        $mail->addAddress($reci_email, $reci_name);
      }
      $mail->addReplyTo(L::mail_reply_to, L::copywrite_company . L::mail_reply_to_name);

      if (count(explode('|', L::mail_cc)) > 0) {
        $r_emails = explode('|', L::mail_cc);
        $r_name = explode('|', L::mail_cc_name);
        for($i=0; $i<count($r_emails); $i++) {
          $mail->addCC($r_emails[$i], $r_name[$i]);
        }
      } else {
        $mail->addCC(L::company_email, L::company_name .' '. L::company_team);
      }

      //Attachments
      if (isset($attachment_path)) {
          if (strpos($attachment_path, ';') !== false) {
              $attachment_path = explode(";",$attachment_path);
          }
          if (is_array($attachment_path)) {
              foreach ($attachment_path as $file_path) {
                  $mail->addAttachment($file_path, basename($file_path));    // Optional name
              }
          }
          else {
              $mail->addAttachment($attachment_path, basename($attachment_path));    // Optional name
          }
      }

      // Site user
      $siteUser = $json->{'users'}->{'app'}->{'userId'};
      $sitePass = $json->{'users'}->{'app'}->{'password'};

      //Content
      $mail->isHTML(true);                                  // Set email format to HTML
      if (isset($subject)) {
        $mail->Subject = $subject;
      } else {
        $mail->Subject = '['. L::title_h2_client .'] '.date("Y.m.d");
      }
      if (isset($message)) {
        $mail->Body    = $message;
        $mail->AltBody = $message;
      } else {
        $mail->Body    = '<HTML>';
        $mail->Body   .= isset($html_header) ? $html_header : "";
        $mail->Body   .= '<BODY>';
        $mail->Body   .= '<br />안녕하세요.<br />';
        $mail->Body   .= '<div>'.$message_header.'</div><br />';
        $mail->Body   .= '<H2><b>설치 및 다운로드 URL</b></H2>';
        $mail->Body   .= '<div style="background: ghostwhite; font-size: 12px; padding: 10px; border: 1px solid lightgray; margin: 10px;">';
        $mail->Body   .= '<a href='. L::client_short_url .'>'. L::client_short_url .'</a>&nbsp;(ID/PW: '. $siteUser .'/'. $sitePass .')';
        $mail->Body   .= '</div>';
        $mail->Body   .= '※'. L::description_notice18;
        $mail->Body   .= '<br /><br />';
        $mail->Body   .= '<H2><b>수정 및 반영사항</b></H2>';
        $mail->Body   .= '항목:&nbsp;<br />';
        $mail->Body   .= '<div>'. isset($message_html) ? $message_html : "" .'</div>';
        $mail->Body   .= '<br /><br /><br />';
        $mail->Body   .= '<H2><b>빌드 환경</b></H2><br />';
        $mail->Body   .= $message_description;
        $mail->Body   .= '<br /><br />';
        if (isset($message_attachment)) {
          $mail->Body   .= '<H2><b>첨부파일:</b></H2>';
          $mail->Body   .= '<div><pre>'.$message_attachment.'</pre></div>';
          $mail->Body   .= '<br />';
        }
        $mail->Body   .= '<br /><br />';
        $mail->Body   .= '감사합니다.<br /><br />';
        $mail->Body   .= 'App Development Team<br />';
        $mail->Body   .= L::copywrite_company .'</BODY></HTML>';

        // This is the body in plain text for non-HTML mail clients
        $mail->AltBody  = '안녕하세요.\n\n'.$message_header.'\n\n';
        $mail->AltBody .= '설치 및 다운로드 URL: '. L::client_short_url .' (ID/PW: '. $siteUser .'/'. $sitePass .')\n\n';
        $mail->AltBody .= '수정 및 반영사항\n항목:\n'.$message_description.'\n\n';
        if (isset($message_attachment)) {
          $mail->AltBody .= '첨부파일: '.$message_attachment.'\n\n';
        }
        $mail->AltBody .= '\n\n';
        $mail->AltBody .= '감사합니다.\n\n';
        $mail->AltBody .= 'App Development Team\n';
        $mail->AltBody .= L::copywrite_company .'\n';
      }

      $mail->send();
      echo 'Message has been sent';
  } catch (Exception $e) {
      $content = "Message could not be sent. Mailer Error: ". $mail->ErrorInfo;
      $errFile = __DIR__ . "/sendmail.log";
      file_put_contents($errFile, $content);
  }
} else {
  echo "No PHPMailer installed";
  echo "Get PHPMailer from https://github.com/PHPMailer/PHPMailer";
}
?>
