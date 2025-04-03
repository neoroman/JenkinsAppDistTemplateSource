<?php
if (!class_exists('i18n')) {
    if (file_exists(__DIR__ .'/../config.php')) {
        require_once(__DIR__ . '/../config.php');
    }
    else if (file_exists(__DIR__ .'/../../config.php')) {
        require_once(__DIR__ . '/../../config.php');
    }
}
require(__DIR__ . '/utils/string.php');

function getPaginationSnippets($os, $isDomesticQA)
{    
    $pageContents = "";

    if ($isDomesticQA) { // QA 전용 페이지
        $fileKey = "html*";
    } else {
        $fileKey = "html";
    }

    if ($os == "android") {
        $findingPath = realpath(__DIR__ . "/../android_distributions");
        if (!$findingPath) {
            $findingPath = realpath(__DIR__ . "/../../android_distributions");
        }
        $files = glob($findingPath . "/[0-9].*/*.$fileKey");
    }
    else if ($os == "ios") {
        $findingPath = realpath(__DIR__ . "/../ios_distributions");
        if (!$findingPath) {
            $findingPath = realpath(__DIR__ . "/../../ios_distributions");
        }
        $files = glob($findingPath . "/[0-9].*/*.$fileKey");
    }

    usort($files, function($a, $b) {
        return filemtime($a) < filemtime($b);
    });

    $CardsPerSite = 20;
    $total_data = count($files);
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;     // GETTING PAGE NUMBER FROM URL

    if (empty($page) || $page == 1) {
        $start_val = 0;
        $end_val = min($CardsPerSite - 1, $total_data - 1); // Make sure the end value does not exceed the total data
    } else {
        $start_val = ($page * $CardsPerSite) - $CardsPerSite;
        $end_val = min($start_val + ($CardsPerSite - 1), $total_data - 1); // Ensure the end value is within bounds
    }

    $less_than = ceil($total_data / $CardsPerSite); // Use ceil to properly calculate total pages
    $less_than = max($less_than, 1); // Ensure at least 1 page is available

    if ($total_data > 1) {
        $pageContents .= '<div class="pagination">';
        $pageContents .= ($page - 1) > 0 ? '<a href="?page=' . ($page - 1) . '">'. L::page_prev .'</a><span class="bar">|</span>' : L::page_prev .'<span class="bar">|</span>';
        for ($i = 1; $i <= $less_than; $i++) {
            if ($page == $i) $pageContents .= '<a class="on">';
            else $pageContents .= '<a href="?page=' . $i . '">';
            $pageContents .= $i;
            $pageContents .= '</a><span class="bar">|</span>';
        }
        $pageContents .= ($page + 1) <= $less_than ? '<a href="?page=' . ($page + 1) . '">'. L::page_next .'</a>' : L::page_next;
        $pageContents .= '</div>';
        $pageContents .= '<BR />';
    }

    $result = array();
    array_push($result, array_slice($files, $start_val, $end_val - $start_val + 1)); // Correct slicing
    array_push($result, $pageContents);
    array_push($result, $page . "/" . (int)$less_than);

    return $result;
}

