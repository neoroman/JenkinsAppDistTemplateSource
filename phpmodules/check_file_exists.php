<?php
header('Content-Type: application/json');

// Input validation
if (!isset($_GET["appVer"]) || !isset($_GET["buildVer"]) || !isset($_GET["srcPath"])) {
    die(json_encode(["error" => "Required parameters are missing"]));
}

$version = $_GET["appVer"];
$build = $_GET["buildVer"];
$srcPath = $_GET["srcPath"];

// Parse base path and platform from srcPath
$pathParts = explode('/', $srcPath);
preg_match('/-(\w+)_source\.zip$/', end($pathParts), $platformMatch);
$platform = isset($platformMatch[1]) ? (strtolower($platformMatch[1]) === 'ios' ? 'iOS' : 'Android') : 'Android';

// Get the base directory
$versionIndex = array_search($version, $pathParts);
$basePath = implode('/', array_slice($pathParts, 0, $versionIndex + 1));

// Format version and create expected path
$fileVersion = str_replace('.', '_', $version);
$expectedZipPath = "{$basePath}/{$fileVersion}_{$build}-{$platform}.zip";

// Return result
echo json_encode([
    "exists" => file_exists($expectedZipPath),
    "path" => $expectedZipPath
]);
?>
