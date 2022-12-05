<?php
require('common.php');
global $root, $inUrl;

$prevPage = $_SERVER['HTTP_REFERER'];
echo "<meta http-equiv=\"REFRESH\" content=\"3;url=$prevPage\"></HEAD>";
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
    foreach (glob("./$osDir/*/*$file_name.html.deleted") as $filename) {
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
    foreach (glob("./$osDir/*/*$ver*.html.deleted") as $filename) {
      // jenkins 빌드번호가 맞는 경우만 삭제(rename)함 by EungShik Kim on 2019.11.25
      if (stripos(file_get_contents($filename), $jenkins)) {
        echo "<H2>Undo removed HTML snippet.... [ DONE ]</H2>";
        $undoPath = pathinfo($filename, PATHINFO_DIRNAME);
        $undoFilename = basename($filename, '.deleted');
        rename($filename, "$undoPath/$undoFilename");
      }
    }
  }
}
?>
<BR /><BR />
