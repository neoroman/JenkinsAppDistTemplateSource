<?php
if (!class_exists('i18n')) {
    if (file_exists(__DIR__ .'/../config.php')) {
        require_once(__DIR__ . '/../config.php');
    }
    else if (file_exists(__DIR__ .'/../../config.php')) {
        require_once(__DIR__ . '/../../config.php');
    }
}
global $topPath, $inUrl, $$outUrl;

function createDirectoryTree($baseDir, $isAndroid = false, $version, $build, $sourceDir, $sourceFile) {
    global $topPath;
    
    // 폴더 구조 정의
    $folders = $isAndroid ? [
        "0_ObfuscationData",
        "1_Android-Release-". $version ."_" . $build,
        "2_Android-Debug",
        "3_Android-Source",
        "4_InternalQCResults",
        "5_SAM_Logs",
        "6_AppFunctionalDocs"
    ] : [
        "0_ObfuscationData",
        "1_iOS-Release-". $version ."_" . $build,
        "2_iOS-Debug",
        "3_iOS-Source",
        "4_InternalQCResults",
        "5_SAM_Logs",
        "6_AppFunctionalDocs"
    ];

    // 폴더 생성
    foreach ($folders as $folder) {
        $folderPath = $baseDir . DIRECTORY_SEPARATOR . $folder;
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true); // 경로 생성
        }
        // .keep 파일 생성
        $keepFilePath = $folderPath . DIRECTORY_SEPARATOR . '.keep';
        if (!file_exists($keepFilePath)) {
            file_put_contents($keepFilePath, ''); // 빈 파일 생성
        }
        // 파일 복사: 각 폴더에 특정 확장자의 파일 복사
        $filesToCopy = [];
        switch ($folder) {
            case strpos($folder, "0_ObfuscationData") === 0:
                $filesToCopy = glob($sourceDir . DIRECTORY_SEPARATOR . "*.png"); // PNG 파일 복사
                break;
            case strpos($folder, "1_") === 0:
                if (!isset($sourceFile) || empty($sourceFile)) {
                    $sourceFile = ".*"; // 기본 값 설정
                }
                // 특정 확장자 및 파일명 패턴 검색 및 복사 (대소문자 구분 없음)
                $allFiles = scandir($sourceDir);
                $filesToCopy = array_filter($allFiles, function ($file) use ($sourceFile) {
                    return preg_match('/' . preg_quote($sourceFile) . '.*-release\.(apk|aab)$|adhoc\.ipa$|appstore\.ipa$/i', $file);
                });

                // 전체 경로를 포함하도록 파일 경로 변환
                $filesToCopy = array_map(function ($file) use ($sourceDir) {
                    return $sourceDir . DIRECTORY_SEPARATOR . $file;
                }, $filesToCopy);
                break;
            case strpos($folder, "2_") === 0:
                if (!isset($sourceFile) || empty($sourceFile)) {
                    $sourceFile = ".*"; // 기본 값 설정
                }
                $allFiles = scandir($sourceDir);
                $filesToCopy = array_filter($allFiles, function ($file) use ($sourceFile) {
                    return preg_match('/' . preg_quote($sourceFile) . '.*debug\.(apk|ipa)$/i', $file);
                });

                // 전체 경로를 포함하도록 파일 경로 변환
                $filesToCopy = array_map(function ($file) use ($sourceDir) {
                    return $sourceDir . DIRECTORY_SEPARATOR . $file;
                }, $filesToCopy);
                break;
            case strpos($folder, "3_") === 0:
                // 소스 위치
                if (isset($_GET["srcPath"])) {
                    $sourceZipPath = explode($topPath, $sourceDir)[0] . DIRECTORY_SEPARATOR . $_GET["srcPath"];
                    if (is_file($sourceZipPath) && is_readable($sourceZipPath)) {
                        $filesToCopy = $sourceZipPath;
                    }
                }
                break;
            case strpos($folder, "4_") === 0:
                // $filesToCopy = glob($sourceDir . DIRECTORY_SEPARATOR . "*.pdf"); // PDF 파일 복사
                break;
            case strpos($folder, "5_") === 0:
                // $filesToCopy = glob($sourceDir . DIRECTORY_SEPARATOR . "*.json"); // JSON 파일 복사
                break;
            case strpos($folder, "6_") === 0:
                // $filesToCopy = glob($sourceDir . DIRECTORY_SEPARATOR . "*.docx"); // DOCX 파일 복사
                break;
            default:
                break;
        }

        // 파일 복사 작업 수행
        foreach ($filesToCopy as $file) {
            $fileName = basename($file); // 파일 이름만 추출
            $destinationFilePath = $folderPath . DIRECTORY_SEPARATOR . $fileName;

            if (!copy($file, $destinationFilePath)) {
                echo "Failed to copy file: $file<br>";
            }
        }        
    }
}

