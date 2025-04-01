<?php
// Increase execution time for large operations
set_time_limit(600);

// Include configuration if not already loaded
if (!class_exists('i18n')) {
    $configPaths = [__DIR__ . '/../config.php', __DIR__ . '/../../config.php'];
    foreach ($configPaths as $path) {
        if (file_exists($path)) {
            require_once($path);
            break;
        }
    }
}

global $topPath, $inUrl, $outUrl, $documentRootPath;

// Function to filter files based on version and extension pattern
function filterBinaryByPattern($directory, $filePattern) {
    $files = glob($directory . DIRECTORY_SEPARATOR . "*.{apk,ipa,aab,png}", GLOB_BRACE);
    return array_filter($files, function ($file) use ($filePattern) {
        return preg_match($filePattern, basename($file));
    });
}

// Create directory structure and copy files
function createDirectoryTree($baseDir, $isAndroid, $sourceDir, $sourceFilename) {
    global $documentRootPath, $fileVersion, $version, $build, $buildDate;

    $folders = $isAndroid
        ? ["0_ObfuscationData", "1_Android-Release-{$fileVersion}_{$build}", "2_Android-Debug", "3_Android-Source", "4_InternalQCResults", "5_SAM_Logs", "6_AppFunctionalDocs"]
        : ["0_ObfuscationData", "1_iOS-Release-{$fileVersion}_{$build}", "2_iOS-Debug", "3_iOS-Source", "4_InternalQCResults", "5_SAM_Logs", "6_AppFunctionalDocs"];

    foreach ($folders as $folder) {
        $folderPath = $baseDir . DIRECTORY_SEPARATOR . $folder;

        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        file_put_contents($folderPath . DIRECTORY_SEPARATOR . '.keep', '');

        $filesToCopy = [];
        $filePattern = ''; // Initialize for each case

        switch (true) {
            case strpos($folder, "0_ObfuscationData") === 0:
                $filePattern = '/.*' . preg_quote($version, '/') . '\(' . preg_quote($build, '/') . '\).*' . preg_quote($buildDate, '/') . '.*\.png$/i';
                $filesToCopy = filterBinaryByPattern($sourceDir, $filePattern);
                break;

            case strpos($folder, "1_") === 0:
                $filePattern = $isAndroid
                    ? '/^signed.*' . preg_quote($version, '/') . '\(' . preg_quote($build, '/') . '\).*' . preg_quote($buildDate, '/') . '.*-release\.(apk|aab)$/i'
                    : '/.*' . preg_quote($version, '/') . '\(' . preg_quote($build, '/') . '\).*' . preg_quote($buildDate, '/') . '.*(AdHoc|AppStore)\.ipa$/i';
                $filesToCopy = filterBinaryByPattern($sourceDir, $filePattern);
                break;

            case strpos($folder, "2_") === 0:
                $filePattern = $isAndroid
                    ? '/.*' . preg_quote($version, '/') . '\(' . preg_quote($build, '/') . '\).*' . preg_quote($buildDate, '/') . '.*-(GoogleStore|OneStore)-debug\.apk$/i'
                    : '/.*' . preg_quote($version, '/') . '\(' . preg_quote($build, '/') . '\).*' . preg_quote($buildDate, '/') . '.*Debug\.ipa$/i';
                $filesToCopy = filterBinaryByPattern($sourceDir, $filePattern);
                break;

            case strpos($folder, "3_") === 0:
                if (isset($_GET["srcPath"])) {
                    $srcPathRelative = str_replace($documentRootPath, '', $_GET["srcPath"]);
                    $sourceZipPath = $documentRootPath . DIRECTORY_SEPARATOR . ltrim($srcPathRelative, DIRECTORY_SEPARATOR);
                    if (is_file($sourceZipPath) && is_readable($sourceZipPath)) {
                        $filesToCopy = [$sourceZipPath];
                    }
                }
                break;
        }

        // Write the file pattern to _dest_.txt for debugging
        file_put_contents($sourceDir .'/'. $sourceFilename .'_pattern.txt', "Folder: $folder\nFile Pattern: $filePattern\n\n", FILE_APPEND);

        foreach ($filesToCopy as $file) {
            $destinationFilePath = $folderPath . DIRECTORY_SEPARATOR . basename($file);
            if (!copy($file, $destinationFilePath)) {
                echo "Failed to copy file: $file<br>";
            }
        }
    }
}

// Create ZIP file
function createZipFile($baseDir, $zipFilename, $desiredFolderName) {
    $zip = new ZipArchive();
    if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die("Failed to create ZIP file: $zipFilename");
    }

    $filesToZip = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($filesToZip as $file) {
        // $zip->addFile($file->getRealPath(), substr($file->getRealPath(), strlen($baseDir) + 1));
        // $relativePath = substr($file->getRealPath(), strlen($baseDir) - strlen(basename($baseDir)) - 1);
        // Adjust the relative path to remove unwanted top-level folder
        // $relativePath = substr($file->getRealPath(), strlen($baseDir) + 1); // "+1" removes the trailing slash
        // Adjust the relative path to include the desired folder name
        $relativePath = $desiredFolderName . '/' . substr($file->getRealPath(), strlen($baseDir) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);

        $zip->addFile($file->getRealPath(), $relativePath);
    }

    $zip->close();
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($filePath) ? deleteDirectory($filePath) : unlink($filePath);
    }
    rmdir($dir);
}

