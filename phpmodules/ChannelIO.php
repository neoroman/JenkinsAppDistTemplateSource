<?php
// ChannelIO PHP 스크립트 - 세션 정보 기반
// 이 파일은 include하여 사용하거나, 직접 출력할 수 있습니다.

function generateChannelIOScript() {
    // 세션 정보 확인
    $sessionId = isset($_SESSION['id']) ? $_SESSION['id'] : '';
    $sessionInternalId = isset($_SESSION['internal_id']) ? $_SESSION['internal_id'] : '';
    $sessionEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    $sessionName = isset($_SESSION['name']) ? $_SESSION['name'] : '';
    $sessionToken = isset($_SESSION['token']) ? $_SESSION['token'] : '';
    
    // 사용자 정보 설정
    $userId = !empty($sessionInternalId) ? $sessionInternalId : $sessionId;
    $userName = !empty($sessionName) ? $sessionName : (!empty($userId) ? $userId : '게스트 사용자');
    $userEmail = !empty($sessionEmail) ? $sessionEmail : '';
    $userType = !empty($sessionInternalId) ? 'internal' : (!empty($sessionId) ? 'client' : 'guest');
    $isLoggedIn = !empty($userId);
    
    // ChannelIO 설정
    $pluginKey = 'a8998820-06d9-420b-964a-c3e072a1265f';
    
    // 스크립트 생성
    $script = "
<!-- ChannelIO 스크립트 -->
<script>
(function() {
  'use strict';
  
  // PHP 세션 정보
  const USER_INFO = {
    id: '" . addslashes($userId) . "',
    name: '" . addslashes($userName) . "',
    email: '" . addslashes($userEmail) . "',
    type: '" . addslashes($userType) . "',
    isLoggedIn: " . ($isLoggedIn ? 'true' : 'false') . ",
    token: '" . addslashes($sessionToken) . "'
  };
  
  // ChannelIO 초기화
  function initChannelIO() {
    if (window.channelIOInitialized) return;
    
    console.log('ChannelIO 초기화 - 사용자:', USER_INFO);
    
    if (window.ChannelIO) {
      window.ChannelIO('boot', {
        \"pluginKey\": '" . addslashes($pluginKey) . "',
        \"profile\": {
          \"name\": USER_INFO.name,
          \"email\": USER_INFO.email,
          \"mobileNumber\": \"\",
          \"landlineNumber\": \"+82 31 710 6200\",
          \"os\": \"pc\",
          \"CUSTOM_VALUE_1\": USER_INFO.id,
          \"CUSTOM_VALUE_2\": USER_INFO.type,
          \"CUSTOM_VALUE_3\": USER_INFO.isLoggedIn ? 'true' : 'false',
          \"CUSTOM_VALUE_4\": window.location.pathname,
          \"CUSTOM_VALUE_5\": new Date().toISOString()
        }
      });
      
      window.channelIOInitialized = true;
      console.log('ChannelIO 부트 완료');
    }
  }
  
  // 초기화
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChannelIO);
  } else {
    initChannelIO();
  }
  
  // 디버깅 함수
  window.debugUserInfo = function() {
    console.log('사용자 정보:', USER_INFO);
    console.log('ChannelIO 상태:', window.channelIOInitialized);
  };
})();
</script>

<!-- ChannelIO 로더 -->
<script>
(function(){
  var w=window;
  if(w.ChannelIO){return w.console.error(\"ChannelIO script included twice.\");}
  var ch=function(){ch.c(arguments);};
  ch.q=[];ch.c=function(args){ch.q.push(args);};
  w.ChannelIO=ch;
  function l(){
    if(w.ChannelIOInitialized){return;}
    w.ChannelIOInitialized=true;
    var s=document.createElement(\"script\");
    s.type=\"text/javascript\";
    s.async=true;
    s.src=\"https://cdn.channel.io/plugin/ch-plugin-web.js\";
    var x=document.getElementsByTagName(\"script\")[0];
    if(x.parentNode){x.parentNode.insertBefore(s,x);}
  }
  if(document.readyState===\"complete\"){l();}else{w.addEventListener(\"DOMContentLoaded\",l);w.addEventListener(\"load\",l);}
})();
</script>";

    return $script;
}

// 직접 출력 모드
if (basename($_SERVER['SCRIPT_NAME']) === 'ChannelIO.php') {
    echo generateChannelIOScript();
}
?>
