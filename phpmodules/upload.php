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
global $usingLogin, $topPath;
global $outBoundPoint;
global $topPath, $boanEndPoint;

if ($usingLogin && !isset($_SESSION['id'])) {
  if ($usingLoginRemoteAPI && $_SERVER['SERVER_NAME'] == $outBoundPoint) {
    // Do nothing for remote API login on app.company.com
    $redirectUrl = str_replace("4000", "8080", $boanEndPoint);
    header('Location: ' . $redirectUrl .'/'. $topPath . '/login.php?redirect='. $_SERVER['PHP_SELF']);
  } else {
    header('Location: /'. $topPath .'/login.php?redirect='. $_SERVER['PHP_SELF']);
  }
}

if (file_exists('../phpmodules/common.php')) {
  require('../phpmodules/common.php');
} else if (file_exists('common.php')) {
  require('common.php');
}

if ($_GET["file"]) {
  $input_file=$_GET["file"];
}
else {
  $input_file=$_POST["file"];
}
// Display informations
if (strlen($input_file) > 0) {
  $findingPath = realpath(__DIR__ . "/../android_distributions");
  if (!$findingPath) {
      $findingPath = realpath(__DIR__ . "/../../android_distributions");
  }
  $files = glob($findingPath . "/[1-9].*/$input_file.*");

  foreach($files as $file) {
      $base_dir = pathinfo($file, PATHINFO_DIRNAME);
      break;
  }
  $suffix = $json->{'android'}->{'outputGoogleStoreSuffix'};
  $target_google = $base_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $input_file . $suffix;
  if ($json->{'android'}->{'GoogleStore'}->{'usingBundleAAB'}) {
    $aSuffix = str_replace('apk', 'aab', $suffix);
    $bundle_google = $base_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $input_file . $aSuffix;
  }

  $suffix = $json->{'android'}->{'outputOneStoreSuffix'};
  if ($json->{'android'}->{'OneStore'}->{'usingBundleAAB'}) {
    $suffix = str_replace('apk', 'aab', $suffix);
  }
  $target_one = $base_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $input_file . $suffix;
}
// URL of upload_ok.php
$upload_ok_uri = "/" . $topPath . "/phpmodules/upload_ok.php";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::company_name ." ". L::app_name ?></title>
  <!-- font CSS -->
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css">
  <script type="text/javascript">
    function ValidateSubmit() {
        var oGoogle = document.getElementById('file_google');
        var oOne = document.getElementById('file_one');
        var check = 0;
        if (oGoogle && !oGoogle.value) {
          check ++;
        } else if (oOne && !oOne.value) {
          check ++;
        }
        if (check >= 2) {
          alert("<?php echo L::description_notice8_file_alert; ?>");
          return false;
        }
        document.getElementById('deliver').submit()
    }
    function FormSubmit(oForm) {
      window.uploadingAnimation( 'loadingAni' );
    }
  </script>
</head>

<body>
<!-- wrap -->
<div class="wrap register_wrap">
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="javascript:window.history.go(-2);"><?php echo L::company_name ." ". L::app_name ?> </a></h1>
      <a href="javascript:window.history.go(-2);" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container" style="width:90% !important;">
    <h2 class="stit"><?php echo L::company_name ." ". L::app_name ." ".  L::title_register_2nd_apksigning; ?></h2>
    <p class="txt"><span class="ico"></span><?php echo L::description_notice7_signing; ?></p>

    <form action="<?php echo $upload_ok_uri ?>" method="POST" id="deliver" name="deliver" enctype="multipart/form-data" onsubmit="FormSubmit(this);">
      <input type="hidden" name="deliver" value="findIt" />
      <input type="hidden" name="input_filename" value="<?php echo $input_file; ?>" />
  		<fieldset class="register_form">
      <legend><?php echo L::company_name ." ". L::app_name ." ". L::title_register_distribution; ?></legend>
        <div class="item box_type4" style="width:600px !important;">
          <div class="cont">
              <label for="rasisterA" class="txt_label"><?php echo L::title_register_googlestore_apk; ?></label>
              <?php if (file_exists($target_google)) {
                echo "<label class=\"txt_label\">". L::title_register_finished ."</label>";
              } else {
                echo "<input type=\"file\" name=\"file_google\" id=\"file_google\" style=\"width:90% !important;\">";
              }?>
          </div>
        </div>
        <?php if ($json->{'android'}->{'GoogleStore'}->{'usingBundleAAB'}) { ?>
        <div class="item box_type4" style="width:600px !important;">
          <div class="cont">
              <label for="rasisterA" class="txt_label"><?php echo L::title_register_googlestore_aab; ?></label>
              <?php if (isset($bundle_google) && file_exists($bundle_google)) {
                echo "<label class=\"txt_label\">". L::title_register_finished ."</label>";
              } else {
                echo "<input type=\"file\" name=\"bundle_google\" id=\"bundle_google\" style=\"width:90% !important;\">";
              }?>
          </div>
        </div>
        <?php } ?>
        <div class="item box_type4" style="width:600px !important;">
          <div class="cont">
    				<label for="rasisterA" class="txt_label"><?php echo L::title_register_onestore_apk; ?></label>
            <?php if (file_exists($target_one)) {
                echo "<label class=\"txt_label\">". L::title_register_finished ."</label>";
              } else {
                echo "<input type=\"file\" name=\"file_one\" id=\"file_one\" style=\"width:90% !important;\">";
              }?>
          </div>
        </div>
  		</fieldset>
    </form>

    <div class="btn_area">
      <?php if (file_exists($target_one) && file_exists($target_google)) {
        echo "<a href=\"#\" class=\"btn_confirm\">". L::button_upload ."</a>";
      } else {
        echo "<a href=\"#\" class=\"btn_confirm\" onclick=\"javascsript:ValidateSubmit();\">". L::button_upload ."</a>";
      }?>
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
