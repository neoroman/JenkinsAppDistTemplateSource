<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title>Undo Remove HTML Snippet</title>
  <!-- font CSS -->
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css?v4">
  <script type="text/javascript">
    setTimeout(function() {
      window.uploadingAnimation( 'loadingAni' );
    }, 100);
  </script>
</head>

<body>

<?php
if (file_exists('../phpmodules/common.php')) {
  require('../phpmodules/common.php');
} else if (file_exists('common.php')) {
  require('common.php');
}
global $root, $inUrl;

$prevPage = $_SERVER['HTTP_REFERER'];
// echo "<meta http-equiv=\"REFRESH\" content=\"2;url=$prevPage\"></HEAD>";
if (isset($_GET["title"])) {
  $file_title=$_GET['title'];
}
if (isset($_GET["os"])) {
  $file_os=$_GET['os'];
}
if (isset($_GET["os"])) {
  $file_name=$_GET['file'];
}

$referrer = $prevPage;
if (strpos($prevPage, '?') !== false) {
  $tmp = preg_split('/\?/', $prevPage);
  $referrer = $tmp[0];
}
if (endsWith($referrer, "php")) {
  $osDir = $file_os;
  if ($file_os == "ios") {
    $osDir = "ios_distributions";
  } elseif ($file_os == "android") {
    $osDir = "android_distributions";
  }

  if ($file_name) {
    foreach (glob("../$osDir/*/*$file_name.html.deleted") as $filename) {
      echo "<H2>Undo removed HTML snippet.... [ DONE ]</H2>";
      $undoPath = pathinfo($filename, PATHINFO_DIRNAME);
      $undoFilename = basename($filename, '.deleted');
      rename($filename, "$undoPath/$undoFilename");
    }
  } else {
    $arr = preg_split('/[\s]+/', $file_title);
    $ver = $arr[0];
    $jenkins = $arr[1];

    // perform actions for each file found
    foreach (glob("../$osDir/*/*$ver*.html.deleted") as $filename) {
      // jenkins 빌드번호가 맞는 경우만 삭제(rename)함 by EungShik Kim on 2019.11.25
      if (stripos(file_get_contents($filename), $jenkins)) {
        echo "<H2>Undo removed HTML snippet.... [ DONE ]</H2>";
        $undoPath = pathinfo($filename, PATHINFO_DIRNAME);
        $undoFilename = basename($filename, '.deleted');
        rename($filename, "$undoPath/$undoFilename");
      }
    }
  }
  echo "<script type=\"text/javascript\">
        setTimeout(function(){
          window.stopAnimation();
          window.location.href = '$prevPage';
        }, 2000);        
        </script>";
}
?>
<BR /><BR />
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