<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once __DIR__ . '/sendmail.php';

function sendUpdatedFiles($recipients, $files) {
    $subject = "[" . L::app_name . "] Updated Language Files";
    $body = "Please find the updated language files attached.";
    
    foreach ($recipients as $recipient) {
        error_log("Sending email to: $recipient"); // Debugging statement
        sendMail($recipient, $subject, $body, $files);
    }
}

function updateLanguageFiles($emailData = null) {
    // Adjust paths for lang directory at same level as src
    $langFiles = [
        __DIR__ . '/../../lang/lang_ko.json',
        __DIR__ . '/../../lang/lang_en.json'
    ];

    // Define new language strings - keeping them flat without sections
    $newStrings = [
        'ko' => [
            'admin_password' => '설정',
            'mail_recipients_management' => '메일 설정',
            'recipient_name' => '수신자 이름',
            'recipient_email' => '이메일 주소',
            'remove' => '삭제',
            'add_recipient' => '수신자 추가',
            'add_cc_recipient' => 'CC 추가',
            'save' => '저장',
            'max_recipients' => '최대 20명까지만 추가할 수 있습니다.',
            'save_success' => '저장되었습니다.',
            'save_error' => '저장 중 오류가 발생했습니다.',
            'keywords_settings' => '키워드 설정',
            'release_keyword' => '배포 키워드',
            'develop_keyword' => '개발 키워드',
            'from_settings' => '발신자 설정',
            'from_email' => '발신자 이메일',
            'from_name' => '발신자 이름',
            'reply_to_settings' => '회신 설정',
            'reply_to_email' => '회신 이메일',
            'reply_to_name' => '회신자 이름',
            'debug_settings' => '디버그 설정',
            'debug_to_email' => '디버그 수신자 이메일',
            'debug_to_name' => '디버그 수신자 이름',
            'to_recipients' => '수신자 목록',
            'cc_recipients' => 'CC 수신자 목록',
            'information_management' => '정보 관리',
            'app_settings' => '앱 설정',
            'app_name' => '앱 이름',
            'app_version' => '앱 버전',
            'client_settings' => '고객사 설정',
            'client_short_url' => '고객사에 전달할 (짧은) 배포 주소',
            'client_title' => '프로젝트 이름',
            'client_name' => '고객사 (짧은) 회사명',
            'client_full_name' => '고객사 (전체) 회사명',
            'company_settings' => '배포 회사 설정',
            'company_name' => '배포 회사 이름',
            'company_team' => '배포 팀 이름',
            'company_email' => '배포 팀 메일 주소'
        ],
        'en' => [
            'admin_password' => 'Settings',
            'mail_recipients_management' => 'Email Settings',
            'recipient_name' => 'Recipient Name',
            'recipient_email' => 'Email Address',
            'remove' => 'Remove',
            'add_recipient' => 'Add Recipient',
            'add_cc_recipient' => 'Add CC',
            'save' => 'Save',
            'max_recipients' => 'Maximum 20 recipients allowed.',
            'save_success' => 'Successfully saved.',
            'save_error' => 'An error occurred while saving.',
            'keywords_settings' => 'Keyword Settings',
            'release_keyword' => 'Release Keyword',
            'develop_keyword' => 'Development Keyword',
            'from_settings' => 'Sender Settings',
            'from_email' => 'From Email',
            'from_name' => 'From Name',
            'reply_to_settings' => 'Reply-To Settings',
            'reply_to_email' => 'Reply-To Email',
            'reply_to_name' => 'Reply-To Name',
            'debug_settings' => 'Debug Settings',
            'debug_to_email' => 'Debug Recipient Email',
            'debug_to_name' => 'Debug Recipient Name',
            'to_recipients' => 'Recipients List',
            'cc_recipients' => 'CC Recipients List',
            'information_management' => 'Information Management',
            'app_settings' => 'App Settings',
            'app_name' => 'App Name',
            'app_version' => 'App Version',
            'client_settings' => 'Client Settings',
            'client_short_url' => 'Client Short URL',
            'client_title' => 'Project Title',
            'client_name' => 'Client Name',
            'client_full_name' => 'Company Name',
            'company_settings' => 'Distribution Company Settings',
            'company_name' => 'Distribution Company Name',
            'company_team' => 'Distribution Team Name',
            'company_email' => 'Distribution Team Email'
        ]
    ];

    global $isDebugMode;
    
    foreach ($langFiles as $file) {
        if (file_exists($file)) {
            // Read the JSON file
            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if ($data === null) {
                error_log("Error decoding JSON in file: " . $file);
                continue;
            }


            // Add new language strings
            $lang = basename($file) === 'lang_ko.json' ? 'ko' : 'en';
            foreach ($newStrings[$lang] as $key => $value) {
                $data['title'][$key] = $value;
            }

            // Update mail settings if provided
            if ($emailData !== null) {
                if (!isset($data['mail'])) {
                    $data['mail'] = [];
                }

                // Update app, client, company
                $appClientCompany = ['app_name', 'app_version', 'client_short_url', 'client_title', 
                                    'client_name', 'client_full_name', 
                                    'company_name', 'company_team', 'company_email'];
                foreach ($appClientCompany as $acc) {
                    if (isset($emailData[$acc])) {
                        $firstKey = explode('_', $acc)[0];
                        $field = explode($firstKey . '_', $acc)[1];
                        $data[$firstKey][$field] = $emailData[$acc];
                    }
                }

                // Update simple fields
                $simpleFields = ['releaseKeyword', 'developKeyword', 'from', 'from_name', 
                               'reply_to', 'reply_to_name', 'debug_to', 'debug_to_name'];
                foreach ($simpleFields as $field) {
                    if (isset($emailData[$field])) {
                        $data['mail'][$field] = $emailData[$field];
                    }
                }

                // Update recipient lists (to and cc)
                $recipientFields = [
                    ['names' => 'to_name', 'emails' => 'to'],
                    ['names' => 'cc_name', 'emails' => 'cc']
                ];

                foreach ($recipientFields as $field) {
                    $names = $field['names'];
                    $emails = $field['emails'];
                    
                    if (!empty($emailData[$names]) && !empty($emailData[$emails])) {
                        // Ensure arrays are of equal length (max 20)
                        $count = min(count($emailData[$names]), count($emailData[$emails]), 20);
                        
                        // Filter out empty entries while preserving corresponding pairs
                        $namesList = array_slice($emailData[$names], 0, $count);
                        $emailsList = array_slice($emailData[$emails], 0, $count);
                        
                        $validPairs = array_filter(array_map(null, $namesList, $emailsList), function($pair) {
                            return !empty($pair[0]) && !empty($pair[1]);
                        });
                        
                        if (!empty($validPairs)) {
                            $finalNames = array_column($validPairs, 0);
                            $finalEmails = array_column($validPairs, 1);
                            
                            $data['mail'][$names] = implode('|', $finalNames);
                            $data['mail'][$emails] = implode('|', $finalEmails);
                        }
                    }
                }
            }

            // Write back to file
            $newContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($newContent !== false) {
                if (file_put_contents($file, $newContent)) {
                    // Clear the language cache after successful update
                    $cacheFile = __DIR__ . '/../../langcache/' . basename($file, '.json') . '.cache.php';
                    if (file_exists($cacheFile)) {
                        unlink($cacheFile);
                    }
                    $cacheFile = __DIR__ . '/../langcache/' . basename($file, '.json') . '.cache.php';
                    if (file_exists($cacheFile)) {
                        unlink($cacheFile);
                    }
                }
            } else {
                error_log("Error encoding JSON for file: " . $file);
            }
        }
    }

    // Send updated files to recipients
    if ($emailData !== null) {
        if ($isDebugMode && isset($emailData['debug_to'])) {
            $recipients = explode('|', $emailData['debug_to']);
        } else if (isset($emailData['reply_to'])) {
            $recipients = explode('|', $emailData['reply_to']);
        } else {
            $recipients = [];
        }
        error_log("Recipients: " . implode(', ', $recipients)); // Debugging statement
        sendUpdatedFiles($recipients, $langFiles);
    }
}

