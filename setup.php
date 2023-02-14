<?php
require_once('config.php');
global $topPath, $root;
global $inUrl, $outUrl, $isDebugMode;

require('common.php');

$configJson = ".test/test.json";
$jsonString = file_get_contents($configJson);
$data = json_decode($jsonString, true);

// TODO: Edit and Write a JSON
// $data['users']['app']['email'] = "bar@foo.com";
// print_r($data['users']['app']);

// $newJsonString = json_encode($data, JSON_PRETTY_PRINT);
// file_put_contents($configJson, $newJsonString);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::setup_title; ?></title>
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
      <h1 class="logo"><a href="javascript:window.history.go(-2);"><?php echo L::setup_title; ?></a></h1>
      <a href="javascript:window.history.go(-2);" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container">
    <h2 class="stit"><?php echo L::setup_title ." ". L::setup_step . " 1/10"; ?></h2>
    <p class="txt">
        <span class="ico"></span><?php echo L::setup_description; ?><br />
        <span class="ico"></span><?php echo L::setup_description2; ?>
    </p>

    <form action="#" method="POST" id="setup" name="setup" onsubmit="FormSubmit(this);">
      <input type="hidden" name="previous_page" value="<?php $prevPage ?>" />
      <input type="hidden" name="deliver" value="findIt"/>
      <?php if ($is_reseding) {
        echo "<input type=\"hidden\" name=\"resend\" value=\"modifyIt\"/>\n";
      } ?>
  		<fieldset class="register_form">
      <legend><?php echo L::company_name . " " . L::app_name . " " . L::title_register_distribution; ?></legend>
  			<div class="inputs">
  				<label for="rasisterA" class="txt_label"><?php echo L::title_distribution_purpose; ?><span class="point_c1">(<?php echo L::title_mandatory; ?>)</span></label>
          <input type="text" class="inp_text" name="version_target" id="rasisterA" <?php 
              if ($is_reseding) {
                echo "value=\"$version_target\"";
              }
          ?>placeholder="<?php echo L::title_distribution_purpose_example; ?>">

          <label for="rasisterB" class="txt_label"><?php echo L::title_distribution_detail; ?><span class="point_c1">(<?php echo L::title_optional; ?>)</span></label>
          <input type="text" class="inp_text" name="version_details" id="rasisterB" <?php
              if ($is_reseding) {
                echo "value=\"$version_details\"";
              }
          ?>placeholder="<?php echo L::title_distribution_detail_example; ?>">
  			</div>
  		</fieldset>
    </form>

    <div class="btn_area">
      <a href="#" class="btn_confirm" onclick="javascsript:document.getElementById('setup').submit()">
      <?php if ($is_reseding) {
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