function getHtmlSnippets($os, $isDomesticQA, $isSearch, $searchPattern, $files): string
{
    global $json, $config;
    global $frontEndProtocol;
    global $frontEndPoint;
    global $outBoundProtocol;
    global $outBoundPoint;
    global $documentRootPath;
    global $topPath;
    // global $isDebugMode;

    $finalContents = "";

    foreach($files as $file) {
        $content = file_get_contents($file);

        $typeKey = "2"; // default: box_type2(배포후)
        if ($isDomesticQA) {
            // QA 전용 페이지 ///////////////////////////////////////////////////////////
            if (endsWith($file, "html.bak")) {
                continue;
            } else if (endsWith($file, "html.deleted")) {
                // for 회사 내부 QA 페이지용만 bak에 대해서 처리함 on 2019.11.22
                $path = pathinfo($file, PATHINFO_DIRNAME);
                $filename = basename($file, '.deleted');
                $typeKey = "_del";
            } else if (startsWith(basename($file), "zzz_")) {
                // 아직 배포되지 않은 페이지
                $typeKey = "1";
            }
        } else {
            // 외부(고객사) 배포 페이지 //////////////////////////////////////////////////////////
            if (startsWith(basename($file), "zzz_")) {
                // 아직 배포되지 않은 페이지
                continue;
            }
        }

        if ($typeKey != "_del") {
            if ($os == "android" && strpos($content, '(스토어 배포용)')) {
                // <!-- 5타입 : box_type1(배포전), box_type2(배포후), box_type3(입고 검증전), box_type4(입고 검증후), box_type_del(삭제) -->
                if (startsWith(basename($file), "zzz_")) {
                    $typeKey = "3";
                } else {
                    $typeKey = "4";
                }
            } else if ($os == "ios" && strpos($content, '(앱스토어 검증버전)')) {
                // <!-- 5타입 : box_type1(배포전), box_type2(배포후), box_type3(입고 검증전), box_type4(입고 검증후), box_type_del(삭제) -->
                if (startsWith(basename($file), "zzz_")) {
                    $typeKey = "3";
                } else {
                    $typeKey = "4";
                }
            }
        }


        // START: '배포목적' TAG박스 표시
        $versionTarget = "";
        $versionDetail = "";
        $path = pathinfo($file, PATHINFO_DIRNAME);
        if (endsWith($file, "deleted")) {
            $basenameWithoutExt = basename($file, '.html.deleted');
        } else {
            $basenameWithoutExt = basename($file, '.html');
        }
        $incFilename = $basenameWithoutExt . ".inc.php";
        if (file_exists("$path/$incFilename")) { // 배포 후
            require_once("$path/$incFilename");
            if (isset($version_target) && strlen($version_target) > 0) {
                $altText = "";
                if (isset($version_details) && strlen($version_details) > 0) {
                    $altText = "(" . $version_details . ")";
                }
                $content = preg_replace("/(<h2 class=\"tit_box\"><span class=\"txt\">)*(<\/span><\/h2>)/", "$1$version_target$altText$2", $content);
                $versionTarget = $version_target;
                $versionDetail = $altText;
                unset($version_target);
                unset($version_details);
            }
        }
        if ($typeKey == "_del") {
            // 삭제됨
            $versionTarget = strlen($versionTarget) > 0 ? "<s>$versionTarget</s>&gt;&nbsp;삭제됨" : "삭제됨";
        } else if ($typeKey == "4" && strlen($versionTarget) == 0) {
            // 검증 입고 완료 버전
            $versionTarget = L::title_tab_qc_version;
        }
        // E N D: '배포목적' TAG박스 표시


        // START: Fetch JSON data
        if (startsWith($basenameWithoutExt, "zzz_")) {
            $basenameWithoutExt = substr($basenameWithoutExt, 4);
        }
        $jsonfile = $basenameWithoutExt . ".json";
        $finalSnippet = "";
        if (file_exists("$path/$jsonfile")) {
            $jsonStr = file_get_contents("$path/$jsonfile");
            $finalJson = json_validate2($jsonStr, false);

            if ($os == "ios") {
                $osName = L::os_ios;
                $storeTarget = "AppStore";
            } else if ($os == "android") {
                $osName = L::os_android;
                $storeTarget = "GoogleStore";
            } else {
                $osName = 'unknown';
                $storeTarget = "Unknown";
            }
            $jsonReleaseType = "Debug";
            if (isset($finalJson->{'releaseType'})) {
                $jsonReleaseType = $finalJson->{'releaseType'};
            }

            if ($typeKey != "_del") {
                if ($jsonReleaseType == 'release') {
                    if (startsWith(basename($file), "zzz_")) {
                        $typeKey = "3";
                    } else {
                        $typeKey = "4";
                    }
                } elseif ($os == "ios") {
                    $appstoreIPA = glob($path . "/" . $basenameWithoutExt . "_AppStore.ipa");
                    if (count($appstoreIPA) > 0) {
                        if (startsWith(basename($file), "zzz_")) {
                            $typeKey = "3";
                        } else {
                            $typeKey = "4";
                        }
                    }
                }
            }

            $distMode=$jsonReleaseType;

            $php_module_prefix = "../phpmodules";
            if (!file_exists($php_module_prefix)) {
                $php_module_prefix = "../../phpmodules";
            }

            // Removal script from original html snippet
            if (strpos($content, "remove_html_snippet")) {
                $tempStr = explode("remove_html_snippet", $content);
                $prefixStr = explode("\"><img", $tempStr[1]);
                $removalUrl = "$php_module_prefix/remove_html_snippet" . $prefixStr[0];

                $undoRemovalUrl = preg_replace("/remove_html_snippet/", "undo_remove_html_snippet", $removalUrl);
            }
            // Above is legacy, now we can pass filename as arguments, 2021.08.01
            if (true /*$isDebugMode*/) {
                $removalUrl = "$php_module_prefix/remove_html_snippet.php?os=$os&file=" . urlencode($basenameWithoutExt);
                $undoRemovalUrl = "$php_module_prefix/undo_remove_html_snippet.php?os=$os&file=" . urlencode($basenameWithoutExt);
                // Share script from original html snippet
                $shareUrl = "$php_module_prefix/distributions.php?os=$os&type=$distMode&file=" . urlencode($file);
            } else {
                // It's not plf'2'mini.company.com domain
                $inBoundPoint = "$frontEndProtocol://$frontEndPoint/$topPath";
                $removalUrl = "$inBoundPoint/phpmodules/remove_html_snippet.php?os=$os&file=" . urlencode($basenameWithoutExt);
                $undoRemovalUrl = "$inBoundPoint/phpmodules/undo_remove_html_snippet.php?os=$os&file=" . urlencode($basenameWithoutExt);
                // Share script from original html snippet
                $shareUrl = "$inBoundPoint/phpmodules/distributions.php?os=$os&type=$distMode&file=" . urlencode($file);
            }
            $resendingUrl = $shareUrl . "&resending=true";

            // START: Make HTML Snippet with JSON data
            $finalSnippet = "
<!-- " . $finalJson->{'appVersion'} . "." . $finalJson->{'buildVersion'} . " jenkins(" . $finalJson->{'buildNumber'} . ") START -->
<div class=\"item box_type$typeKey\"> <!-- 5타입 : box_type1(배포전), box_type2(배포후), box_type3(입고 검증전), box_type4(입고 검증후), box_type_del(삭제) -->
<div class=\"item_inner\">
";
            $finalSnippet .= "
<h2 class=\"tit_box\"><span class=\"txt\">
";
            if ($isDomesticQA && $typeKey != "_del") {
                $finalSnippet .= "<a href=\"$resendingUrl\" alt=\"". L::title_alt_resend_mail ."\">";
            }
            $finalSnippet .= "$versionTarget";
            if (strlen(trim($versionDetail)) > 0) {
                $finalSnippet .= "<br /><font size=1>$versionDetail</font>";
            }
            if ($isDomesticQA && $typeKey != "_del") {
                $finalSnippet .= "</a>";
            }
            $finalSnippet .= "</span></h2>";
            if ($isDomesticQA && $distMode == "release") {
                // 소스 코드 파일 접미사 확인
                $sourceFileSuffix = "-". $os ."_source.zip"; // 기본값 설정
                if (isset($json->{$os}->{$storeTarget}) && 
                    isset($json->{$os}->{$storeTarget}->{'sourceCodeFileSuffix'})) {
                    $sourceFileSuffix = $json->{$os}->{$storeTarget}->{'sourceCodeFileSuffix'};
                }
                
                $realSourceFile = $basenameWithoutExt . $sourceFileSuffix;
                
                // 소스 코드 경로 확인
                $sourcePath = "{OutputFolder}"; // 기본값 설정
                if (isset($json->{$os}->{$storeTarget}) && 
                    isset($json->{$os}->{$storeTarget}->{'sourceCodeAbsPath'})) {
                    $sourcePath = dirname($json->{$os}->{$storeTarget}->{'sourceCodeAbsPath'});
                }
            
                // documentRootPath가 정의되지 않았을 때를 대비한 초기화
                if (!isset($documentRootPath) || empty($documentRootPath)) {
                    $documentRootPath = $_SERVER['DOCUMENT_ROOT'];
                    if (empty($documentRootPath)) {
                        // 서버 환경 변수가 없는 경우 다른 방법으로 경로 찾기 시도
                        $scriptPath = dirname($_SERVER['SCRIPT_FILENAME']);
                        $requestUri = dirname($_SERVER['REQUEST_URI']);
                        $documentRootPath = str_replace($requestUri, '', $scriptPath);
                    }
                }
                
                $inBoundPoint = "$frontEndProtocol://$frontEndPoint";
                if ($sourcePath == "{OutputFolder}") {
                    $newSourcePath = parse_url($finalJson->{'urlPrefix'})['path'];
                    $realSourceFilePath = $documentRootPath ."/". $newSourcePath ."/". $realSourceFile;
                    $srcUrl = $finalJson->{'urlPrefix'} . "$realSourceFile";
                } else {
                    $realSourceFilePath = $sourcePath ."/". $realSourceFile;
                    $srcUrl = "$inBoundPoint/$realSourceFilePath";
                }
                if (isset($srcUrl)) {
                    $finalSnippet .= "<!--SOURCE_BOTTON --><a class=\"btn_src\" onclick=\"javascript:downloadSrc('". $srcUrl ."');\" alt=\"소스코드\"><span class=\"hide\">소스코드</span></a>";
                }
                $packageUrl = "$php_module_prefix/download_full_packages.php";
                $finalSnippet .= "<!--PACKAGE_BUTTON --><a class=\"btn_package\" onclick=\"javascript:downloadPackages('$packageUrl','". urldecode($file) ."','". $finalJson->{'appVersion'} ."','". $finalJson->{'buildVersion'} ."','$outBoundPoint','$realSourceFilePath');\" alt=\"Packages\"><span class=\"hide\">Packages</span></a>";
            }
            $finalSnippet .= "<!--SOURCE_BOTTON --><a class=\"btn_src\" onclick=\"javascript:downloadSrc('". $srcUrl ."');\" alt=\"소스코드\"><span class=\"hide\">소스코드</span></a>";
            $finalSnippet .= "<!--COPY_BOTTON --><a class=\"btn_copy\" onclick=\"copyToClip('[$versionTarget $versionDetail] ";
            $finalSnippet .= L::app_name ." $osName v" . $finalJson->{'appVersion'} . ".";
            $finalSnippet .= $finalJson->{'buildVersion'} . " (" . $finalJson->{'buildTime'} . "), Jenkins(";
            $finalSnippet .= $finalJson->{'buildNumber'} . ")')\" alt=\"배포문구복사\"><span class=\"hide\">복사</span></a>
<!--DIST_BOTTON --><a href=\"$shareUrl\" class=\"btn_share\" alt=\"공유\"><span class=\"hide\">공유</span></a>
<!--REMOVE_BOTTON--><a href=\"javascript:deleteFiles('$removalUrl','$outBoundPoint');\" class=\"btn_del\" alt=\"삭제\"><span class=\"hide\">삭제</span></a><a href=\"$undoRemovalUrl\" class=\"btn_re\"><span class=\"hide\">되돌리기</span></a>
<div class=\"cont\">
<span class=\"date\">" . $finalJson->{'buildTime'} . "</span>
<p class=\"stit\"><strong class=\"point_c\">". L::app_name ." ". $osName;
            $finalSnippet .= "</strong> <span>v" . $finalJson->{'appVersion'} . "." . $finalJson->{'buildVersion'};
            $finalSnippet .= "&nbsp;&nbsp;&nbsp;<font size=1 color=silver>jenkins(<b>" . $finalJson->{'buildNumber'} . "</b>)</font></span></p>";

            if (is_array($finalJson->{'files'})) {
                $finalSnippet .= "<ul class=\"list_down\">";

                for ($i = 0; $i < count($finalJson->{'files'}); $i++) {

                    $anItem = $finalJson->{'files'}[$i];

                    if (rtrim($anItem->{'file'}) != "") {
                        $binTitle = $anItem->{'title'};
                        if (!$isDomesticQA) {
                            if ($os == "ios") {
                                if ($jsonReleaseType == 'release') {
                                    if ($json->{$os}->{'AppStore'}->{'title'} == $binTitle) {
                                        if ($json->{$os}->{'AppStore'}->{'showToClient'} != true) continue;
                                    }
                                }
                                if ($json->{$os}->{'Adhoc'}->{'title'} == $binTitle) {
                                    if ($json->{$os}->{'Adhoc'}->{'showToClient'} != true) continue;
                                }
                                if ($json->{$os}->{'Enterprise'}->{'title'} == $binTitle) {
                                    if ($json->{$os}->{'Enterprise'}->{'showToClient'} != true) continue;
                                }
                            } else if ($os == "android") {
                                if ($jsonReleaseType == 'release') {
                                    if ($json->{$os}->{'GoogleStore'}->{'title'} == $binTitle) {
                                        if ($json->{$os}->{'GoogleStore'}->{'showToClient'} != true) continue;
                                    }
                                    if ($json->{$os}->{'OneStore'}->{'title'} == $binTitle) {
                                        if ($json->{$os}->{'OneStore'}->{'showToClient'} != true) continue;
                                    }
                                }
                                if ($json->{$os}->{'LiveServer'}->{'title'} == $binTitle) {
                                    if ($json->{$os}->{'LiveServer'}->{'showToClient'} != true) continue;
                                }
                                if ($json->{$os}->{'TestServer'}->{'title'} == $binTitle) {
                                    if ($json->{$os}->{'TestServer'}->{'showToClient'} != true) continue;
                                }
                            }
                        }

                        $downUrl = $anItem->{'file'};
                        $downSize = $anItem->{'size'};

                        if (!startsWith($downUrl, "http") &&
                            strpos($downUrl, 'android_signing.php') === false) {
                            $downUrl = $finalJson->{'urlPrefix'} . $downUrl;
                        }

                        if (strlen(rtrim($anItem->{'plist'})) > 0) {
                            $plistUrl = rtrim($anItem->{'plist'});
                            if (!startsWith($plistUrl, "http")) {
                                $plistUrl = $finalJson->{'urlPrefix'} . $plistUrl;
                            }
                            if (!startsWith($plistUrl, "http")) {
                                $plistUrl = $outBoundProtocol . '://' . $outBoundPoint . '/' . $topPath . '/' . $plistUrl;
                            } else {
                                $plistUrl = str_replace($frontEndProtocol, $outBoundProtocol, $plistUrl);
                                $plistUrl = str_replace($frontEndPoint, $outBoundPoint, $plistUrl);
                            }
                            $downUrl = "itms-services://?action=download-manifest&url=" . $plistUrl;
                        }

                        if (startsWith($downUrl, "http")) {
                            // It's not plf'2'mini.company.com domain
                            // Replace any protocol (http or https) with outbound protocol
                            $downUrl = preg_replace('/^(http|https|httpss):\/\//', $outBoundProtocol . '://', $downUrl);
                            $downUrl = str_replace($frontEndPoint, $outBoundPoint, $downUrl);
                            
                            $tempUrl = $downUrl;
                            if (isset($plistUrl)) {
                                $tempUrl = $plistUrl;
                            }
                            $tempHost = parse_url($tempUrl, PHP_URL_HOST);
                            $tempPort = parse_url($tempUrl, PHP_URL_PORT);
                            $exEndPoint = $tempHost;
                            if ($tempPort > 0) {
                                $exEndPoint .= ":" . $tempPort;
                            }
                            $downUrl = str_replace($exEndPoint, $outBoundPoint, $downUrl);
                        } else if (!startsWith($downUrl, "itms-services")) {
                            if (strpos($downUrl, 'android_signing.php') !== false && strpos($downUrl, 'android/') === false) {
                                $downUrl = $outBoundProtocol . '://' . $outBoundPoint . '/' . $topPath . '/android/' . $downUrl;
                            } else {
                                $downUrl = $outBoundProtocol . '://' . $outBoundPoint . '/' . $topPath . '/' . $downUrl;
                            }
                        }

                        if (strpos($downUrl, 'android_signing.php') !== false) {
                            if (! $isDomesticQA) { 
                                continue;
                            }

                            $finalURL = str_replace($outBoundProtocol, $frontEndProtocol, $downUrl);
                            $finalURL = str_replace($outBoundPoint, $frontEndPoint, $finalURL);
                            $array = explode('title=', $finalURL);
                            $apkFile = end($array);
                            $isGoogleExist = 0;
                            $isOneStoreExist = 0;

                            $unsignedPrefix = $json->{$os}->{'outputUnsignedPrefix'};
                            $googleSuffix = $json->{$os}->{'outputGoogleStoreSuffix'};
                            if (file_exists("$path/$unsignedPrefix$apkFile$googleSuffix")) {
                                $isGoogleExist = 1;
                            }
                            $oneSuffix = $json->{$os}->{'outputOneStoreSuffix'};
                            if (file_exists("$path/$unsignedPrefix$apkFile$oneSuffix")) {
                                $isOneStoreExist = 1;
                            }
                            $apkSignerPath = $json->{'android'}->{'androidHome'} . "/build-tools";
                            $apksignerCandidates = glob($apkSignerPath . "/*/apksigner");
                            if (is_array($apksignerCandidates)) {
                                $lastApkSigner = end($apksignerCandidates);
                            }
                            if (isset($lastApkSigner) && file_exists($lastApkSigner)) {
                                $anArray = explode('/', $lastApkSigner);
                                $apkSignerVersion = $anArray[count($anArray) - 2];
                                $finalURL = "javascript:androidSigning('$finalURL', '$apkFile', '$apkSignerVersion', $isGoogleExist, $isOneStoreExist, '$outBoundPoint');";
                            } else {
                                $apkSignerVersion = '0';
                                $finalURL = "javascript:androidSigning('$apkSignerPath', '$apkFile', '$apkSignerVersion', $isGoogleExist, $isOneStoreExist, '$outBoundPoint');";
                            }
                            if ($isGoogleExist || $isOneStoreExist) {
                                $downSize = "2차 난독화 수행";
                            }
                        } else {
                            $finalURL = "javascript:appDownloader('$downUrl');";
                            if (startsWith(ltrim($anItem->{'title'}), "Enterprise4Web")) {
                                $finalURL = "javascript:enterprise4web('$downUrl');";
                            }
                        }

                        $itemClassForAppStore = "";
                        $itemClassForAppStoreDesc = "";
                        $appStoreUploadLink = "";
                        // iOS Upload to App Store
                        if ($os == "ios" && $jsonReleaseType == 'release' && 
                            $json->{$os}->{'AppStore'}->{'uploadApp'}->{'enabled'} && 
                            $json->{$os}->{'AppStore'}->{'title'} == $binTitle) {
                            $itemClassForAppStore = "class=\"item_type2\"";
                            $itemClassForAppStoreDesc = "<!-- 20220119 item_type2 클래스 추가 -->";
                            $uploadAppLink = "appstore_upload.php?title=". $anItem->{'file'};
                            $uploadAppVersion = $finalJson->{'appVersion'} . "." . $finalJson->{'buildVersion'};
                            $appStoreUploadLink = "<a href=\"javascript:appStoreUploading('". $uploadAppLink ."','". $uploadAppVersion ."','" . "$frontEndProtocol://$frontEndPoint" . "','". $outBoundPoint ."');\" class=\"btn_$os\">" .strtoupper($os). " 배포 바로가기</a> <!-- 20220119 추가 -->";
                            // TODO: change button link after done uploaded to App Store, need to add AppID('1542294610') into config.json
                            // https://appstoreconnect.apple.com/apps/1542294610/testflight/ios
                        }
                        else if ($os == "ios" && $jsonReleaseType == 'release' && $json->{$os}->{'Adhoc'}->{'title'} == $binTitle) { // AdHoc
                            $pathArray = explode($topPath, $finalJson->{'urlPrefix'});
                            if (count($pathArray) < 2) {
                                $tempTopPath = explode('/', $topPath);
                                if (count($tempTopPath) > 1) {
                                    $pathArray = explode($tempTopPath[1], $finalJson->{'urlPrefix'});
                                }
                            }
                            $adhocFilename = str_replace('AdHoc.ipa', 'AdHoc_Debug.ipa', $anItem->{'file'});
                            $adhocFilePath = ".." . $pathArray[1] . $adhocFilename;
                            if (!file_exists($adhocFilePath)) {
                                $adhocFilePath = "../.." . $pathArray[1] . $adhocFilename;
                            }

                            if (file_exists($adhocFilePath)) {
                                $itemClassForAppStore = "class=\"item_type2\"";
                                $itemClassForAppStoreDesc = "<!-- 20220119 item_type2 클래스 추가 -->";
                                $downUrl = str_replace('AdHoc.plist', 'AdHoc_Debug.ipa', $downUrl);
                                $adhocDebugURL = "javascript:appDownloader('$downUrl');";
                                $appStoreUploadLink = "<a href=\"$adhocDebugURL\" class=\"btn_debug\" alt=\"ADHOC DEBUG 다운로드\">" .strtoupper($os). " ADHOC DEBUG 다운로드</a> <!-- 20220119 추가 -->";
                            }
                        }
                        
                        // Android download AAB Bundle and apk
                        if ($os == "android" && $jsonReleaseType == 'release' &&  $json->{$os}->{'GoogleStore'}->{'usingBundleAAB'}) {
                            $pathArray = explode($topPath, $finalJson->{'urlPrefix'});
                            $pathSuffix = '';
                            
                            if (count($pathArray) >= 2) {
                                $pathSuffix = $pathArray[1];
                            } else {
                                $tempTopPath = explode('/', $topPath);
                                if (count($tempTopPath) > 1) {
                                    $pathArray = explode($tempTopPath[1], $finalJson->{'urlPrefix'});
                                    if (count($pathArray) >= 2) {
                                        $pathSuffix = $pathArray[1];
                                    }
                                }
                            }
                            
                            $bundleFilename = str_replace('apk', 'aab', $anItem->{'file'});
                            
                            if (!empty($pathSuffix)) {
                                $bundleFilePath = ".." . $pathSuffix . $bundleFilename;
                                if (!file_exists($bundleFilePath)) {
                                    $bundleFilePath = "../.." . $pathSuffix . $bundleFilename;
                                }
                            } else {
                                // 경로를 찾을 수 없는 경우 대체 경로 사용
                                $bundleFilePath = "../android_distributions/" . $bundleFilename;
                                if (!file_exists($bundleFilePath)) {
                                    $bundleFilePath = "../../android_distributions/" . $bundleFilename;
                                }
                            }

                            if ($json->{$os}->{'GoogleStore'}->{'title'} == $binTitle) {
                                // 출력 파일 자체가 *.aab임
                                $itemClassForAppStore = "class=\"item_type2\"";
                                $itemClassForAppStoreDesc = "<!-- 20220119 item_type2 클래스 추가 -->";
                                $appStoreUploadLink = "<a href=\"$finalURL\" class=\"btn_$os\" alt=\"AAB 다운로드\">" .strtoupper($os). " AAB 다운로드</a> <!-- 20220119 추가 -->";
                                $finalURL = str_replace('aab', 'apk', $finalURL);
                            }
                            else if (substr($bundleFilename, 0, 7) === 'signed_' && file_exists($bundleFilePath)) {
                                // 2차 난독화 후 signing된 AAB를 표시함: 출력 파일은 *.apk임
                                $itemClassForAppStore = "class=\"item_type2\"";
                                $itemClassForAppStoreDesc = "<!-- 20220119 item_type2 클래스 추가 -->";
                                $aTempAABFile = str_replace('apk', 'aab', $finalURL);
                                $appStoreUploadLink = "<a href=\"$aTempAABFile\" class=\"btn_$os\" alt=\"signed AAB(2차 난독화) 다운로드\">" .strtoupper($os). " AAB 다운로드</a> <!-- 20220119 추가 -->";
                            }
                        }
                        $finalSnippet .= "
                            <li $itemClassForAppStore> $itemClassForAppStoreDesc
                                <a href=\"$finalURL\" class=\"btn_down\">
                                    <em class=\"txt1\">" . $anItem->{'title'} . "</em>
                                    <span class=\"bar\">|</span>
                                    <span class=\"txt2\">" . $downSize . "</span>               
                                    <span class=\"hide\">다운로드</span>
                                </a>
                                $appStoreUploadLink
                            </li>
                        ";
                    }
                }
                $finalSnippet .= "</ul>";
            }

            $finalSnippet .= "<!-- 히스토리 : 펼침 접힘 토글 -->
      <div class=\"history_area\">
      <button type=\"button\" class=\"btn\">History</button>
      <ul class=\"list\">\n";

            if (isset($finalJson->{'gitLastLogs'})) {
                $hideGitCommitter = false;
                if (isset($config->{'hideGitCommitter'})) {
                    $hideGitCommitter = $config->{'hideGitCommitter'};
                }
            
                if (!$isDomesticQA) { // 고객사용
                    if ($os == "ios") {
                        if (isset($json->{'ios'}->{'clientGitUrl'}) && strlen($json->{'ios'}->{'clientGitUrl'}) > 6) {
                            $gitBrowseUrl = $json->{'ios'}->{'clientGitUrl'};
                        } else {
                            $gitBrowseUrl = $json->{'ios'}->{'gitBrowseUrl'};
                        }
                    } else if ($os == "android") {
                        if (isset($json->{'android'}->{'clientGitUrl'}) && strlen($json->{'android'}->{'clientGitUrl'}) > 6) {
                            $gitBrowseUrl = $json->{'android'}->{'clientGitUrl'};
                        } else {
                            $gitBrowseUrl = $json->{'android'}->{'gitBrowseUrl'};
                        }
                    }
                } else { // 회사 내부용
                    if ($os == "ios") {
                        $gitBrowseUrl = $json->{'ios'}->{'gitBrowseUrl'};
                    } else if ($os == "android") {
                        $gitBrowseUrl = $json->{'android'}->{'gitBrowseUrl'};
                    }                    
                }

                $input = $finalJson->{'gitLastLogs'};
                for ($i = 0; $i < count($input); $i++) {
                    $gitItem = $input[$i];
                    $gitHash = $gitItem->{'hash'};
                    $gitDate = $gitItem->{'date'};
                    $gitComment = $gitItem->{'comment'};
                    if ($hideGitCommitter) {
                        $gitCommiter = " by ". L::company_name;
                    } else {
                        if (isset($gitItem->{'commiter'})) {
                            $gitCommiter = " by ". $gitItem->{'commiter'};
                        } else if (isset($gitItem->{'committer'})) {
                            $gitCommiter = " by ". $gitItem->{'committer'};
                        } else {
                            $gitCommiter = "";
                        }    
                    }
                    $commitId = $gitHash;
                    if ($jsonReleaseType == 'release') {
                        $commitId = $gitDate;
                    }
                    $finalSnippet .= "\t\t<li><span class=\"tit\"><a href=\"$gitBrowseUrl/$gitHash\">$commitId</a></span><p class=\"txt\">$gitComment$gitCommiter</p></li>\n";
                }
            } else {
                $input = $finalJson->{'gitLastLog'};
                $finalSnippet .= "\t\t$input\n";
            }
        $finalSnippet .= "\n</ul>
      </div>
      <!-- //히스토리 : 펼침 접힘 토글 -->
      </div>
      </div>
      </div>
      <!-- " . $finalJson->{'appVersion'} . "." . $finalJson->{'buildVersion'} . " jenkins(" . $finalJson->{'buildNumber'} . ") END -->
      ";

            // E N D: Make HTML Snippet with JSON data
        }
        // E N D: Fetch JSON data



        if ($isSearch) {
            if ($searchPattern == L::title_tab_qc_version && $typeKey >= 3 &&
                $typeKey != "_del" && $jsonReleaseType == 'release') {
                    $content = $finalSnippet;
                    $finalContents = $finalContents . $content;    
            }
            else if (stripos($content, "$searchPattern")) {
                if ($searchPattern == L::title_tab_qc_version && $typeKey < 3) {
                    continue;
                }
                $content = $finalSnippet;
                $finalContents = $finalContents . $content;
            } else {
                continue;
            }
        } else {
            $content = $finalSnippet;
            $finalContents = $finalContents . $content;
        }

        $finalSnippet = "";
    } //foreach

    return $finalContents;
}

