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
    $file_name = str_replace('zzz_', '', $file_name);
    foreach (glob("./$osDir/*/*$file_name*") as $filename) {
        // Remove whole files instead of rename by EungShik Kim on 2022/03/24
        // echo "<H2>Delete HTML snippet.... [ DONE ]</H2>";
        // rename($filename, "$filename.deleted");
        echo "<H2>Delete '$filename' [ DONE ]</H2>";
        unlink($filename);
    }
  } else {
    $arr = preg_split('/[\s]+/', $file_title);
    $ver = $arr[0];
    $jenkins = str_replace($ver, "", $file_title);

    // perform actions for each file found
    foreach (glob("./$osDir/*/*$ver*.html") as $filename) {
      // jenkins 빌드번호가 맞는 경우만 삭제(rename)함 by EungShik Kim on 2019.11.25
      // echo "jenkins => $jenkins, ver => $ver";
      if (stripos(file_get_contents($filename), $jenkins)) {
        echo "<H2>Delete HTML snippet.... [ DONE ]</H2>";
        rename($filename, "$filename.deleted");
      }
    }
  }
}
?>
<BR /><BR />
