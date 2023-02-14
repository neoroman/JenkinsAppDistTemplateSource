<?php
// session_start();

require_once(__DIR__ . '/../config.php');
// global $usingLogin;

// if ($usingLogin && !isset($_SESSION['internal_id'])) {
//   header('Location: /login.php?redirect='. $_SERVER['PHP_SELF']);
// }
if (isset($_GET["appVer"])) {
  $appVer=$_GET["appVer"];
}
else if (isset($_POST["appVer"])) {
  $appVer=$_POST["appVer"];
}
if (isset($appVer)) {
  $version = str_replace(array('(', ')'), '', $appVer);
  $appVer = " (v$version)";
}
if (isset($_GET["os"])) {
  $inputOS=$_GET["os"];
}
else if (isset($_POST["os"])) {
  $inputOS=$_POST["os"];
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=0.5, maximum-scale=1.5, user-scalable=yes">
  <title><?php echo L::client_title ." ". L::app_name; ?></title>
  <link rel="apple-touch-icon-precomposed" href="../images/HomeIcon.png">
  <!-- font CSS -->
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css?v2">
  <!-- Jira Css -->
  <link rel="stylesheet" href="../css/jira.css">
</head>

<body>
<!-- wrap -->
<div class="wrap register_wrap qa_type2"> <!-- (내부)qa_type1, (외부)qa_type2 -->
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="javascript:history.back()"><?php echo L::client_title ." ". L::app_name; ?></a></h1>
      <a href="javascript:history.back()" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container">
    <div class="box_guide">
      <h1 class="tit">아래의 '피드백 제공' 버튼을 눌러 의견 또는<BR /> 불편 사항을 남겨주세요!</h1>
      <p class="stit">앱개발팀에서 최선을 다해서 개선하도록 하겠습니다. 답변을 받으시려면 "꼭" 연락처(이메일)을 남겨주세요.</p>
      <ul class="msg">
        <li>담당: 개발본부 > 개발부 > 앱개발팀</li>
      </ul>
      <br />
      <!-- <img src="images/right-arrow.gif" width=300 height=160> -->
    </div>
    <div class="btn_area">
      <a href="#" class="btn_send" id="feedback_button" class='btn btn-primary btn-large'>피드백 제공</a>
    </div>
    <div><h1><font color=red>[안내] Jira 서버의 방화벽 정책 변경으로 피드백을 남길 수 없습니다.<BR />(2022/6/16 ~ )</font></h1></div>
    <div><h1>개선 제안이나 이슈가 있으시면 [<b><a href="mailto:appdev.svc@company.com">메일: appdev.svc@company.com</a></b>]로 연락주시길 바랍니다.<BR /></h1></div>
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
<script src="../js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="../js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="../plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="../js/common.js"></script>
<!-- app dist common for client JS -->
<script src="../js/appDistCommon4client.js"></script>
<!-- Jira issue collector -->
<script type="text/javascript" src="http://svcdiv.company.com/s/f0e9f7b3406188080a76afc7900388f3-T/-w3q5mi/72008/2a2759a63b9be9071626e429a5d3e82b/2.0.23/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?locale=ko-KR&collectorId=c9c8ee91"></script>
<script type="text/javascript">
  var osComp = '11605';
  if (getMobileOperatingSystem() == "iOS" ) {
    osComp = '11423';
  } else if (getMobileOperatingSystem() == "Android" ) {
    osComp = '11424';
  }
  var osName = getMobileOperatingSystem();
  var jsAppVer, jsVersion;
  <?php
    if (isset($inputOS)) {
      if (strtolower($inputOS) == "ios" ) {
        echo "osComp = '11423';";
        echo "osName = 'iOS';";
      } else if (strtolower($inputOS) == "android" ) {
        echo "osComp = '11424';";
        echo "osName = 'Android';";
      }
    }
    if (isset($appVer)) {
      $appVer = trim($appVer);
      print("jsAppVer = '$appVer';\n");
    }
    if (isset($version)) {
      $version = trim($version);
      print("jsVersion = '$version';\n");
    }
  ?>
  window.ATL_JQ_PAGE_PROPS = $.extend(window.ATL_JQ_PAGE_PROPS, {
    'c9c8ee91': {
      "triggerFunction": function(showCollectorDialog) {
        //Requires that jQuery is available! 
        jQuery("#feedback_button").click(function(e) {
          e.preventDefault();
          showCollectorDialog();
        });
      },
      "fieldValues": {
        summary: osName + ' > 의견 또는 불편 사항 ' + jsAppVer,
        components: osComp,
        priority: "1",
        affectsVersion: jsVersion
      }
    }
  });
</script>
</body>
</html>
