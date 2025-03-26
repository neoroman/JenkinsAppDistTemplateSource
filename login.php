<?php
session_start();

require_once('config.php');
global $conn, $topPath, $usingMySQL, $userDict, $usingLoginRemoteAPI;
global $boanEndPoint;

require('phpmodules/common.php');
global $inUrl, $outUrl;

if (isset($_POST['login'])) {
  $loginID = trim($_POST['login_id']);
  $loginPW = trim($_POST['login_pw']);
  $loginType = strtolower(trim($_POST['login_type']));

  $error_message = '';
        
  if (!empty($loginID) && !empty($loginPW)) {
    if ($usingMySQL) {
      $sql = "SELECT userId, password, email, status FROM ". L::app_name ."_users WHERE userId = '".$loginID."' AND password = '".$loginPW."'";
      //md5($conn->real_escape_string($password))."'";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
      }
    }
    else if ($userDict && isset($userDict[$loginID])) {
      $row = $userDict[$loginID];
    }
    else if ($usingLoginRemoteAPI) {
      $url = "$boanEndPoint/secure?id=". urlencode($loginID) ."&pwd=" . urlencode($loginPW);
      $result = httpGet($url);
      $finalJson = json_validate2($result, false);
      if (isset($finalJson->{'userId'}) && isset($finalJson->{'token'}) && isset($finalJson->{'email'}) && isset($finalJson->{'name'})) {
        $row = [
          "userId" => $finalJson->{'userId'},
          "token" => $finalJson->{'token'},
          "status" => "1",
          "email" => $finalJson->{'email'},
          "name" => $finalJson->{'name'},
          "password" => $loginPW
        ];  
      }
    }
    if (isset($row) && $loginPW == $row['password']) {
        if ($row['status'] == "2" || $loginType == "2") {
          $_SESSION['internal_id'] = $row['userId'];
          $_SESSION['id'] = $row['userId'];
          if (isset($row['token'])) {
            $_SESSION['token'] = $row['token'];
          }
          if (isset($row['email'])) {
            $_SESSION['email'] = $row['email'];
          }
          if (isset($row['name'])) {
            $_SESSION['name'] = $row['name'];
          }
          
          $redirUrl = "";
          if (isset($_POST['redirection'])) {
            $redirUrl = $_POST['redirection'];
          } else if (isset($_GET['redirection'])) {
            $redirUrl = $_GET['redirection'];
          } else if (isset($_GET['redirect'])) {
            $redirUrl = $_GET['redirect'];
          } else if (isset($_POST['redirect'])) {
            $redirUrl = $_POST['redirect'];
          }
          
          if (strpos($redirUrl, '?')) {
            $temp = explode('?', $redirUrl);
            $redirUrl = $temp[0];
          }
          if (strlen($redirUrl) <= 0) {
            $redirUrl = "/$topPath/dist_domestic.php";
          }
          if ($loginType == "1") {
            $redirUrl = "/$topPath/dist_client.php";
          }
          header('Location: '. $redirUrl);
        }
        else if ($row['status'] == "1") {
          $_SESSION['id'] = $row['userId'];
          if (isset($row['token'])) {
            $_SESSION['token'] = $row['token'];
          }
          if (isset($row['email'])) {
            $_SESSION['email'] = $row['email'];
          }
          if (isset($row['name'])) {
            $_SESSION['name'] = $row['name'];
          }          
          $redirUrl = "";
          if (isset($_POST['redirection'])) {
            $redirUrl = $_POST['redirection'];
          } else if (isset($_GET['redirect'])) {
            $redirUrl = $_GET['redirect'];
          }
          if (strpos($redirUrl, '?')) {
            $temp = explode('?', $redirUrl);
            $redirUrl = $temp[0];
          }
          if (strlen($redirUrl) <= 0) {
            $redirUrl = "/$topPath/dist_client.php";
          }
          header('Location: '. $redirUrl);
        }
        else {
            $error_message .= 'Your account is not active yet.';
        }
    }
    else {
        $error_message .= 'Incorrect ID(email) or password.';
    }
  } 
  else {
      $error_message .= 'Please enter ID(email) and password.';
  }

  if (isset($error_message) && strlen($error_message) > 0) {
    //echo "<H1>$error_message</H1>";
    //header('Location: login.php');
  }
}
 ?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php echo L::company_name ." ". L::app_name ?></title>
  <link rel="apple-touch-icon-precomposed" href="./images/HomeIcon.png">
  <!-- font CSS -->
  <link rel="stylesheet" href="./font/NotoSans.css">
  <!-- select Css -->
  <link rel="stylesheet" href="./css/nice-select.css">
  <!-- common Css -->
  <link rel="stylesheet" href="./css/common.css?v3">
  <script type="text/javascript">
  function LoginGetParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  }
  function LoginFormSubmit(oForm) {
      var oHidden = oForm.elements["redirection"];
      if (oHidden && LoginGetParameterByName("redirect"))
          oHidden.value = LoginGetParameterByName("redirect");
  }
  </script>