function createZipFile($baseDir, $zipFilename) {
    // ZIP 아카이브 객체 생성
    $zip = new ZipArchive();

    // ZIP 파일 열기
    if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die("Failed to create ZIP file: $zipFilename");
    }

    // 디렉터리와 파일을 재귀적으로 탐색
    $filesToZip = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($filesToZip as $file) {
        $filePath = $file->getRealPath();

        // 상대 경로를 `baseDir` 기준으로 계산
        $relativePath = substr($filePath, strlen($baseDir) + 1);

        // 파일 존재 여부와 읽기 가능 여부 확인
        if (is_file($filePath) && is_readable($filePath)) {
            // ZIP 파일에 추가
            if (!$zip->addFile($filePath, $relativePath)) {
                echo "Failed to add file to ZIP: $filePath<br>";
            }
        } else {
            echo "Skipped (not a file or unreadable): $filePath<br>";
        }
    }

    // ZIP 닫기
    $zip->close();

    // ZIP 파일 존재 여부 확인
    if (!file_exists($zipFilename)) {
        die("Failed to create ZIP file at the specified location: $zipFilename");
    }
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($filePath) ? deleteDirectory($filePath) : unlink($filePath);
    }
    rmdir($dir);
}

// input_filename 처리
if (isset($_GET["input_filename"])) {
    $inputFilename = $_GET["input_filename"];
} else {
    die("input_filename not provided.");
}

// iOS 또는 Android 여부 확인
$isAndroid = strpos($inputFilename, 'android_distributions') !== false;

// 기본 경로 설정
if ($isAndroid) {
    $keyDistPath = "android_distributions";
} else {
    $keyDistPath = "ios_distributions";
}
$sourceDir = realpath(__DIR__ . "/../$keyDistPath");
if (!$sourceDir) {
    $sourceDir = realpath(__DIR__ . "/../../$keyDistPath");
}
$sourceSuffixPath = dirname(explode($keyDistPath, $inputFilename)[1]);
$sourceFilename = explode('.', basename($inputFilename))[0];

// input_filename에서 버전 및 빌드 번호 추출
if (isset($_GET["appVer"]) && isset($_GET["buildVer"])) {
    $version = str_replace('.', '_', $_GET["appVer"]); // 버전: 2.0.4 -> 2_0_4
    $build = $_GET["buildVer"]; // 빌드 번호
} else {
    die("Invalid appVer, buildVer format.");
}

// ZIP 파일 이름 생성
$zipFilename = $version . "_" . $build . "-" . ($isAndroid ? "Android" : "iOS") . ".zip";

// `/tmp` 디렉터리에 임시 폴더 생성
$tmpBaseDir = "/tmp" . DIRECTORY_SEPARATOR . $topPath;
$tmpTargetDir = $tmpBaseDir . DIRECTORY_SEPARATOR . $version . "_" . $build . "-" . ($isAndroid ? "Android" : "iOS");

// ZIP 파일 생성 경로
$zipFilePath = $tmpBaseDir . DIRECTORY_SEPARATOR . $zipFilename;
// 압축 파일 최종 경로
$finalBaseDir = $sourceDir . DIRECTORY_SEPARATOR . $sourceSuffixPath;
$finalZipPath = $finalBaseDir . DIRECTORY_SEPARATOR . $zipFilename;

if (!is_file($finalZipPath) || !is_readable($finalZipPath)) {
    if (!is_dir($tmpBaseDir)) {
        mkdir($tmpBaseDir, 0777, true);
    }
    // 임시 디렉터리에 올바른 경로 및 권한 확인
    if (is_dir($tmpTargetDir)) {
       // 임시 폴더 삭제
        deleteDirectory($tmpTargetDir); 
    }
    // 임시 폴더에 iOS 또는 Android 폴더 구조 생성
    createDirectoryTree($tmpTargetDir, $isAndroid, $version, $build, $finalBaseDir, $sourceFilename);
    // Zip 파일 생성
    createZipFile($tmpTargetDir, $zipFilePath);
    // 압축 파일 이동
    if (!rename($zipFilePath, $finalZipPath)) {
        die("Failed to move ZIP file to the final location.");
    }
    
    // 결과 출력
    echo "ZIP file created and moved to: $finalZipPath";    
}

// ZIP 파일 다운로드
function downloadZipFile($outputZipFile) {
    global $outUrl, $topPath, $keyDistPath, $sourceSuffixPath, $zipFilename;

    // Wait for ZIP file to be created
    if (!file_exists($outputZipFile)) {
        die("ZIP file does not exist: $outputZipFile");
    }

    // Clear output buffer and send headers for download
    ob_end_clean();
    // header('Content-Type: application/zip');
    // header('Content-Disposition: attachment; filename="' . basename($outputZipFile) . '"');
    // header('Content-Length: ' . filesize($outputZipFile));
    // readfile($outputZipFile);

    $previousPage = $_SERVER["HTTP_REFERER"];
    $realDownloadUrl = $outUrl . DIRECTORY_SEPARATOR . $topPath . DIRECTORY_SEPARATOR . $keyDistPath . DIRECTORY_SEPARATOR . $sourceSuffixPath . DIRECTORY_SEPARATOR . $zipFilename;
    header('Refresh: 0; url='. $realDownloadUrl);

    // Use JavaScript to refresh the previous page after a small delay
    echo "<script type='text/javascript'>
    setTimeout(function() {
        window.location.href = '$previousPage';
    }, 3000); // Refresh after 3 seconds (adjust delay as needed)
    </script>";

    // Optionally delete the ZIP file after download
    // unlink($outputZipFile);
    exit;
}

// 다운로드 처리
downloadZipFile($finalZipPath);

?>