// Function to check if updates are needed
function checkLanguageUpdatesNeeded() {
    $koFile = __DIR__ . '/../../lang/lang_ko.json';
    if (file_exists($koFile)) {
        $content = file_get_contents($koFile);
        $data = json_decode($content, true);
        if ($data) {
            return !isset($data['title']['information_management']) || $data['title']['information_management'] !== "정보 관리";
        }
    }
    return true;
}

// Function to get current email settings
function getCurrentEmailSettings() {
    $koFile = __DIR__ . '/../../lang/lang_ko.json';
    if (file_exists($koFile)) {
        $content = file_get_contents($koFile);
        $data = json_decode($content, true);
        if ($data && isset($data['mail'])) {
            $mail = $data['mail'];
            $app = $data['app'];
            $client = $data['client'];
            $company = $data['company'];

            // Get simple fields
            $settings = [
                'releaseKeyword' => isset($mail['releaseKeyword']) ? $mail['releaseKeyword'] : '',
                'developKeyword' => isset($mail['developKeyword']) ? $mail['developKeyword'] : '',
                'from' => isset($mail['from']) ? $mail['from'] : '',
                'from_name' => isset($mail['from_name']) ? $mail['from_name'] : '',
                'reply_to' => isset($mail['reply_to']) ? $mail['reply_to'] : '',
                'reply_to_name' => isset($mail['reply_to_name']) ? $mail['reply_to_name'] : '',
                'debug_to' => isset($mail['debug_to']) ? $mail['debug_to'] : '',
                'debug_to_name' => isset($mail['debug_to_name']) ? $mail['debug_to_name'] : '',
                'app_name' => isset($app['name']) ? $app['name'] : '',
                'app_version' => isset($app['version']) ? $app['version'] : '',
                'client_short_url' => isset($client['short_url']) ? $client['short_url'] : '',
                'client_title' => isset($client['title']) ? $client['title'] : '',
                'client_name' => isset($client['name']) ? $client['name'] : '',
                'client_full_name' => isset($client['full_name']) ? $client['full_name'] : '',
                'company_name' => isset($company['name']) ? $company['name'] : '',
                'company_team' => isset($company['team']) ? $company['team'] : '',
                'company_email' => isset($company['email']) ? $company['email'] : ''
            ];
            
            // Get recipient lists
            $recipientFields = [
                ['names' => 'to_name', 'emails' => 'to'],
                ['names' => 'cc_name', 'emails' => 'cc']
            ];
            
            foreach ($recipientFields as $field) {
                $names = $field['names'];
                $emails = $field['emails'];
                
                $namesList = isset($mail[$names]) ? explode('|', $mail[$names]) : [];
                $emailsList = isset($mail[$emails]) ? explode('|', $mail[$emails]) : [];
                
                // Ensure arrays are of equal length by padding with empty strings
                $maxLength = max(count($namesList), count($emailsList));
                $settings[$names] = array_pad($namesList, $maxLength, '');
                $settings[$emails] = array_pad($emailsList, $maxLength, '');
            }
            
            return $settings;
        }
    }
    return [
        'releaseKeyword' => '',
        'developKeyword' => '',
        'from' => '',
        'from_name' => '',
        'reply_to' => '',
        'reply_to_name' => '',
        'debug_to' => '',
        'debug_to_name' => '',
        'to' => [],
        'to_name' => [],
        'cc' => [],
        'cc_name' => [],
        'app_name' => '',
        'app_version' => '',
        'client_short_url' => '',
        'client_title' => '',
        'client_name' => '',
        'client_full_name' => '',
        'company_name' => '',
        'company_team' => '',
        'company_email' => ''
    ];
}

// Handle POST request for email updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_email') {
    $emailData = $_POST;
    unset($emailData['action']); // Remove the action field
    updateLanguageFiles($emailData);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}
?>
