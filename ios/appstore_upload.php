<?php
require_once('../config.php');
global $root, $isDebugMode;

$file_title=$_GET['title'];

$referrer = "index.php";
if (isset($_SERVER['HTTP_REFERER'])) {
    $referrer = basename($_SERVER['HTTP_REFERER']);
}

if ($isDebugMode) {
    $output = shell_exec('./AppStoreUpload.sh -d -p ios -f ' . escapeshellarg($file_title)) . ' -r ' . escapeshellarg($root);
    echo 'INPUT => ./AppStoreUpload.sh -d -p ios -f ' . escapeshellarg($file_title) . ' -r ' . escapeshellarg($root) .'<BR /><BR />\n\n' . $_SERVER['HTTP_REFERER'];
    exit("<META http-equiv='REFRESH' content='1;url=$referrer'>$output<BR />iOS upload to App Store .... [ DEBUG ]<br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
} else {
    $output = shell_exec('./AppStoreUpload.sh -p ios -f ' . escapeshellarg($file_title)) . ' -r ' . escapeshellarg($root);
    echo 'INPUT => ./AppStoreUpload.sh -p ios -f ' . escapeshellarg($file_title) . ' -r ' . escapeshellarg($root) .'<BR /><BR />\n\n' . $_SERVER['HTTP_REFERER'];
    exit("<META http-equiv='REFRESH' content='1;url=$referrer'>$output<BR />iOS upload to App Store  [ DONE ]<br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
}
?>
