<?php
require_once('config.php');
global $topPath, $root;
global $inUrl, $outUrl, $isDebugMode;
global $outBoundPoint;

require('common.php');

if (isset($_SERVER['HTTP_REFERER'])) {
  $prevPage = $_SERVER['HTTP_REFERER'];
}
if (isset($_GET["os"])) {
  $input_os=$_GET["os"];
} else {
  $input_os=$_POST["os"];
}
if (isset($_GET["type"])) {
  $dist_mode=$_GET["type"];
} else {
  $dist_mode=$_POST["type"];
}
if (isset($_GET["file"])) {
  $input_file=$_GET["file"];
} else {
  $input_file=$_POST["file"];
}
if ($input_os != "ios" && $input_os != "android") {
  echo "<meta http-equiv=\"REFRESH\" content=\"3;url=$prevPage\"></HEAD>";
  echo "<H1>알 수 없는 OS 타입으로 배포가 불가능 합니다.</H1>";
  exit("알 수 없는 OS 타입으로 배포가 불가능 합니다.");
}
if (!$input_file) {
  echo "<meta http-equiv=\"REFRESH\" content=\"3;url=$prevPage\"></HEAD>";
  echo "<H1>입력 파일이 없어서 배포가 불가능 합니다.</H1>";
  exit("입력 파일이 없어서 배포가 불가능 합니다.");
}
if (isset($_GET["resending"])) {
  $is_reseding=$_GET["resending"];
}
else if (isset($_POST["resending"])) {
  $is_reseding=$_POST["resending"];
}

$newOS = $input_os;
$org_os = $input_os;
if ($input_os == "ios") {
  $newOS = "ios_distributions";
} elseif ($input_os == "android") {
  $newOS = "android_distributions";
}
if ($_SERVER['SERVER_NAME'] == $outBoundPoint) {
  exit("고객사 배포는 사내망에서만 가능합니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='2;url=/$topPath/$org_os/dist_$org_os.php'>");
}

$newPath = pathinfo($input_file, PATHINFO_DIRNAME);
if (startsWith($newPath, './')) {
  $newPath = substr($newPath, 2);
} else if (startsWith($newPath, '../') && !file_exists($newPath) && strpos($newPath, $newOS)) {
  $newPath = substr($newPath, 3 + strlen($newOS) + 1);
}
$filename = basename($input_file, '.html') . ".inc.php";
if (startsWith(basename($input_file), "zzz_")) {
  $newFilename = substr(basename($input_file), 4);
  $filename = basename($newFilename, '.html') . ".inc.php";
}
$incFile = "$newOS/$newPath/$filename";
$iOS_incFile = "ios_distributions/$newPath/$filename";
$Android_incFile = "android_distributions/$newPath/$filename";

if (isset($is_reseding) && $is_reseding && file_exists($incFile)) {
  require_once($incFile);
}