function extractBuildDate($filename) {
    // Regex to match the build date, which is 6 digits at the end of the string
    if (preg_match('/_(\d{6})$/', $filename, $matches)) {
        return $matches[1];  // This will be the build date (e.g., "250115")
    }
    return null;  // Return null if no date is found
}

// Input validation
if (!isset($_GET["input_filename"])) {
    die("Required parameters are missing.");
}

$inputFilename = $_GET["input_filename"];
$isAndroid = strpos($inputFilename, 'android_distributions') !== false;
$keyDistPath = $isAndroid ? "android_distributions" : "ios_distributions";
$sourceDir = realpath(__DIR__ . "/../$keyDistPath") ?: realpath(__DIR__ . "/../../$keyDistPath");
$sourceSuffixPath = dirname(explode($keyDistPath, $inputFilename)[1]);
$sourceFilename = pathinfo(basename($inputFilename))['filename'];
// Use regex to extract version, build, and build date
preg_match('/[a-zA-Z0-9_]+(?:[\._])([\d]+\.[\d]+\.[\d]+)\((\d+)\)_(\d{6})/', $sourceFilename, $matches);
// Extract version, build, and build date
$version = isset($matches[1]) ? $matches[1] : (isset($_GET["appVer"]) ? $_GET["appVer"] : '');
$build = isset($matches[2]) ? $matches[2] : (isset($_GET["buildVer"]) ? $_GET["buildVer"] : '');
$buildDate = isset($matches[3]) ? $matches[3] : extractBuildDate($sourceFilename);
$fileVersion = str_replace('.', '_', $version);
$filenameOnly = "{$fileVersion}_{$build}-" . ($isAndroid ? "Android" : "iOS");
$zipFilename =  "{$filenameOnly}.zip";
$tmpBaseDir = "/tmp/{$topPath}";
$tmpTargetDir = "{$tmpBaseDir}/{$filenameOnly}";
$zipFilePath = "{$tmpBaseDir}/$zipFilename";
$finalBaseDir = "{$sourceDir}/$sourceSuffixPath";
$finalZipPath = "{$finalBaseDir}/$zipFilename";
file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "Source Filename: $sourceFilename\n\n", FILE_APPEND);
if (isset($folder) && isset($filePattern)) {
    file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "Folder: $folder\nFile Pattern: $filePattern\n\n", FILE_APPEND);
}
file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "Folder: $folder\nFile Pattern: $filePattern\n\n", FILE_APPEND);
if (isset($version)) {
    file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "version: $version\n", FILE_APPEND);
}
if (isset($build)) {
    file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "build: $build\n", FILE_APPEND);
}
file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "buildDate: $buildDate\n", FILE_APPEND);
file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "fileVersion: $fileVersion\n\n", FILE_APPEND);
file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "filenameOnly: $filenameOnly\n", FILE_APPEND);
file_put_contents($finalBaseDir .'/'. $sourceFilename .'_pattern.txt', "finalZipPath: $finalZipPath\n\n", FILE_APPEND);

// Check if regenerate parameter is set to 1
if (isset($_GET["regenerate"]) && $_GET["regenerate"] === "1") {
    if (file_exists($finalZipPath)) {
        unlink($finalZipPath);
    }
}

if (!is_file($finalZipPath)) {
    if (!is_dir($tmpBaseDir)) mkdir($tmpBaseDir, 0777, true);
    if (is_dir($tmpTargetDir)) deleteDirectory($tmpTargetDir);

    createDirectoryTree($tmpTargetDir, $isAndroid, $finalBaseDir, $sourceFilename);
    createZipFile($tmpTargetDir, $zipFilePath, $filenameOnly);

    if (!rename($zipFilePath, $finalZipPath)) {
        die("Failed to move ZIP file to final location.");
    }
}

function downloadZipFile($outputZipFile) {
    global $inUrl, $topPath, $keyDistPath, $sourceSuffixPath, $zipFilename;

    if (!file_exists($outputZipFile)) die("ZIP file does not exist: $outputZipFile");

    // Clear output buffer and send headers for download
    ob_end_clean();

    $realDownloadUrl = $inUrl . DIRECTORY_SEPARATOR . $topPath . DIRECTORY_SEPARATOR . $keyDistPath . DIRECTORY_SEPARATOR . $sourceSuffixPath . DIRECTORY_SEPARATOR . $zipFilename;
    header('Refresh: 0; url=' . $realDownloadUrl);

    echo "<script type='text/javascript'>
        setTimeout(function() {
            window.location.href = '{$_SERVER["HTTP_REFERER"]}';
        }, 3000);
    </script>";
    exit;
}

downloadZipFile($finalZipPath);

?>