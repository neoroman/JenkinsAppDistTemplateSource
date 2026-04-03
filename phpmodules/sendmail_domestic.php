<?php
if (!class_exists('i18n')) {
  if (file_exists(__DIR__ .'/../config.php')) {
      require_once(__DIR__ . '/../config.php');
  }
  else if (file_exists(__DIR__ .'/../../config.php')) {
      require_once(__DIR__ . '/../../config.php');
  }
}
global $documentRootPath, $frontEndProtocol, $frontEndPoint, $topPath;
global $isDebugMode;

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
      if (isset($to_domestic)) {
        if (is_array($to_domestic)) {
          foreach ($to_domestic as $recipient) {
            if ($recipient == L::mail_to_domestic) {
              $mail->addAddress($recipient, L::mail_to_domestic_name);     // Add a recipient
            }
            else {
              $mail->addAddress($recipient);               // Name is optional
            }
          }
        }
        else {
          $mail->addAddress($to_domestic);               // Name is optional
        }
      }
      else if (isset($to)) {
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
      else if ($isDebugMode && L::mail_debug_to) {
        $mail->addAddress(L::mail_debug_to, L::mail_debug_to_name);
      }   
      else if (count(explode('|', L::mail_reply_to)) > 0) {
        $r_emails = explode('|', L::mail_reply_to);
        $r_name = explode('|', L::mail_reply_to_name);
        for($i=0; $i<count($r_emails); $i++) {
          $mail->addAddress($r_emails[$i], $r_name[$i]);
        }
      } else {
        $mail->addAddress(L::company_email, L::company_name .' '. L::company_team);
      }

      $mail->addReplyTo(L::mail_reply_to, L::copywrite_company . L::mail_reply_to_name);


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
      $siteUser = $json->{'users'}->{'qc'}->{'userId'};
      $sitePass = $json->{'users'}->{'qc'}->{'password'};

      //Content
      $mail->isHTML(true);                                  // Set email format to HTML
      if (isset($subject)) {
        $mail->Subject = $subject;
      } else {
        $mail->Subject = '[앱배포] 내부 테스트 앱 배포: '.date("Y.m.d");
      }
      $domestic_url = "$frontEndProtocol://$frontEndPoint/$topPath/dist_domestic.php";
      if (isset($message)) {
        $mail->Body    = $message;
        $mail->AltBody = $message;
      } else {
        // Table-based layout + inline styles (same pattern as sendmail_release.php); header #4587D8
        $mh = isset($message_html) ? $message_html : '';
        $urlEsc = htmlspecialchars((string) $domestic_url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $userEsc = htmlspecialchars((string) $siteUser, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $passEsc = htmlspecialchars((string) $sitePass, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $attachEsc = isset($message_attachment) ? htmlspecialchars((string) $message_attachment, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';

        $mail->Body = '<!DOCTYPE html><html lang="ko"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>내부 테스트 앱 배포</title></head>';
        $mail->Body .= '<body style="margin:0;padding:0;background-color:#eef2f7;">';
        $mail->Body .= '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#eef2f7;padding:20px 12px;">';
        $mail->Body .= '<tr><td align="center">';
        $mail->Body .= '<table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;border:1px solid #dbe0e8;overflow:hidden;">';

        $mail->Body .= '<tr><td style="background:#4587D8;padding:22px 26px;color:#ffffff;">';
        $mail->Body .= '<div style="font-size:18px;font-weight:700;letter-spacing:-0.02em;line-height:1.3;">[앱배포] 내부 테스트 앱 배포</div>';
        $mail->Body .= '<div style="font-size:13px;opacity:0.92;margin-top:6px;">'. htmlspecialchars(date("Y.m.d"), ENT_QUOTES | ENT_HTML5, 'UTF-8') .'</div>';
        $mail->Body .= '</td></tr>';

        if (isset($html_header) && $html_header !== '') {
          $mail->Body .= '<tr><td style="padding:0 26px 8px;">'.$html_header.'</td></tr>';
        }

        $mail->Body .= '<tr><td style="padding:22px 26px 8px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;font-size:15px;line-height:1.55;color:#1f2937;">';
        $mail->Body .= '<p style="margin:0 0 14px;">안녕하세요.</p>';
        $mail->Body .= '<div style="color:#374151;">'.$message_header.'</div>';
        $mail->Body .= '</td></tr>';

        $mail->Body .= '<tr><td style="padding:8px 26px 18px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;">';
        $mail->Body .= '<div style="font-size:14px;font-weight:700;color:#111827;padding-bottom:8px;border-bottom:3px solid #2563eb;display:inline-block;">내부 QA 사이트 URL</div>';
        $mail->Body .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">';
        $mail->Body .= '<tr><td style="padding:16px 18px;font-size:13px;line-height:1.5;">';
        $mail->Body .= '<a href="'. $urlEsc .'" style="color:#2563eb;word-break:break-all;text-decoration:none;border-bottom:1px solid #93c5fd;">'. $urlEsc .'</a>';
        $mail->Body .= '<div style="margin-top:12px;font-size:12px;color:#64748b;">';
        $mail->Body .= 'ID: <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;background:#fff;padding:3px 10px;border-radius:4px;border:1px solid #e2e8f0;color:#0f172a;">'. $userEsc .'</span>';
        $mail->Body .= ' &nbsp; PW: <span style="font-family:ui-monospace,SFMono-Regular,Consolas,monospace;background:#fff;padding:3px 10px;border-radius:4px;border:1px solid #e2e8f0;color:#0f172a;">'. $passEsc .'</span>';
        $mail->Body .= '</div></td></tr></table>';
        $mail->Body .= '<p style="font-size:11px;color:#94a3b8;margin:12px 0 0;line-height:1.45;">※ '. L::description_notice18 .'</p>';
        $mail->Body .= '</td></tr>';

        $mail->Body .= '<tr><td style="padding:8px 26px 18px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;">';
        $mail->Body .= '<div style="font-size:14px;font-weight:700;color:#111827;padding-bottom:8px;border-bottom:3px solid #2563eb;display:inline-block;">수정 및 반영사항</div>';
        $mail->Body .= '<div style="margin-top:14px;color:#374151;font-size:14px;line-height:1.55;">'. $mh .'</div>';
        $mail->Body .= '</td></tr>';

        $mail->Body .= '<tr><td style="padding:8px 26px 18px;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;">';
        $mail->Body .= '<div style="font-size:14px;font-weight:700;color:#111827;padding-bottom:8px;border-bottom:3px solid #2563eb;display:inline-block;">빌드 환경</div>';
        $mail->Body .= '<div style="margin-top:14px;font-size:13px;color:#374151;line-height:1.55;white-space:pre-wrap;">'. $message_description .'</div>';
        $mail->Body .= '</td></tr>';

        if (isset($message_attachment)) {
          $mail->Body .= '<tr><td style="padding:8px 26px 22px;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;">';
          $mail->Body .= '<div style="font-size:14px;font-weight:700;color:#111827;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;padding-bottom:8px;">첨부파일</div>';
          $mail->Body .= '<div style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:8px;padding:14px;font-size:12px;line-height:1.45;color:#0f172a;white-space:pre-wrap;word-break:break-word;">'. $attachEsc .'</div>';
          $mail->Body .= '</td></tr>';
        }

        $mail->Body .= '<tr><td style="padding:22px 26px;background:#f8fafc;border-top:1px solid #e5e7eb;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,\'Helvetica Neue\',Arial,sans-serif;">';
        $mail->Body .= '<p style="margin:0 0 10px;color:#374151;font-size:15px;">감사합니다.</p>';
        $mail->Body .= '<p style="margin:0;font-size:13px;color:#6b7280;line-height:1.5;">App Development Team<br />'. L::copywrite_company .'</p>';
        $mail->Body .= '</td></tr>';

        $mail->Body .= '</table></td></tr></table></body></html>';

        $mail->AltBody = "안녕하세요.\n\n" . strip_tags($message_header) . "\n\n";
        $mail->AltBody .= '내부 QA 사이트 URL: ' . $domestic_url . ' (ID/PW: ' . $siteUser . '/' . $sitePass . ")\n\n";
        $mail->AltBody .= "수정 및 반영사항\n" . strip_tags($mh) . "\n\n";
        $mail->AltBody .= "빌드 환경\n" . $message_description . "\n\n";
        if (isset($message_attachment)) {
          $mail->AltBody .= '첨부파일: ' . $message_attachment . "\n\n";
        }
        $mail->AltBody .= "감사합니다.\n\n";
        $mail->AltBody .= "App Development Team\n";
        $mail->AltBody .= L::copywrite_company . "\n";
      }

      $mail->send();
      echo 'Message has been sent';
  } catch (Exception $e) {
      $content = "Message could not be sent. Mailer Error: ". $mail->ErrorInfo;
      $errFile = __DIR__ . "/../sendmail.log";
      file_put_contents($errFile, $content);
  }
} else {
  echo "No PHPMailer installed";
  echo "Get PHPMailer from https://github.com/PHPMailer/PHPMailer";
}
?>
