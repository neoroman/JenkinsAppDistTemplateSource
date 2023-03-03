<?php
session_start();

if (!class_exists('i18n')) {
  if (file_exists(__DIR__ .'/../config.php')) {
      require_once(__DIR__ . '/../config.php');
  }
  else if (file_exists(__DIR__ .'/../../config.php')) {
      require_once(__DIR__ . '/../../config.php');
  }
}
global $topPath, $root;
global $inUrl, $outUrl, $isDebugMode;
global $documentRootPath, $frontEndProtocol, $frontEndPoint;

if (file_exists('../phpmodules/common.php')) {
  require('../phpmodules/common.php');
} else if (file_exists('common.php')) {
  require('common.php');
}

$prevPage = $_SERVER['HTTP_REFERER'];

// Form submitted
if (isset($_POST['recommand'])) {
  if (isset($_POST['receipients_email']) && strlen(trim($_POST['receipients_email'])) > 5) {
    $receipients = explode(',', $_POST['receipients_email']);

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
    
          // To load the French version
          $mail->setLanguage('ko', $documentRootPath .'/PHPMailer/language');
    
          //Recipients
          $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 
          if (isset($_SESSION['email']) && preg_match($regex, $_SESSION['email'])) {
            if (isset($_SESSION['name'])) {
              $mail->setFrom($_SESSION['email'], $_SESSION['name']);
            } else {
              $mail->setFrom($_SESSION['email'], '메일 발송자');
            }
          } else {
            $mail->setFrom(L::mail_from, L::mail_from_name);
          }
          for($i=0; $i<count($receipients); $i++) {
            $mail->addAddress($receipients[$i]);
          }
          $mail->addReplyTo(L::mail_reply_to, L::copywrite_company . L::mail_reply_to_name);
    
          // Site user
          $siteUser = $json->{'users'}->{'app'}->{'userId'};
          $sitePass = $json->{'users'}->{'app'}->{'password'};
    
          //Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = '[' . L::client_title . '] '. L::app_name .' 앱을 소개합니다.';
          $mail->Body    = '<HTML>';
          $mail->BODY   .= '<BODY>';
          $mail->Body   .= '<br />안녕하세요.<br /><br />';
          $mail->Body   .= L::description_mail_recommand;
          $mail->Body   .= '<br />';
          $mail->Body   .= '<H2><b>설치 및 다운로드 URL</b></H2><br />';
          $mail->Body   .= '<a href='. L::client_short_url .'>'. L::client_short_url .'</a>&nbsp;(ID/PW: '. $siteUser .'/'. $sitePass .')';
          $mail->Body   .= '<br /><br />';
          $mail->Body   .= '감사합니다.<br /><br />';
          $mail->Body   .= 'App Development Team<br />';
          $mail->Body   .= L::copywrite_company .'</BODY></HTML>';
  
          // This is the body in plain text for non-HTML mail clients
          $mail->AltBody  = '안녕하세요.\n\n'. L::description_mail_recommand .'\n\n';
          $mail->AltBody .= '설치 및 다운로드 URL: '. L::client_short_url .' (ID/PW: '. $siteUser .'/'. $sitePass .')\n\n';
          $mail->AltBody .= '\n\n';
          $mail->AltBody .= '감사합니다.\n\n';
          $mail->AltBody .= 'App Development Team\n';
          $mail->AltBody .= L::copywrite_company .'\n';
    
          $mail->send();
      } catch (Exception $e) {
        $error_message = "Message could not be sent. Mailer Error: ". $mail->ErrorInfo;
      }
    } else {
      $error_message = "No PHPMailer installed. Get PHPMailer from https://github.com/PHPMailer/PHPMailer";
    }
  } else {
    $error_message = L::error_no_email;
  }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::client_title ." ". L::app_name ?></title>
  <!-- font CSS -->
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css?v2">
  <script type="text/javascript">
  function FormSubmit(oForm) {
      var oHidden = oForm.elements["version"];
      var oDDL = oForm.elements["version_ddl"];
      var oTextbox = oForm.elements["version_txt"];
      if (oHidden && oDDL && oTextbox)
          oHidden.value = (oDDL.value == "") ? oTextbox.value : oDDL.value;
  }
  </script>
</head>

<body>
<!-- wrap -->
<div class="wrap register_wrap qa_type2">
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="javascript:window.history.go(-1);"><?php echo L::client_title ." ". L::app_name ?></a></h1>
      <a href="javascript:window.history.go(-1);" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container">
    <h2 class="stit"><?php echo L::client_title . " " . L::app_name . " " . L::title_send_link; ?></h2>
    <p class="txt"><span class="ico"></span><?php echo L::description_notice13_input_email_for_link; ?></p>

    <form action="#" method="POST" id="recommand" name="recommand" onsubmit="FormSubmit(this);">
      <input type="hidden" name="previous_page" value="<?php $prevPage ?>" />
      <input type="hidden" name="recommand" value="findIt"/>
  		<fieldset class="register_form">
      <legend><?php echo L::company_name . " " . L::app_name . " " . L::title_send_link; ?></legend>
  			<div class="inputs">
  				<label class="txt_label"><?php echo L::title_email; ?><span class="point_c1">(<?php echo L::title_mandatory; ?>)</span></label>
          <input type="text" class="inp_text" name="receipients_email" placeholder="<?php echo L::title_email_example; ?>">

          <label class="txt_label"><?php echo L::title_link_to_send; ?></label>
          <input type="text" class="inp_text" value="<?php echo L::client_short_url; ?>" readonly>
        </div>
  		</fieldset>
    </form>

    <div class="btn_area">
      <a href="#" class="btn_send" onclick="javascsript:document.getElementById('recommand').submit()">
      <?php echo L::button_send_link; ?>
      </a>
      <?php if (isset($error_message) && strlen($error_message) > 0) echo "<div>$error_message</div>"; ?>
    </div>
  </div>
</div>
<!-- //wrap -->

<!-- footer -->
<div class="footer">
  <div class="inner">
    <p class="copyright"><?php echo L::copywrite_years; ?> &copy; <a href="javascript:logout();"><?php echo L::copywrite_company; ?></a></p>
  </div>
</div>
<!-- //footer -->

<!-- jquery JS -->
<script src="../js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="../js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="../plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="../js/common.js"></script>
<!-- app dist common for client JS -->
<script src="../js/appDistCommon4client.js?v4"></script>
</body>
</html>
