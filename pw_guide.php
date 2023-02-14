<?php
session_start();

require_once('config.php');
global $usingLogin;
global $outBoundPoint;

if ($usingLogin && !isset($_SESSION['internal_id'])) {
  if ($usingLoginRemoteAPI && $_SERVER['SERVER_NAME'] == $outBoundPoint) {
    // Do nothing for remote API login on app.company.com
  } else {
    header('Location: login.php?redirect='. $_SERVER['PHP_SELF']);
  }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::client_title ." ". L::app_name; ?></title>
  <link rel="apple-touch-icon-precomposed" href="./images/HomeIcon.png">
  <!-- font CSS -->
  <link rel="stylesheet" href="./font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="./css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="./css/common.css">
</head>

<body>
<!-- wrap -->
<div class="wrap qa_type2"> <!-- (내부)qa_type1, (외부)qa_type2 -->
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="javascript:history.back()"><?php echo L::client_title ." ". L::app_name; ?></a></h1>
      <a href="javascript:history.back()" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container">
    <div class="box_guide">
      <h1 class="tit"><?php echo L::title_admin_password_guide; ?></h1>
      <p class="stit"><?php echo L::description_notice6_admin; ?></p>
      <ul class="msg">
        <li><?php echo L::description_notice6_admin_detail; ?></li>
      </ul>
      <br />
      <br />
      <br />
      <br />
    </div>
  </div>
</div>
<!--//wrap-->

<!-- footer -->
<div class="footer">
  <div class="inner">
    <p class="copyright"><?php echo L::copywrite_years; ?> &copy; <a href="javascript:logout();"><?php echo L::copywrite_company; ?></a></p>
  </div>
</div>
<!-- //footer -->

<!-- jquery JS -->
<script src="./js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="./js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="./plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="./js/common.js"></script>
<!-- app dist common for client JS -->
<!--<script src="./js/appDistCommon4client.js?v4"></script>-->
</body>
</html>
