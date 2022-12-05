<?php
require(__DIR__ . '/utils/json.php');
$class_i18n_path = __DIR__ . '/utils/i18n.class.php';
if (!file_exists($class_i18n_path)) {
    $class_i18n_path = __DIR__ . '../utils/i18n.class.php'; 
}
if (file_exists($class_i18n_path)) {
    require_once $class_i18n_path;
    $i18n = new i18n();
    $i18n->setCachePath(__DIR__  . '/langcache');
    $i18n->setFilePath(__DIR__  . '/lang/lang_{LANGUAGE}.json'); // language file path
    $i18n->setLangVariantEnabled(true); // trim region variant in language codes (e.g. en-us -> en)
    $i18n->setFallbackLang('ko');
    $i18n->setPrefix('L');
    $i18n->setForcedLang('ko'); // force Korean, even if another user language is available
    $i18n->setSectionSeparator('_');
    $i18n->setMergeFallback(false); // make keys available from the fallback language
    $i18n->init();
}
// ----------------------------------------------------------
$jsonFile = __DIR__ . "/config/config.json";
if (file_exists($jsonFile)) {
    $jsonStr = file_get_contents($jsonFile);
    $json = json_validate($jsonStr);
} else {
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
$documentRootPath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
$frontEndProtocol = $config->{'frontEndProtocol'};
$frontEndPoint = $config->{'frontEndPoint'};
$outBoundProtocol = $config->{'outBoundProtocol'};
$outBoundPoint = $config->{'outBoundPoint'};
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
if (isset($_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_HOST'], $frontEndPoint) !== false &&
    isset($_SERVER['REQUEST_URI']) &&
    strpos($_SERVER['REQUEST_URI'], $testTopPath) !== false) {
    $isDebugMode = true;
    $debugMailRecipient = $userGIT->{'email'};
    $topPath = $testTopPath;
    $outBoundProtocol = $frontEndProtocol;
    $outBoundPoint = $frontEndPoint;
}
elseif (isset($_SERVER['SERVER_NAME']) &&
        strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) {
    $dirArray = explode('/', getcwd());
    $topPath = implode('/', array_slice($dirArray,-2,2,false));
    $documentRootPath = str_replace($topPath, '', $documentRootPath);
}

$root = "${documentRootPath}";
$inUrl = "${frontEndProtocol}://${frontEndPoint}";
$outUrl = "${outBoundProtocol}://${outBoundPoint}";
?>
