<?php
session_start();

if (!class_exists('i18n')) {
  if (file_exists(__DIR__ .'/../config.php')) {
    require_once(__DIR__ . '/../config.php');
  }  
  else if (file_exists(__DIR__ .'/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
  }  
}
global $usingLogin, $topPath;
global $outBoundPoint;

if ($usingLogin && !isset($_SESSION['internal_id'])) {
  if ($usingLoginRemoteAPI && $_SERVER['SERVER_NAME'] == $outBoundPoint) {
    // Do nothing for remote API login on app.company.com
  } else {
    header('Location: /'. $topPath .'/login.php?redirect='. $_SERVER['PHP_SELF']);
  }
}

if (file_exists('../phpmodules/common.php')) {
  require('../phpmodules/common.php');
} else if (file_exists('phpmodules/common.php')) {
  require('phpmodules/common.php');
}
if (file_exists("../.access_file.php")) {
    require_once('../.access_file.php');
    global $accessToken, $dateString;
    echo "<!-- accessToken: $accessToken -->\n";
    echo "<!-- timestamp: $dateString -->\n";
}
global $inUrl, $outUrl;

$icon = $json->{'icon'};
$selectedPattern = "";

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::client_title ." ". L::app_name ?></title>
  <link rel="apple-touch-icon-precomposed" href="../images/<?php echo $icon->{'home'}; ?>">
  <!-- font CSS -->
  <link rel="stylesheet" href="../font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="../css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="../css/common.css?v4">
</head>

<body>
  <?php
    $pattern = "";
    if (isset($_POST['search'])) {
      $pattern = $_POST['version'];
    }
    // echo "<H3><font color=pink>검색어: $pattern</font></H1>";
   ?>
<!-- wrap -->
<div class="wrap sub_type1"> <!-- (내부)sub_type1, (외부)sub_type2 -->
  <div class="header">
    <div class="inner">
      <h1 class="logo"><a href="../dist_domestic.php"><?php echo L::client_title ." ". L::app_name ?></a></h1>
      <a href="../dist_domestic.php" class="page_prev"><span class="hide"><?php echo L::title_alt_previous_page ?></span></a>

      <!-- 검색 팝업 : modal-S(모바일의 경우만 모달처리됨) -->
      <div class="modal_box" id="modal-S">
        <!-- 검색 -->
        <div class="search_area">
          <form method="POST" name="search" id="mySearch" onsubmit="FormSubmit(this);">
            <input type="hidden" name="version" />
            <input type="hidden" name="search" value="findIt"/>
            <select name="version_ddl" onchange="DropDownChanged(this)">
              <option value="Select"><?php if (isset($_POST['search']) && strlen($pattern) > 0 && $pattern != "Select") { echo L::search_reset_word; } else { echo L::search_select; } ?></option>
              <option value="<?php echo L::search_define1; ?>" <?php if ($pattern == L::search_define1) { echo "selected"; $selectedPattern = $pattern; }?>><?php echo L::search_define1 ?></option>
              <option value="<?php echo L::search_define2; ?>" <?php if ($pattern == L::search_define2) { echo "selected"; $selectedPattern = $pattern; }?>><?php echo L::search_define2 ?></option>
              <option value="<?php echo L::search_define3; ?>" <?php if ($pattern == L::search_define3) { echo "selected"; $selectedPattern = $pattern; }?>><?php echo L::search_define3 ?></option>
              <option value="<?php echo L::search_define4; ?>" <?php if ($pattern == L::search_define4) { echo "selected"; $selectedPattern = $pattern; }?>><?php echo L::search_define4 ?></option>
              <option value="<?php echo L::search_define5; ?>" <?php if ($pattern == L::search_define5) { echo "selected"; $selectedPattern = $pattern; }?>><?php echo L::search_define5 ?></option>
              <option value="self" <?php
              if ($selectedPattern != $pattern && isset($_POST['search']) && strlen($pattern) > 0 && $pattern != "Select") {
                echo "selected> $pattern";
              } else {
                echo ">" . L::search_input_word;
              }?></option>
            </select>
            <?php
            if ($selectedPattern != $pattern && isset($_POST['search']) && strlen($pattern) > 0 && $pattern != "Select") {
              echo "<input type=\"text\" id=\"\" name=\"version_txt\" class=\"inp_self\" value=\"$pattern\" enabled>";
            }
            else {
              echo "<input type=\"text\" id=\"\" name=\"version_txt\" class=\"inp_self\" disabled>";
            }
            ?>
            <a href="#" class="btn" onclick="javascript:document.getElementById('mySearch').submit()"><span class="hide"><?php echo L::search_title; ?></span></a>
            <a href="#" class="btn_close"><?php echo L::search_close; ?></a>
          </form>
        </div>
        <!-- //검색 -->
      </div>
      <!-- //검색 팝업 : modal-S(모바일의 경우만 모달처리됨) -->

      <a href="../phpmodules/pw_guide_uaqa.php" class="link_pw"><?php echo L::title_admin_password; ?></a>
      <a href="#modal-S" class="link_search"><?php echo L::search_title; ?></a>
    </div>
  </div>

  <div class="tab_version">
    <a href="javascript:FormSubmitWithKeyword('Select');" <?php if (!isset($_POST['search']) || (isset($_POST['search']) && strlen($pattern) > 0 && $pattern != L::title_tab_qc_version)) { echo "class=\"on\""; } ?>><span><?php echo L::title_tab_full_version; ?></span></a>
    <a href="javascript:FormSubmitWithKeyword('<?php echo L::title_tab_qc_version; ?>');" <?php if (isset($_POST['search']) && strlen($pattern) > 0 && $pattern == L::title_tab_qc_version) { echo "class=\"on\""; } ?>><span><?php echo L::title_tab_qc_version; ?></span></a>
    <?php if ($usingPreviousVersion) {
      echo "<a href=\"". $outUrl ."/". $topPathPreviousVersion ."/android/dist_android.php\"><span>". L::title_tab_previous_version ."</span></a>";
    }?>
  </div>

  <?php
    list($html_list, $pagenation, $currPage) = getPaginationSnippets("android", true);
  ?>

  <div class="container">

    <div class="tab_os">
      <?php if ($usingAndroid) { ?>
      <a href="#" class="on"><span>Android</span></a>
      <?php } ?>
      <?php if ($usingAndroid && $usingiOS) { ?>
      <span class="bar">|</span>
      <?php } ?>
      <?php if ($usingiOS) { ?>
      <a href="../ios/dist_ios.php"><span>iOS</span></a>
      <?php } ?>
    </div>

    <div class="cont_area">
      <div class="top_area">
      <h2 class="tit_box"><?php echo L::company_name; ?>&nbsp;<?php echo L::title_h2_domestic_in_os; ?>&nbsp;&nbsp;<?php 
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;     // GETTING PAGE NUMBER FROM URL
        echo ($page - 1) > 0 ? '&nbsp;&nbsp;<a href="?page=' . ($page - 1) . '"><i class="arrow left"></i></a>' : '&nbsp;&nbsp;<i class="arrow left"></i>';
        echo $currPage;
        $less_than = explode('/', $currPage)[1];
        echo ($page + 1) <= $less_than ? '<a href="?page=' . ($page + 1) . '"><i class="arrow right"></i></a>' : '<i class="arrow right"></i>';      
        ?></h2>
        <p class="txt"><?php echo L::description_notice4; ?></p>
        <p class="txt"><?php echo L::description_notice15_android; ?></p>
      </div>

      <div class="item_area">
        <!-- item -->
        <?php
        if (isset($_POST['search']) && strlen($pattern) > 0 && $pattern != "Select") {
          echo getHtmlSnippets("android", true, true, $pattern, $html_list);
        } else {
          echo getHtmlSnippets("android", true, false, $pattern, $html_list);
        }
        ?>
        <!-- //item -->
      </div>
    </div>
  </div>
</div>
<!--//wrap -->

<?php echo $pagenation; ?>

<!-- footer -->
<div class="footer">
  <div class="inner">
    <p class="copyright"><?php echo L::copywrite_years; ?> &copy; <?php echo L::copywrite_company; ?></a></p>
  </div>
</div>
<!-- //footer -->

<a href="#" title="맨위로" id="moveTop">TOP</a>

<!-- jquery JS -->
<script src="../js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="../js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="../plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="../js/common.js?v1"></script>
<!-- app dist common for client JS -->
<script src="../js/appDistCommon4client.js?v5"></script>
</body>
</html>