</head>
<body>
<?php
$loginType = "<input type=\"hidden\" name=\"login_type\" value=\"1\" />";
if (glob(('../*_organization_flat.php'))) {
  $login_desc = "* 본인의 ". L::app_name ." ID/PW로 로그인 하세요.";
}
if (file_exists('../password_find.php')) {
  $reset_password = '<BR />[ <a href="password_find.php">비밀번호 찾기</a> ]';
}
echo "<!-- wrap -->";
//<!-- (외부로그인)login_type1, (내부로그인)login_type2 -->
if (isset($_GET['redirect'])) {
  $redirUrl = $_GET['redirect'];
  if (strpos($redirUrl, 'dist_domestic') || strpos($redirUrl, 'dist_android') || strpos($redirUrl, 'dist_ios')) {
    echo "<div class=\"wrap login_type2\">";
    $loginType = "<input type=\"hidden\" name=\"login_type\" value=\"2\" />";
    $login_desc = "";
  }
  else {
    echo "<div class=\"wrap login_type1\">";
  }
}
else {
  echo "<div class=\"wrap login_type1\">";
}
?>
  <!-- 로그인 -->
  <div class="login_area">
    <form method="POST" name="login" onsubmit="LoginFormSubmit(this);">
      <input type="hidden" name="redirection" />
      <?php echo $loginType; ?>
  		<fieldset class="login_form">
      <legend><?php echo L::app_name; ?></legend>
      <h1 class="logo"><?php echo L::app_name; ?> 배포 사이트</h1>
  			<div class="inputs">
  				<label class="id_type">
            <input type="text" name="login_id" class="inp_text" placeholder="아이디를 입력하세요." autocomplete="username" required>
          </label>
  				<label class="pw_type">
            <input type="password" name="login_pw" class="inp_text" placeholder="비밀번호를 입력하세요." autocomplete="current-password" required>
          </label>
  			</div>
        <?php if (isset($login_desc) && strlen($login_desc) > 0) echo "<div>$login_desc</div>"; ?>
        <div class="btn_area">
          <input type="submit" class="btn_login" name="login" value="로그인" />
        </div>
        <?php if (isset($reset_password) && strlen($reset_password) > 0) echo "<div class=\"find_password\">$reset_password</div>"; ?>
        <?php if (isset($error_message) && strlen($error_message) > 0) echo "<div>$error_message</div>"; ?>
  		</fieldset>
    </form>
  </div>
  <!-- //로그인 -->
</div>
<!-- //wrap -->

<!-- footer -->
<div class="footer">
  <div class="inner">
    <p class="copyright"><?php echo L::copywrite_years; ?> &copy; <?php echo L::copywrite_company; ?></a></p>
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
</body>
</html>

<?php 
//phpinfo(); 
?>
