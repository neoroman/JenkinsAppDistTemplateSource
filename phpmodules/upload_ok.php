<?php
if (!class_exists('i18n')) {
    if (file_exists(__DIR__ .'/../config.php')) {
        require_once(__DIR__ . '/../config.php');
    }  
    else if (file_exists(__DIR__ .'/../../config.php')) {
        require_once(__DIR__ . '/../../config.php');
    }  
}
global $topPath;

if ($_GET["input_filename"]) {
    $in_file=$_GET["input_filename"];
}
else {
    $in_file=$_POST["input_filename"];
}
if (isset($in_file)) {
    $files = glob("../android_distributions/[1-9].*/$in_file.*");
    foreach($files as $file) {
        $base_dir = pathinfo($file, PATHINFO_DIRNAME);
        break;
    }
} else {
    echo "No input file.";
}
// 설정
$uploads_dir = $base_dir; //'./android_distributions/3.3.1';
$allowed_ext = array('apk','APK','png','gif', 'aab', 'AAB');

$suffix = $json->{'android'}->{'outputGoogleStoreSuffix'};
$target_google = $uploads_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $in_file . $suffix;
if ($json->{'android'}->{'GoogleStore'}->{'usingBundleAAB'}) {
  $aSuffix = str_replace('apk', 'aab', $suffix);
  $bundle_google = $uploads_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $in_file . $aSuffix;
}

$suffix = $json->{'android'}->{'outputOneStoreSuffix'};
// if ($json->{'android'}->{'OneStore'}->{'usingBundleAAB'}) {
//   $suffix = str_replace('apk', 'aab', $suffix);
// }
$target_one = $uploads_dir ."/". $json->{'android'}->{'outputUnsignedPrefix'} . $in_file . $suffix;

// 파일 정보 출력
echo "<h2>". L::title_file_info ."</h2>";
echo "input: ". $in_file . "<br />";
echo "upload to: ". $uploads_dir . "<br />";

// 변수 정리
if (!file_exists($target_google)) {
    $error1 = $_FILES['file_google']['error'];
    $name1 = $_FILES['file_google']['name'];
    $ext1 = array_pop(explode('.', $name1));
     
    // 오류 확인
    if( $error1 != UPLOAD_ERR_OK ) {
        switch( $error1 ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo L::description_notice9_file_is_too_big ." ($error1)";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo L::description_notice10_file_not_selected ." ($error1)";
                break;
            default:
                echo L::description_notice11_file_not_uploaded ." ($error1)";
        }
        exit;
    }
    // 확장자 확인
    if( !in_array($ext1, $allowed_ext) ) {
        echo L::description_notice12_not_permitted_extention ." ($ext1) of $name1";
        exit;
    } 

    // 파일 이동
    move_uploaded_file( $_FILES['file_google']['tmp_name'], "$target_google");

    // 파일 정보 출력
    echo "<ul>\n";
    echo "<li>". L::title_file_name .": $name1</li>\n";
    echo "<li>". L::title_file_ext .": $ext1</li>\n";
    echo "<li>". L::title_file_format .": {$_FILES['file_google']['type']}</li>\n";
    echo "<li>". L::title_file_size .": {$_FILES['file_google']['size']} 바이트</li>\n";
    echo "<li>". L::title_file_path .": $target_google</li>\n";
    echo "</ul>";
}
if ($json->{'android'}->{'GoogleStore'}->{'usingBundleAAB'} && !file_exists($bundle_google)) {
    $error1 = $_FILES['bundle_google']['error'];
    $name1 = $_FILES['bundle_google']['name'];
    $ext1 = array_pop(explode('.', $name1));
     
    // 오류 확인
    if( $error1 != UPLOAD_ERR_OK ) {
        switch( $error1 ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo L::description_notice9_file_is_too_big ." ($error1)";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo L::description_notice10_file_not_selected ." ($error1)";
                break;
            default:
                echo L::description_notice11_file_not_uploaded ." ($error1)";
        }
        exit;
    }
    // 확장자 확인
    if( !in_array($ext1, $allowed_ext) ) {
        echo L::description_notice12_not_permitted_extention ." ($ext1) of $name1";
        exit;
    } 

    // 파일 이동
    move_uploaded_file( $_FILES['bundle_google']['tmp_name'], "$bundle_google");

    // 파일 정보 출력
    echo "<ul>\n";
    echo "<li>". L::title_file_name .": $name1</li>\n";
    echo "<li>". L::title_file_ext .": $ext1</li>\n";
    echo "<li>". L::title_file_format .": {$_FILES['bundle_google']['type']}</li>\n";
    echo "<li>". L::title_file_size .": {$_FILES['bundle_google']['size']} 바이트</li>\n";
    echo "<li>". L::title_file_path .": $bundle_google</li>\n";
    echo "</ul>";
}


if (!file_exists($target_one)) {
    $error2 = $_FILES['file_one']['error'];
    $name2 = $_FILES['file_one']['name'];
    $ext2 = array_pop(explode('.', $name2));
     
    // 오류 확인
    if( $error2 != UPLOAD_ERR_OK ) {
        switch( $error ) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                echo L::description_notice9_file_is_too_big ." ($error2)";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo L::description_notice10_file_not_selected ." ($error2)";
                break;
            default:
                echo L::description_notice11_file_not_uploaded ." ($error2)";
        }
        exit;
    }
    // 확장자 확인
    if( !in_array($ext2, $allowed_ext) ) {
        echo L::description_notice12_not_permitted_extention ." ($ext2) of $name2";
        exit;
    } 

    // 파일 이동
    move_uploaded_file( $_FILES['file_one']['tmp_name'], "$target_one");

    // 파일 정보 출력
    echo "<ul>\n";
    echo "<li>". L::title_file_name .": $name2</li>\n";
    echo "<li>". L::title_file_ext .": $ext2</li>\n";
    echo "<li>". L::title_file_format .": {$_FILES['file_one']['type']}</li>\n";
    echo "<li>". L::title_file_size .": {$_FILES['file_one']['size']} byte</li>\n";
    echo "<li>". L::title_file_path .": $target_one</li>\n";
    echo "</ul>";
}

echo "<br /><a href='/$topPath/android/dist_android.php'>". L::title_back ."</a>";
?>