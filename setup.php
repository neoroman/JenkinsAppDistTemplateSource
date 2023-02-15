<?php
// require_once('config.php');
// global $topPath, $root;
// global $inUrl, $outUrl, $isDebugMode;

// require('./phpmodules/common.php');

// $configJson = "./config/config.json";
// if (file_exists($configJson)) {
//   $jsonString = file_get_contents($configJson);
//   $data = json_decode($jsonString, false);
// } else {
//   echo "<H1>ERROR unknown</H1>";
//   exit(101);
// }

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
  <title>Jenkins Application Distribution Site</title>
  <!-- font CSS -->
  <link rel="stylesheet" href="./font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="./css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="./css/common.css">
</head>

<body>
<!-- wrap -->
<div class="wrap register_wrap">
  <div class="header">
    <div class="inner">
      <h1><p>SETUP: Jenkins Application Distribution Site</p></h1>
    </div>
  </div>

  <div class="container">
  <?php if (!file_exists('config/config.json')) { ?>
    <h2 class="stit">Please copy ``config/config.json.default`` to ``config/config.json``</h2>
  <?php } ?>
  <?php if (!file_exists('lang/lang_ko.json')) { ?>
    <h2 class="stit">Please copy ``lang/lang_ko.json.default`` to ``lang/lang_ko.json``</h2>
  <?php } ?>
  <?php if (!file_exists('lang/lang_en.json')) { ?>
    <h2 class="stit">Please copy ``lang/lang_en.json.default`` to ``lang/lang_en.json``</h2>
  <?php } ?>
  </div>
</div>
<!-- //wrap -->

<!-- footer -->
<div class="footer">
  <div class="inner">
    <p class="copyright"></p>
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
