<?php
error_reporting(E_ALL & ~E_DEPRECATED);

session_start();

if (!class_exists('i18n')) {
  if (file_exists(__DIR__ .'/../config.php')) {
      require_once(__DIR__ . '/../config.php');
  }
  else if (file_exists(__DIR__ .'/../../config.php')) {
      require_once(__DIR__ . '/../../config.php');
  }
}
// Include the language update functionality
require_once(__DIR__ . '/update_settings.php');
// Check and update language files if needed
if (checkLanguageUpdatesNeeded()) {
    updateLanguageFiles();
}

// Initialize email settings
$emailSettings = getCurrentEmailSettings();

global $usingLogin, $topPath;
global $outBoundPoint;
global $topPath, $boanEndPoint;

if ($usingLogin && !isset($_SESSION['id'])) {
  if ($usingLoginRemoteAPI && $_SERVER['SERVER_NAME'] == $outBoundPoint) {
    // Do nothing for remote API login on app.company.com
    $redirectUrl = str_replace("4000", "8080", $boanEndPoint);
    header('Location: ' . $redirectUrl .'/'. $topPath . '/login.php?redirect='. $_SERVER['PHP_SELF']);
  } else {
    header('Location: /'. $topPath .'/login.php?redirect='. $_SERVER['PHP_SELF']);
  }
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::client_title ." ". L::app_name; ?></title>
  <link rel="apple-touch-icon-precomposed" href="../images/HomeIcon.png">
  <!-- font CSS -->
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css">
  <!-- Custom CSS -->
  <style>
    .settings-group {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #f9f9f9;
    }

    .settings-group h3 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 16px;
        color: #333;
    }

    .form-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .form-row label {
        width: 150px;
        margin-right: 10px;
        font-weight: bold;
    }

    .form-row .input_text {
        flex: 1;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }

    .email-entry {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .btn_add, .btn_remove, .btn_save {
        display: inline-block;
        padding: 10px 20px;
        margin-top: 10px;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn_add:hover, .btn_remove:hover, .btn_save:hover {
        background-color: #0056b3;
    }

    .btn_remove {
        background-color: #dc3545;
    }

    .btn_remove:hover {
        background-color: #c82333;
    }
  </style>
</head>

<body>
<!-- wrap -->
<div class="wrap qa_type1"> <!-- (내부)qa_type1, (외부)qa_type2 -->
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="javascript:history.back()"><?php echo L::client_title ." ". L::app_name; ?></a></h1>
      <a href="javascript:history.back()" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>
    </div>
  </div>

  <div class="container">
    <div class="box_guide">
      <h1 class="tit"><?php if ($lang == 'ko') { echo '설정 변경'; } else { echo 'Change Settings'; } ?></h1>

      <form id="emailForm" class="email-form">
        <!-- Information Management -->
        <div class="information_management">
          <h2 class="stit"><?php echo L::title_information_management; ?></h2>
          <div class="settings-group">
            <div class="form-row">
              <label for="app_name"><?php echo L::title_app_name; ?></label>
              <input type="text" id="app_name" name="app_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['app_name']); ?>">
            </div>
            <div class="form-row">
              <label for="app_version"><?php echo L::title_app_version; ?></label>
              <input type="text" id="app_version" name="app_version" class="input_text" value="<?php echo htmlspecialchars($emailSettings['app_version']); ?>">
            </div>
            <div class="form-row">
              <label for="client_short_url"><?php echo L::title_client_short_url; ?></label>
              <input type="text" id="client_short_url" name="client_short_url" class="input_text" value="<?php echo htmlspecialchars($emailSettings['client_short_url']); ?>">
            </div>
            <div class="form-row">
              <label for="client_title"><?php echo L::title_client_title; ?></label>
              <input type="text" id="client_title" name="client_title" class="input_text" value="<?php echo htmlspecialchars($emailSettings['client_title']); ?>">
            </div>
            <div class="form-row">
              <label for="client_name"><?php echo L::title_client_name; ?></label>
              <input type="text" id="client_name" name="client_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['client_name']); ?>">
            </div>
            <div class="form-row">
              <label for="client_full_name"><?php echo L::title_client_full_name; ?></label>
              <input type="text" id="client_full_name" name="client_full_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['client_full_name']); ?>">
            </div>
            <div class="form-row">
              <label for="company_name"><?php echo L::title_company_name; ?></label>
              <input type="text" id="company_name" name="company_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['company_name']); ?>">
            </div>
            <div class="form-row">
              <label for="company_team"><?php echo L::title_company_team; ?></label>
              <input type="text" id="company_team" name="company_team" class="input_text" value="<?php echo htmlspecialchars($emailSettings['company_team']); ?>">
            </div>
            <div class="form-row">
              <label for="company_email"><?php echo L::title_company_email; ?></label>
              <input type="email" id="company_email" name="company_email" class="input_text" value="<?php echo htmlspecialchars($emailSettings['company_email']); ?>">
            </div>
          </div>
        </div>

        <!-- Email Management -->
        <div class="email_management">
          <h2 class="stit"><?php echo L::title_mail_recipients_management; ?></h2>
            <!-- Keywords Section -->
            <div class="settings-group">
              <h3><?php echo L::title_keywords_settings; ?></h3>
              <div class="form-row">
                <label for="releaseKeyword"><?php echo L::title_release_keyword; ?></label>
                <input type="text" id="releaseKeyword" name="releaseKeyword" class="input_text" value="<?php echo htmlspecialchars($emailSettings['releaseKeyword']); ?>">
              </div>
              <div class="form-row">
                <label for="developKeyword"><?php echo L::title_develop_keyword; ?></label>
                <input type="text" id="developKeyword" name="developKeyword" class="input_text" value="<?php echo htmlspecialchars($emailSettings['developKeyword']); ?>">
              </div>
            </div>

            <!-- From Settings -->
            <div class="settings-group">
              <h3><?php echo L::title_from_settings; ?></h3>
              <div class="form-row">
                <label for="from"><?php echo L::title_from_email; ?></label>
                <input type="email" id="from" name="from" class="input_text" value="<?php echo htmlspecialchars($emailSettings['from']); ?>">
              </div>
              <div class="form-row">
                <label for="from_name"><?php echo L::title_from_name; ?></label>
                <input type="text" id="from_name" name="from_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['from_name']); ?>">
              </div>
            </div>

            <!-- Reply-To Settings -->
            <div class="settings-group">
              <h3><?php echo L::title_reply_to_settings; ?></h3>
              <div class="form-row">
                <label for="reply_to"><?php echo L::title_reply_to_email; ?></label>
                <input type="email" id="reply_to" name="reply_to" class="input_text" value="<?php echo htmlspecialchars($emailSettings['reply_to']); ?>">
              </div>
              <div class="form-row">
                <label for="reply_to_name"><?php echo L::title_reply_to_name; ?></label>
                <input type="text" id="reply_to_name" name="reply_to_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['reply_to_name']); ?>">
              </div>
            </div>

            <!-- Debug Settings -->
            <div class="settings-group">
              <h3><?php echo L::title_debug_settings; ?></h3>
              <div class="form-row">
                <label for="debug_to"><?php echo L::title_debug_to_email; ?></label>
                <input type="email" id="debug_to" name="debug_to" class="input_text" value="<?php echo htmlspecialchars($emailSettings['debug_to']); ?>">
              </div>
              <div class="form-row">
                <label for="debug_to_name"><?php echo L::title_debug_to_name; ?></label>
                <input type="text" id="debug_to_name" name="debug_to_name" class="input_text" value="<?php echo htmlspecialchars($emailSettings['debug_to_name']); ?>">
              </div>
            </div>

            <!-- Recipients (TO) -->
            <div class="settings-group">
              <h3><?php echo L::title_to_recipients; ?></h3>
              <div id="toEmailEntries">
                <?php
                $toCount = max(count($emailSettings['to']), count($emailSettings['to_name']), 1);
                for ($i = 0; $i < $toCount; $i++) {
                  $name = isset($emailSettings['to_name'][$i]) ? $emailSettings['to_name'][$i] : '';
                  $email = isset($emailSettings['to'][$i]) ? $emailSettings['to'][$i] : '';
                ?>
                <div class="email-entry">
                  <input type="text" name="to_name[]" placeholder="<?php echo L::title_recipient_name; ?>" value="<?php echo htmlspecialchars($name); ?>" class="input_text">
                  <input type="email" name="to[]" placeholder="<?php echo L::title_recipient_email; ?>" value="<?php echo htmlspecialchars($email); ?>" class="input_text">
                  <?php if ($i > 0) { ?>
                    <button type="button" class="btn_remove" onclick="removeEntry(this, 'toEmailEntries')"><?php echo L::title_remove; ?></button>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
              <button type="button" class="btn_add" onclick="addEntry('toEmailEntries')"><?php echo L::title_add_recipient; ?></button>
            </div>

            <!-- CC Recipients -->
            <div class="settings-group">
              <h3><?php echo L::title_cc_recipients; ?></h3>
              <div id="ccEmailEntries">
                <?php
                $ccCount = max(count($emailSettings['cc']), count($emailSettings['cc_name']), 1);
                for ($i = 0; $i < $ccCount; $i++) {
                  $name = isset($emailSettings['cc_name'][$i]) ? $emailSettings['cc_name'][$i] : '';
                  $email = isset($emailSettings['cc'][$i]) ? $emailSettings['cc'][$i] : '';
                ?>
                <div class="email-entry">
                  <input type="text" name="cc_name[]" placeholder="<?php echo L::title_recipient_name; ?>" value="<?php echo htmlspecialchars($name); ?>" class="input_text">
                  <input type="email" name="cc[]" placeholder="<?php echo L::title_recipient_email; ?>" value="<?php echo htmlspecialchars($email); ?>" class="input_text">
                  <?php if ($i > 0) { ?>
                    <button type="button" class="btn_remove" onclick="removeEntry(this, 'ccEmailEntries')"><?php echo L::title_remove; ?></button>
                  <?php } ?>
                </div>
                <?php } ?>
              </div>
              <button type="button" class="btn_add" onclick="addEntry('ccEmailEntries')"><?php echo L::title_add_cc_recipient; ?></button>
            </div>

            <div class="btn_wrap">
              <button type="submit" class="btn_save"><?php echo L::title_save; ?></button>
            </div>
        </div>
      </form>

<!-- Email management script -->
<script>
function addEntry(containerId) {
    const container = document.getElementById(containerId);
    const count = container.querySelectorAll('.email-entry').length;
    if (count >= 20) {
        alert('<?php echo L::title_max_recipients; ?>');
        return;
    }
    
    const template = `
        <div class="email-entry">
            <input type="text" name="${containerId === 'ccEmailEntries' ? 'cc_name[]' : 'to_name[]'}" 
                   placeholder="<?php echo L::title_recipient_name; ?>" class="input_text">
            <input type="email" name="${containerId === 'ccEmailEntries' ? 'cc[]' : 'to[]'}" 
                   placeholder="<?php echo L::title_recipient_email; ?>" class="input_text">
            <button type="button" class="btn_remove" onclick="removeEntry(this, '${containerId}')"><?php echo L::title_remove; ?></button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
}

function removeEntry(button, containerId) {
    const container = document.getElementById(containerId);
    if (container.querySelectorAll('.email-entry').length > 1) {
        button.closest('.email-entry').remove();
    }
}

document.getElementById('emailForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update_email');
    
    fetch('update_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text()) // Change to response.text() to log the raw response
    .then(text => {
        console.log('Response Text:', text); // Log the raw response text
        try {
            const data = JSON.parse(text); // Parse the response text as JSON
            if (data.success) {
                alert('<?php echo L::title_save_success; ?>');
            } else {
                alert('<?php echo L::title_save_error; ?>');
            }
        } catch (error) {
            console.error('JSON parse error:', error);
            alert('<?php echo L::title_save_error; ?>');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('<?php echo L::title_save_error; ?>');
    });
});
</script>

    </div>
  </div>
</div>
<!--//wrap-->

<!-- footer -->
<div class="footer">
  <div class="inner">
    <p class="copyright"><?php echo L::copywrite_years; ?> &copy; <a href="javascript:logout();"><?php echo L::copywrite_company; ?></a></p>
  </div>
</div>
<!-- //footer -->

<!-- jquery JS -->
<script src="../js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="../js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="../plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="../js/common.js"></script>

</body>
</html>
