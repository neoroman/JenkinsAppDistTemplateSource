<?php
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
global $outBoundPoint;

if (file_exists('../phpmodules/common.php')) {
  require('../phpmodules/common.php');
} else if (file_exists('common.php')) {
  require('common.php');
}

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

$org_os = $input_os;
if (!$isDebugMode && $_SERVER['SERVER_NAME'] == $outBoundPoint) {
  exit("고객사 배포는 사내망에서만 가능합니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='2;url=/$topPath/$org_os/dist_$org_os.php'>");
}

$path = pathinfo($input_file, PATHINFO_DIRNAME);
$basename = basename($input_file);
$basenameWithoutExt = basename($input_file, '.html');
if (startsWith($basenameWithoutExt, "zzz_")) {
  $pureBasenameWithoutExt = substr($basenameWithoutExt, 4);
}
$incFilename = $pureBasenameWithoutExt . ".inc.php";
$incFile = "$path/$incFilename";
if ($org_os == "ios") {
  $iOS_incFile = $incFile;
  $iOS_path = $path;
  $Android_incFile = str_replace('ios_distributions', 'android_distributions', $incFile);
  $Android_path = str_replace('ios_distributions', 'android_distributions', $path);
} elseif ($org_os == "android") {
  $iOS_incFile = str_replace('android_distributions', 'ios_distributions', $incFile);
  $iOS_path = str_replace('android_distributions', 'ios_distributions', $path);
  $Android_incFile = $incFile;
  $Android_path = $path;  
}
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
  if (file_exists("$path/$basename")) {
    updateVersionTag($_POST['version_target'], $_POST['version_details']);    
    $newFilename = renameInputFile();
    executeShellScript($newFilename, isset($_POST['resend']));
  }
  else {
    printError();
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
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css">
  <script type="text/javascript">
  function FormSubmit(oForm) {
      var oHidden = oForm.elements["version"];
      var oDDL = oForm.elements["version_ddl"];
      var oTextbox = oForm.elements["version_txt"];
      if (oHidden && oDDL && oTextbox)
          oHidden.value = (oDDL.value == "") ? oTextbox.value : oDDL.value;

      window.uploadingAnimation( 'loadingAni' );
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

<!-- loading :220127추가 -->
<div class="loading_dimm" style="visibility: hidden;">
  <span class="animation" id="loadingAni"></span>
</div>
<!-- //loading : 220127추가 -->

<!-- jquery JS -->
<script src="../js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="../js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="../plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- loading -->
<script src="../plugin/lottie.js"></script>
<script src="../js/loading.js"></script> <!-- 220127추가 -->
<!-- common JS -->
<script src="../js/common.js"></script>
<!-- app dist common for client JS -->
<script src="../js/appDistCommon4client.js?v4"></script>
</body>
</html>

<?php
function executeShellScript($newFilename, $isResend) {
  global $org_os, $input_os;
  global $isSendingEmail, $root, $topPath, $inUrl, $outUrl;
  global $isDebugMode;

  $resendArg = "resend";
  if (!$isResend) {
    $resendArg = "";
  }
  if ($isDebugMode) {
    $output = shell_exec(__DIR__ . '/../shells/doDistributions.sh '. $resendArg .' -d -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl));
    echo 'INPUT => ' .__DIR__. '/../shells/doDistributions.sh '. $resendArg .' -d -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) . ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl) .'<BR />\n\n';
    exit("$output<BR />고객사 배포가 완료되었습니다. <br /><script type=\"text/javascript\">window.stopAnimation();</script><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
  } else {
    $output = shell_exec(__DIR__ . '/../shells/doDistributions.sh '. $resendArg .' -p ' . escapeshellarg($input_os) . ' -f ' . escapeshellarg($newFilename) . ' -m ' . escapeshellarg($isSendingEmail) . ' -r ' . escapeshellarg($root) .  ' -tp ' . escapeshellarg($topPath) . ' -iu ' . escapeshellarg($inUrl) . ' -ou ' . escapeshellarg($outUrl));
    if ($input_os == 'both') {
      $goBackUrl = "/$topPath/$org_os/dist_$org_os.php";
    } else {
      $goBackUrl = "/$topPath/$input_os/dist_$input_os.php";
    }
    exit("고객사 배포가 완료되었습니다. <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a><meta http-equiv='REFRESH' content='1;url=$goBackUrl'>");
  }
}
function printError() {
  global $path, $basename;

  $debugDesc  = "Is startsWith($basename, \"zzz_\") is ". startsWith($basename, "zzz_");
  $debugDesc .= "<BR /><BR />";
  $debugDesc .= "Is file_exists(\"$path/\".$basename) is ". file_exists("$path/".$basename);
  $debugDesc .= "<BR /><BR />";
  exit("$debugDesc<br /> 고객사 배포 실패 <br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
}

function updateVersionTag($verTarget, $verDetail) {
  global $input_os, $incFile, $iOS_incFile, $Android_incFile;

  if (isset($verTarget) && strlen($verTarget)>0) {
    $aVersionTarget = $verTarget;
    $aVersionDetails = "";
    if (isset($verDetail)) {
      $aVersionDetails = $verDetail;
    }

    $content = "<?php\n"
    ."\$version_target = \"$aVersionTarget\";\n"
    ."\$version_details = \"$aVersionDetails\";\n"
    ."?>\n";

    if ($input_os != 'both') {
      // if (file_exists($incFile)) {
      //   unlink($incFile);
      // }
      file_put_contents($incFile, $content);
    } else {
      // if (file_exists($iOS_incFile)) {
      //   unlink($iOS_incFile);
      // }
      file_put_contents($iOS_incFile, $content);
      // if (file_exists($Android_incFile)) {
      //   unlink($Android_incFile);
      // }
      file_put_contents($Android_incFile, $content);
    }
  }
}
function renameInputFile() {
  global $path, $basename;
  global $input_os, $iOS_path, $Android_path;

  if (startsWith($basename, "zzz_") && file_exists("$path/$basename")) {
    $newFilename = substr(basename($basename), 4);
    if ($input_os != 'both') {
      rename("$path/$basename", "$path/$newFilename");
    } else {
      if (file_exists("$iOS_path/$basename")) {
        rename("$iOS_path/$basename", "$iOS_path/$newFilename");
      }
      if (file_exists("$Android_path/$basename")) {
        rename("$Android_path/$basename", "$Android_path/$newFilename");
      }
    }
    return $newFilename;
  }
  return $basename;
}
?>