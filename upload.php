<?php
session_start();

require_once('config.php');
global $usingLogin;

if ($usingLogin && !isset($_SESSION['internal_id'])) {
  header('Location: login.php?redirect='. $_SERVER['PHP_SELF']);
}
require('common.php');

if ($_GET["file"]) {
  $input_file=$_GET["file"];
}
else {
  $input_file=$_POST["file"];
}
// Form submitted
if (strlen($input_file) > 0) {
  $files = glob("./android_distributions/[1-9].*/$input_file.*");
  foreach($files as $file) {
      $base_dir = pathinfo($file, PATHINFO_DIRNAME);
      break;
  }
  $suffix = $json->{'android'}->{'outputGoogleStoreSuffix'};
  if ($json->{'android'}->{'GoogleStore'}->{'usingBundleAAB'}) {
    $suffix = str_replace('apk', 'aab', $suffix);
  }
  $target_google = $base_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $input_file . $suffix;

  $suffix = $json->{'android'}->{'outputOneStoreSuffix'};
  if ($json->{'android'}->{'OneStore'}->{'usingBundleAAB'}) {
    $suffix = str_replace('apk', 'aab', $suffix);
  }
  $target_one = $base_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $input_file . $suffix;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::company_name ." ". L::app_name ?></title>
  <!-- font CSS -->
  <link rel="stylesheet" href="./font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="./css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="./css/common.css">
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

    <form action="upload_ok.php" method="POST" id="deliver" name="deliver" enctype="multipart/form-data">
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

<!-- jquery JS -->
<script src="./js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="./js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="./plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="./js/common.js"></script>
<!-- app dist common for client JS -->
<script src="./js/appDistCommon4client.js?v1"></script>
</body>
</html>
