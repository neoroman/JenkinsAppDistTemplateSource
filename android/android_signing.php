<?php
require_once('../config.php');
global $root, $isDebugMode;

$file_title=$_GET['title'];
if ($isDebugMode) {
    $output = shell_exec('./AndroidSigning.sh -d -p android -f ' . escapeshellarg($file_title)) . ' -r ' . escapeshellarg($root);
    echo 'INPUT => ./AndroidSigning.sh -d -p android -f ' . escapeshellarg($file_title) . ' -r ' . escapeshellarg($root) .'<BR /><BR />\n\n';
    exit("$output<BR />안드로이드 2차 난독화 Signing.... [ DEBUG ]<br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
} else {
    $output = shell_exec('./AndroidSigning.sh -p android -f ' . escapeshellarg($file_title)) . ' -r ' . escapeshellarg($root);
    echo 'INPUT => ./AndroidSigning.sh -p android -f ' . escapeshellarg($file_title) . ' -r ' . escapeshellarg($root) .'<BR /><BR />\n\n';
    exit("$output<BR />안드로이드 2차 난독화 Signing.... [ DONE ]<br /><a href='javascript:window.history.go(-2);'>뒤로가기</a>");
}
?>