// Form submitted
if (isset($_POST['deliver'])) {
  $isSendingEmail = 0;
  if (isset($_POST['sendEmail'])) {
    $isSendingEmail = $_POST['sendEmail'];
  }
  if (isset($_POST['sendBothPlatform']) && $_POST['sendBothPlatform'] == "1") {
    $input_os = 'both';
  }

  if (isset($_POST['resend'])) {
    if (isset($_POST['version_target']) && strlen($_POST['version_target'])>0) {
      $aVersionTarget = $_POST['version_target'];
      $aVersionDetails = "";
      if (isset($_POST['version_details'])) {
        $aVersionDetails = $_POST['version_details'];
      }

      if (file_exists($incFile)) {
        unlink($incFile);
      }
      $content = "<?php\n"
      ."\$version_target = \"$aVersionTarget\";\n"
      ."\$version_details = \"$aVersionDetails\";\n"
      ."?>\n";
      if ($input_os != 'both') {
        file_put_contents($incFile, $content);
      } else {
        $result_ios = file_put_contents($iOS_incFile, $content);
        $result_aos = file_put_contents($Android_incFile, $content);
      }
    }
    if (file_exists("./$newOS/$newPath/".basename($input_file))) {
      $newFilename = basename($input_file);
      if ($isDebugMode) {
        $output = shell_exec('./doDistributions.sh resend -d -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl));
        echo 'INPUT => ./doDistributions.sh resend -d -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl) .'<BR />\n\n';
        exit("$output<BR />고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
      } else {
        $output = shell_exec('./doDistributions.sh resend -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) .  ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl));
        if ($input_os == 'both') {
          exit("고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='1;url=/$topPath/$org_os/dist_$org_os.php'>");
        } else {
          exit("고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='1;url=/$topPath/$input_os/dist_$input_os.php'>");
        }
      }
    }
    else {
      $debugDesc  = "Is startsWith(basename($input_file), \"zzz_\") is ". startsWith(basename($input_file), "zzz_");
      $debugDesc .= "Is file_exists(\"./$newOS/$newPath/\".basename($input_file)) is ". file_exists("./$newOS/$newPath/".basename($input_file));
      exit("$debugDesc<br /> 고객사 배포 실패 <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
    }
  }
  else if (isset($_POST['version_target']) && strlen($_POST['version_target'])>0) {
    $aVersionTarget = $_POST['version_target'];
    echo "<H1><span style=\"color: pink; \">$aVersionTarget</span></H1>
    <script>
      const countDownTimer = function (id, date) { 
        var _vDate = new Date(date); // 전달 받은 일자 
        var _second = 1000; 
        var _minute = _second * 60; 
        var _hour = _minute * 60; 
        var _day = _hour * 24; 
        var timer; 
        function showRemaining() { 
          var now = new Date(); 
          var distDt = _vDate - now; 
          if (distDt < 0) { 
            clearInterval(timer); 
            document.getElementById(id).textContent = '완료 되었습니다!'; 
            return; 
          } 
          // var days = Math.floor(distDt / _day); 
          // var hours = Math.floor((distDt % _day) / _hour); 
          // var minutes = Math.floor((distDt % _hour) / _minute); 
          var seconds = Math.floor((distDt % _minute) / _second); 
          // document.getElementById(id).textContent = days + '일 '; 
          // document.getElementById(id).textContent += hours + '시간 '; 
          // document.getElementById(id).textContent += minutes + '분 '; 
          document.getElementById(id).textContent = seconds + '초'; 
        } 
        timer = setInterval(showRemaining, 1000); 
      } 
      function startTimer() {
        var dateObj = new Date(); 
        dateObj.setTime(Date.now() + 1 * 61 * 1000); // Add 1 minutes to current timestamp
        countDownTimer('sample01', dateObj); // 1분후 
        // countDownTimer('sample02', '04/01/2024 00:00 AM'); // 2024년 4월 1일까지, 시간을 표시하려면 01:00 AM과 같은 형식을 사용한다. 
        // countDownTimer('sample03', '04/01/2024'); // 2024년 4월 1일까지 
        // countDownTimer('sample04', '04/01/2019'); // 2024년 4월 1일까지 
      }
      startTimer();
    </script>
    <body>
    <div id='sample01' style='font-size:200px;color:pink'></div>
    </body>
    ";
    $aVersionDetails = "";

    if (isset($_POST['version_details']) && strlen($_POST['version_details'])>0) {
      $aVersionDetails = $_POST['version_details'];
    }
    if (file_exists($incFile)) {
      unlink($incFile);
    }
    $content = "<?php\n"
    ."\$version_target = \"$aVersionTarget\";\n"
    ."\$version_details = \"$aVersionDetails\";\n"
    ."?>\n";

    if (startsWith(basename($input_file), "zzz_") && file_exists("./$newOS/$newPath/".basename($input_file))) {
      $newFilename = substr(basename($input_file), 4);
      if ($input_os != 'both') {
        file_put_contents($incFile, $content);
        rename("./$newOS/$newPath/".basename($input_file), "$newOS/$newPath/$newFilename");
      } else {
        file_put_contents($iOS_incFile, $content);
        file_put_contents($Android_incFile, $content);

        if (file_exists("./ios_distributions/$newPath/".basename($input_file))) {
          rename("./ios_distributions/$newPath/".basename($input_file), "ios_distributions/$newPath/$newFilename");
        }
        if (file_exists("./android_distributions/$newPath/".basename($input_file))) {
          rename("./android_distributions/$newPath/".basename($input_file), "android_distributions/$newPath/$newFilename");
        }
      }

      if ($isDebugMode) {
        $output = shell_exec('./doDistributions.sh -d -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl));
        echo 'INPUT => ./doDistributions.sh resend -d -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl) .'<BR />\n\n';
        exit("$output<BR />고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
      } else {
        $output = shell_exec('./doDistributions.sh -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl));
          if ($input_os != 'both') {
            exit("고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='1;url=/$topPath/$input_os/dist_$input_os.php'>");
          } else {
            exit("고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='1;url=/$topPath/$org_os/dist_$org_os.php'>");
          }
      }
    }
    else {
      $debugDesc  = "Is startsWith(basename($input_file), \"zzz_\") is ". startsWith(basename($input_file), "zzz_");
      $debugDesc .= "Is file_exists(\"./$newOS/$newPath/\".basename($input_file)) is ". file_exists("./$newOS/$newPath/".basename($input_file));
      exit("$debugDesc<br /> 고객사 배포 실패 <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
    }
  }
}
if (startsWith(basename($input_file), "zzz_")) {
  $realVersionString = substr(basename($input_file, '.html'), 4);
} else {
  $realVersionString = basename($input_file, '.html');
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
  <link rel="stylesheet" href="./font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="./css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="./css/common.css">
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
<div class="wrap register_wrap">
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="javascript:window.history.go(-1);"><?php echo L::client_title ." ". L::app_name ?></a></h1>
      <a href="javascript:window.history.go(-1);" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container">
    <h2 class="stit"><?php echo L::client_title . " " . L::app_name . " " . L::title_register_distribution; ?></h2>
    <p class="txt">
      <span class="ico"></span><?php echo L::description_notice5; ?><BR />
      <span class="ico"></span><?php echo L::description_notice17; ?>
    </p>

    <form action="#" method="POST" id="deliver" name="deliver" onsubmit="FormSubmit(this);">
      <input type="hidden" name="previous_page" value="<?php $prevPage ?>" />
      <input type="hidden" name="deliver" value="findIt"/>
      <?php if (isset($is_reseding) && $is_reseding) {
        echo "<input type=\"hidden\" name=\"resend\" value=\"modifyIt\"/>\n";
      } ?>
  		<fieldset class="register_form">
      <legend><?php echo L::company_name . " " . L::app_name . " " . L::title_register_distribution; ?></legend>
  			<div class="inputs">
    			<div class="target_binary">
            <label for="description" class="txt_label"><?php echo L::title_distribution_version; ?></label>
            <input type="text" class="inp_text" name="version_name" id="description" value="<?php echo $realVersionString; ?>" style="outline:none" readonly />
          </div>
  				<label for="rasisterA" class="txt_label"><?php echo L::title_distribution_purpose; ?><span class="point_c1">(<?php echo L::title_mandatory; ?>)</span></label>
          <input type="text" class="inp_text" name="version_target" id="rasisterA" <?php 
              if (isset($is_reseding) && $is_reseding && isset($version_target)) {
                echo "value=\"$version_target\"";
              }
          ?>placeholder="<?php echo L::title_distribution_purpose_example; ?>" />

          <label for="rasisterB" class="txt_label"><?php echo L::title_distribution_detail; ?><span class="point_c1">(<?php echo L::title_optional; ?>)</span></label>
          <input type="text" class="inp_text" name="version_details" id="rasisterB" <?php
              if (isset($is_reseding) && $is_reseding && isset($version_details)) {
                echo "value=\"$version_details\"";
              }
          ?>placeholder="<?php echo L::title_distribution_detail_example; ?>" />

          <p style="position: relative; top:1.5px; !important">
            <label style="word-wrap:break-word" for="rasisterC" class="txt_label">
              <input type="checkbox" name="sendEmail" id="rasisterC" value="1" checked />
              <?php echo L::title_distribution_mail_usage; ?><span class="point_c1">(<?php echo L::title_optional; ?>)</span>
            </label><?php if ($dist_mode != 'release') { ?>
            <label for="rasisterD" class="txt_label">
              <input type="checkbox" name="sendBothPlatform" id="rasisterD" value="1" <?php 
                if ($input_os == 'ios') {
                  $found_to_be = str_replace('ios', 'android', $input_file);
                  if (startsWith($found_to_be, "../")) {
                    $found_to_be = substr($found_to_be, 3);
                  }
                  if (file_exists("$found_to_be")) {
                    echo "checked";
                  }
                } else if ($input_os == 'android') {
                  $found_to_be = str_replace('android', 'ios', $input_file);
                  if (startsWith($found_to_be, "../")) {
                    $found_to_be = substr($found_to_be, 3);
                  }
                  if (file_exists("$found_to_be")) {
                    echo "checked";
                  }                  
                }
              ?> />
              <?php echo L::title_distribution_mail_both_platform; ?><span class="point_c1">(<?php echo L::title_optional; ?>)</span>
            </label>
            <?php } ?>
          </p>
      </div>
  		</fieldset>
    </form>

    <div class="btn_area">
      <a href="#" class="btn_confirm" onclick="javascsript:document.getElementById('deliver').submit()">
      <?php if (isset($is_reseding) && $is_reseding) {
          echo L::button_resend_mail;
        } else {
          echo L::button_send_to_client;
        }
        if ($isDebugMode) echo "&nbsp;(DEBUG)"; ?>
      </a>
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
<script src="./js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="./js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="./plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="./js/common.js"></script>
<!-- app dist common for client JS -->
<script src="./js/appDistCommon4client.js?v4"></script>
</body>
</html>
