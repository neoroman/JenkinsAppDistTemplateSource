<?php
require(__DIR__ . '/phpmodules/utils/json.php');
if (file_exists(__DIR__ . "/../lang/default.json")) {
    $defaultLang = __DIR__ . "/../lang/default.json";
    $langPath = __DIR__ . "/../lang";
} else if (file_exists(__DIR__ . "/lang/default.json")) {
    $defaultLang = __DIR__ . "/lang/default.json";
    $langPath = __DIR__ . "/lang";
} else {
    exit(101);
}
if (file_exists($defaultLang)) {
    $jsonStr = file_get_contents($defaultLang);
    $json = json_validate2($jsonStr, false);
    $lang = $json->{'LANGUAGE'};
    $langFile = $langPath . '/lang_'. $lang . '.json';
    if (!file_exists($langFile)) {
        header('Location: setup.php');
        exit(101);
    }
    $class_i18n_path = __DIR__ . '/phpmodules/utils/i18n.class.php';
    if (!file_exists($class_i18n_path)) {
        $class_i18n_path = __DIR__ . '/../phpmodules/utils/i18n.class.php'; 
    }
    if (file_exists($class_i18n_path)) {
        require_once $class_i18n_path;
        $i18n = new i18n();
        $i18n->setCachePath(__DIR__  . '/../langcache');
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
if (file_exists(__DIR__ . "/../config/config.json")) {
    $jsonStr = file_get_contents(__DIR__ . "/../config/config.json");
    $json = json_validate2($jsonStr, false);
} else if (file_exists(__DIR__ . "/config/config.json")) {
    $jsonStr = file_get_contents(__DIR__ . "/config/config.json");
    $json = json_validate2($jsonStr, false);
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
}
$frontEndProtocol = $config->{'frontEndProtocol'};
$frontEndPoint = $config->{'frontEndPoint'};
$outBoundProtocol = $config->{'outBoundProtocol'};
$outBoundPoint = $config->{'outBoundPoint'};
$boanEndPoint = "https://boan.company.com:4000";
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
$lastTopPath=$topPaths[1];
$testTopPath="test/$lastTopPath";
//    strpos($_SERVER['HTTP_HOST'], $frontEndPoint) !== false &&
if (isset($_SERVER['HTTP_HOST']) &&
    isset($_SERVER['REQUEST_URI']) &&
    strpos($_SERVER['REQUEST_URI'], $testTopPath) !== false) {
    $isDebugMode = true;
    $debugMailRecipient = $userGIT->{'email'};
    $topPath = $testTopPath;
    $outBoundProtocol = $frontEndProtocol;
    $outBoundPoint = $frontEndPoint;
    $boanEndPoint = "https://boan.company.com:4040";
}
elseif (isset($_SERVER['SERVER_NAME']) &&
        strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
    $dirArray = explode('/', getcwd());
    $topPath = implode('/', array_slice($dirArray,-2,2,false));
    $documentRootPath = str_replace($topPath, '', $documentRootPath);
}

$root = "$documentRootPath";
$inUrl = "$frontEndProtocol://$frontEndPoint";
$outUrl = "$outBoundProtocol://$outBoundPoint";
?>
