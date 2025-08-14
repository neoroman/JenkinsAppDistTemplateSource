<?php
session_start();

require_once('config.php');
global $usingLogin;
global $outBoundPoint;
global $topPath, $boanEndPoint;

$icon = $json->{'icon'};

if ($usingLogin && !isset($_SESSION['internal_id'])) {
    if ($usingLoginRemoteAPI && $_SERVER['SERVER_NAME'] == $outBoundPoint) {
        // Do nothing for remote API login on app.company.com
        $redirectUrl = str_replace("4000", "8080", $boanEndPoint);
        header('Location: ' . $redirectUrl .'/'. $topPath . '/login.php?redirect='. $_SERVER['PHP_SELF']);
    } else {
        header('Location: login.php?redirect='. $_SERVER['PHP_SELF']);
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo L::client_title ." ". L::app_name ?></title>
    <link rel="apple-touch-icon-precomposed" href="./images/<?php echo $icon->{'home'}; ?>">
    <!-- font CSS -->
    <link rel="stylesheet" href="./font/NotoSans.css">
    <!-- select Css -->
    <link rel="stylesheet" href="./css/nice-select.css">
    <!-- common Css -->
    <link rel="stylesheet" href="./css/common.css?v3">
    <?php
        if (file_exists('../custom/user.css')) {
            echo "<link rel=\"stylesheet\" href=\"./custom/user.css\">";
        }
    ?>
</head>

<body>
<!-- wrap -->
<div class="wrap qa_wrap qa_type1"> <!-- (내부)qa_type1, (외부)qa_type2 -->
    <div class="header">
        <div class="inner">
            <h1 class="logo"><a href="#"><?php echo L::client_title ." ". L::app_name ?></a></h1>
        </div>
    </div>

    <div class="container">
        <h2 class="stit"><?php echo L::company_name; ?>&nbsp;<?php echo L::title_h2_domestic; ?></h2>
        <div class="link_os">
            <!--
            <a href="javascript:passingQueryTo('./android/index.php');"><span class="txt">Android</span></a>
            <a href="javascript:passingQueryTo('./ios/index.php');"><span class="txt">iOS</span></a>
            -->
            <?php if ($usingAndroid) { ?>
            <a href="./android/dist_android.php"><span class="txt"><?php echo L::os_android; ?></span></a>
            <?php } 
            if ($usingiOS) { ?>
            <a href="./ios/dist_ios.php"><span class="txt"><?php echo L::os_ios; ?></span></a>
            <?php } ?>
        </div>

        <div class="txt_area">
            <p class="txt"><strong class="txt_bold">
                <?php if ($usingAndroid) { ?>
                <span class="point_c1"><?php echo L::os_android; ?></span>
                <?php } ?>
                <?php if ($usingAndroid && $usingiOS) { ?>
                &nbsp;/&nbsp;
                <?php } ?>
                <?php if ($usingiOS) { ?>
                <span class="point_c1"><?php echo L::os_ios; ?></span> 
                <?php } ?>
                </strong>
                <?php echo L::description_notice3_domestic; ?><span class="txt_by"></span></p>
        </div>

        <div class="btn_area">
            <a href="./dist_client.php" class="btn_confirm"><?php echo L::button_link_to_client; ?></a>
            <!-- <p class="txt_link">(AOS iOS 모둠 페이지)</p> -->
        </div>
    </div>
</div>
<!-- //wrap -->

<!-- footer -->
<div class="footer">
    <div class="inner">
        <p class="copyright"><?php echo L::copywrite_years; ?> &copy; <a href="javascript:logout();"><?php echo L::copywrite_company; ?></a></p>
    </div>
</div>
<!-- //footer -->

<!-- jquery JS -->
<script src="./js/jquery-3.2.1.min.js"></script>
<!-- select JS -->
<script src="./js/jquery.nice-select.min.js"></script>
<!-- placeholder JS : For ie9 -->
<script src="./plugin/jquery-placeholder/jquery.placeholder.min.js"></script>
<!-- common JS -->
<script src="./js/common.js"></script>
<!-- app dist common for client JS -->
<script src="./js/appDistCommon4client.js?v4"></script>

<!-- ChannelIO 스크립트 -->
<?php
require_once('./phpmodules/ChannelIO.php');
echo generateChannelIOScript();
?>
</body>
</html>
