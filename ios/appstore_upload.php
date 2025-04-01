<?php
// Set execution time limit to 300 seconds (5 minutes)
set_time_limit(300);

if (!class_exists('i18n')) {
    if (file_exists(__DIR__ .'/../config.php')) {
        require_once(__DIR__ . '/../config.php');
    }
    else if (file_exists(__DIR__ .'/../../config.php')) {
        require_once(__DIR__ . '/../../config.php');
    }
}
global $root, $isDebugMode;

$file_title=$_GET['title'];

$referrer = "index.php";
if (isset($_SERVER['HTTP_REFERER'])) {
    $referrer = basename($_SERVER['HTTP_REFERER']);
}

if ($isDebugMode) {
    $output = shell_exec(__DIR__ .'/AppStoreUpload.sh -d -p ios -f ' . escapeshellarg($file_title)) . ' -r ' . escapeshellarg($root);
    echo 'INPUT => '. __DIR__ .'/AppStoreUpload.sh -d -p ios -f ' . escapeshellarg($file_title) . ' -r ' . escapeshellarg($root) .'<BR /><BR />' . $_SERVER['HTTP_REFERER'];
    exit("<BR /><BR /><pre>$output</pre><BR />iOS upload to App Store .... [ DEBUG ]<br /><a href='javascript:window.history.go(-1);'>뒤로가기</a>");
} else {
    $output = shell_exec(__DIR__ .'/AppStoreUpload.sh -p ios -f ' . escapeshellarg($file_title)) . ' -r ' . escapeshellarg($root);
    echo 'INPUT => '. __DIR__ .'/AppStoreUpload.sh -p ios -f ' . escapeshellarg($file_title) . ' -r ' . escapeshellarg($root) .'<BR /><BR />' . $_SERVER['HTTP_REFERER'];
    // exit("<META http-equiv='REFRESH' content='1;url=$referrer'>$output<BR />iOS upload to App Store  [ DONE ]<br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
    exit("<BR /><BR /><pre>$output</pre><BR />iOS upload to App Store  [ DONE ]<br /><a href='javascript:window.history.go(-1);'>뒤로가기</a>");
}
?>