function httpPost($url, $headers, $data, $isBianry)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    if (isset($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    if ($isBianry) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    } else {
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function httpGet($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function getLastJson($os) {
    global $topPath;
    global $frontEndProtocol;
    global $frontEndPoint;
    global $outBoundProtocol;
    global $outBoundPoint;

    $fileKey = "html";
  
    if ($os == "android") {
        $findingPath = realpath(__DIR__ . "/../android_distributions");
        if (!$findingPath) {
            $findingPath = realpath(__DIR__ . "/../../android_distributions");
        }
        $files = glob($findingPath ."/?.*/*.$fileKey");
    }
    else if ($os == "ios") {
        $findingPath = realpath(__DIR__ . "/../ios_distributions");
        if (!$findingPath) {
            $findingPath = realpath(__DIR__ . "/../../ios_distributions");
        }
        $files = glob($findingPath ."/?.*/*.$fileKey");
    }
  
    usort($files, function($a, $b) {
      return filemtime($a) < filemtime($b);
    });
  
    foreach($files as $file) {
        // $content = file_get_contents($file);
        $path = pathinfo($file, PATHINFO_DIRNAME);
        // $basename = basename($file);
        $basenameWithoutExt = basename($file, '.html');
  
        // 외부(고객사) 배포 페이지 //////////////////////////////////////////////////////////
        if (startsWith(basename($file), "zzz_")) {
            // 아직 배포되지 않은 페이지
            continue;
        }
        // START: Fetch JSON data
        if (startsWith($basenameWithoutExt, "zzz_")) {
            // $tmpOut = explode("zzz_", $basenameWithoutExt);
            // $basenameWithoutExt = $tmpOut[1];
            $basenameWithoutExt = substr($basenameWithoutExt, 4);
    
            //echo "<H1><font color=pink>JSON FILE:::$basenameWithoutExt</font></H1>";
        }
        $jsonfile = $basenameWithoutExt . ".json";
    
        if (file_exists("$path/$jsonfile")) {
            $jsonStr = file_get_contents("$path/$jsonfile");
            $finalJson = json_validate($jsonStr);
    
            $param["appVersion"] = $finalJson->{'appVersion'};
            $param["buildVersion"] = $finalJson->{'buildVersion'};
            $param["buildNumber"] = $finalJson->{'buildNumber'};
            $param["buildTime"] = $finalJson->{'buildTime'};
            if (is_array($finalJson->{'files'})) {
                for ($i=0; $i < count($finalJson->{'files'}); $i++) {
                    $anItem = $finalJson->{'files'}[$i];
        
                    if (rtrim($anItem->{'file'}) != "") {
                        $downUrl = $anItem->{'file'};
                        if (!startsWith($downUrl, "http")) {
                            $downUrl = $finalJson->{'urlPrefix'} . $downUrl;
                        }
                        if ($frontEndPoint != $outBoundPoint) {
                            $downUrl = str_replace($frontEndProtocol, $outBoundProtocol, $downUrl);
                            $downUrl = str_replace($frontEndPoint, $outBoundPoint, $downUrl);
                        }
                        if ($os == "android" && endsWith($downUrl, "aab")) {
                            $downUrl = str_replace('aab', 'apk', $downUrl);
                        }
                        if (rtrim($anItem->{'plist'}) != "") {
                            $plistUrl = rtrim($anItem->{'plist'});
                            if (!startsWith($plistUrl, "http")) {
                                $plistUrl = $finalJson->{'urlPrefix'} . $plistUrl;
                            }
                            if (!startsWith($plistUrl, "http")) {
                                $plistUrl = $outBoundProtocol . '://' . $outBoundPoint . '/' . $topPath . '/' . $plistUrl;
                            } else {
                                $plistUrl = str_replace($frontEndProtocol, $outBoundProtocol, $plistUrl);
                                $plistUrl = str_replace($frontEndPoint, $outBoundPoint, $plistUrl);
                            }
                            $downUrl = "itms-services://?action=download-manifest&url=" . $plistUrl;
                        }
                        if (rtrim($downUrl) != "") {
                            $param["downUrl"] = $downUrl;
                            break;
                        }
                    }
                }
            }
            //echo "DEBUG:::" . var_dump($param);
            return $param;
        }
    }
}
  
?>
