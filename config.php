<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);

$src_top_path = __DIR__;
require($src_top_path . '/phpmodules/utils/json.php');
$defaultLang = $src_top_path . "/../lang/default.json";
if (!file_exists($defaultLang)) {
    copy($src_top_path . "/lang/default.json", $defaultLang);
    chmod($defaultLang, 0777);
}
if (file_exists($defaultLang)) {
    $jsonStr = file_get_contents($defaultLang);
    $json = json_validate2($jsonStr, false);
    $lang = $json->{'LANGUAGE'};
    $langFilename = 'lang_'. $lang . '.json';
    $langFile = $src_top_path . '/../lang/'. $langFilename;
    if (!file_exists($langFile)) {
        copy($src_top_path . '/lang/'. $langFilename . '.default', $langFile);
        chmod($langFile, 0777);
    }
    $class_i18n_path = $src_top_path . '/phpmodules/utils/i18n.class.php';
    $langCachePath = $src_top_path . '/langcache';
    if (file_exists($class_i18n_path)) {
        require_once $class_i18n_path;
        $i18n = new i18n();
        $i18n->setCachePath($langCachePath);
        $i18n->setFilePath($langFile); // language file path
        $i18n->setLangVariantEnabled(true); // trim region variant in language codes (e.g. en-us -> en)
        $i18n->setFallbackLang('ko');
        $i18n->setPrefix('L');
        $i18n->setForcedLang('ko'); // force Korean, even if another user language is available
        $i18n->setSectionSeparator('_');
        $i18n->setMergeFallback(false); // make keys available from the fallback language
        $i18n->init();
    }
}
// ----------------------------------------------------------
$jsonConfig = $src_top_path . "/../config/config.json";
$originalJsonConfig = $src_top_path . "/config/config.json.default";
if (file_exists($jsonConfig)) {
    $jsonStr = file_get_contents($jsonConfig);
    if (file_exists($originalJsonConfig)) {
        $orgJsonStr = file_get_contents($originalJsonConfig);
        $tempJson = json_encode(array_merge(json_validate2($orgJsonStr, true), json_validate2($jsonStr, true)));
        $json = json_validate2($tempJson, false);
    } else {
        $json = json_validate2($jsonStr, false);
    }
} else if (file_exists($originalJsonConfig)) {
    copy($src_top_path . "/config/config.json.default", $jsonConfig);
    $jsonStr = file_get_contents($jsonConfig);
    $json = json_validate2($jsonStr, false);

    // TODO: need to find out apache, httpd.conf, and DocumentRoot
    $command = 'apachectl';
    $output = shell_exec('which ' . escapeshellarg($command) . ' 2>&1');
    if (strpos($output, 'not found') === false) {
        // echo 'The command ' . $command . ' exists at ' . trim($output);
        $apache_path = shell_exec("$command -V | grep SERVER_CONFIG_FILE | sed -e 's/.*=\"\(.*\)\"/\1/'" . ' 2>&1');
        if (file_exists($apache_path)) {
            $keyword = "DocumentRoot";
            $documentRootFromHttpConf = shell_exec("grep -R $keyword ". $apache_path . "| grep $apache_path: $keyword". ' | sed -e "s/^.*\"\(.*\)\"$/\1/" | awk "{print $2}" | tr -d "\""' . ' 2>&1');
        }
    }

} else {
    header('Location: setup.php');
    exit(101);
}
$userInfo = $json->{'users'};
$userAPP = $userInfo->{'app'};
$userQC = $userInfo->{'qc'};
$userGIT = $userInfo->{'git'};
// ----------------------------------------------------------
// Users Dictionary
$userDict = [
    $userAPP->{'userId'} => [
        "userId" => $userAPP->{'userId'},
        "password" => $userAPP->{'password'},
        "status" => $userAPP->{'status'},
        "email" => $userAPP->{'email'},
        ],
    $userQC->{'userId'} => [
        "userId" => $userQC->{'userId'},
        "password" => $userQC->{'password'},
        "status" => $userQC->{'status'},
        "email" => $userQC->{'email'},
        ],
];
$isDebugMode = false;
if (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
    $isDebugMode = true;
    $config = $json->{'development'};
} else {
    $config = $json->{'production'};
}
// ----------------------------------------------------------
if (isset($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
    $documentRootPath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
} else if (isset($_SERVER['DOCUMENT_ROOT'])) {
    $documentRootPath = $_SERVER['DOCUMENT_ROOT'];
} else if (isset($documentRootFromHttpConf)) {
    $documentRootPath = $documentRootFromHttpConf;
}
$frontEndProtocol = $config->{'frontEndProtocol'};
$frontEndPoint = $config->{'frontEndPoint'};
$outBoundProtocol = $config->{'outBoundProtocol'};
$outBoundPoint = $config->{'outBoundPoint'};
$boanEndPoint = "https://boan.company.com:4000";
if (isset($config->{'urlLoginRemoteAPI'})) {
    $boanEndPoint = $config->{'urlLoginRemoteAPI'};
}
// ----------------------------------------------------------
$topPath = $config->{'topPath'};
$outputPrefix = $config->{'outputPrefix'};
// ----------------------------------------------------------
$usingAndroid = $json->{'android'}->{'enabled'};
$androidJenkinsWorkspace = $json->{'android'}->{'jenkinsWorkspace'};
$usingiOS = $json->{'ios'}->{'enabled'};
$iOSJenkinsWorkspace = $json->{'ios'}->{'jenkinsWorkspace'};
// ----------------------------------------------------------
$usingLogin = $config->{'usingLogin'};
$usingLoginRemoteAPI = $config->{'usingLoginRemoteAPI'};
$usingMySQL = $config->{'usingMySQL'};
$usingPreviousVersion = $config->{'usingPreviousVersion'};
$topPathPreviousVersion = $config->{'topPathPreviousVersion'};
// ----------------------------------------------------------
$topPaths=explode("/", $topPath);
$lastTopPath=count($topPaths) > 1 ? $topPaths[1] : $topPaths[0];
$testTopPath="test/$lastTopPath";
if (isset($_SERVER['HTTP_HOST']) &&
    isset($_SERVER['REQUEST_URI']) &&
    strpos($_SERVER['REQUEST_URI'], $testTopPath) !== false) {
    $isDebugMode = true;
    $debugMailRecipient = $userGIT->{'email'};
    $topPath = $testTopPath;
    if (isset($json->{'development'}->{'outBoundProtocol'}) && $json->{'development'}->{'outBoundProtocol'} == 'http') {
        $outBoundProtocol = $frontEndProtocol;
    }
    if (isset($json->{'development'}->{'outBoundPoint'}) && $json->{'development'}->{'outBoundProtocol'} == 'localhost') {
        $outBoundPoint = $frontEndPoint;
    }
    if (isset($json->{'development'}->{'urlLoginRemoteAPI'})) {
        $boanEndPoint = $json->{'development'}->{'urlLoginRemoteAPI'};
    }
}
elseif (isset($_SERVER['SERVER_NAME']) &&
        strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
    $dirArray = explode('/', getcwd());
    $topPath = implode('/', array_slice($dirArray,-2,2,false));
    if (strpos($output, 'src/phpmodules') !== false) {
        $topPath = implode('/', array_slice($dirArray,-4,2,false));
    }
    $documentRootPath = str_replace($topPath, '', $documentRootPath);
}

$root = "$documentRootPath";
$inUrl = "$frontEndProtocol://$frontEndPoint";
$outUrl = "$outBoundProtocol://$outBoundPoint";
?>
