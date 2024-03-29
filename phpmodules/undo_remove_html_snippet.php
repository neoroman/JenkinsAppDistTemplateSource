<?php
$prefix_os_dir = "..";
if (file_exists('../phpmodules/common.php')) {
  require('../phpmodules/common.php');
  $prefix_os_dir = "../..";
} else if (file_exists('common.php')) {
  require('common.php');
  $prefix_os_dir = "../..";
}
global $root, $inUrl;

$prevPage = $_SERVER['HTTP_REFERER'];
// echo "<meta http-equiv=\"REFRESH\" content=\"2;url=$prevPage\"></HEAD>";
if (isset($_GET["title"])) {
  $file_title=$_GET['title'];
}
if (isset($_GET["os"])) {
  $file_os=$_GET['os'];
}
if (isset($_GET["file"])) {
  $file_name=$_GET['file'];
}

$referrer = $prevPage;
if (strpos($prevPage, '?') !== false) {
  $tmp = preg_split('/\?/', $prevPage);
  $referrer = $tmp[0];
}
if (endsWith($referrer, "php")) {
  $osDir = $file_os;
  if ($file_os == "ios") {
    $osDir = $prefix_os_dir . "/ios_distributions";
  } elseif ($file_os == "android") {
    $osDir = $prefix_os_dir . "/android_distributions";
  }

  if ($file_name) {
    foreach (glob("$osDir/*/*$file_name.html.deleted") as $filename) {
      echo "<H2>Undo removed HTML snippet.... [ DONE ]</H2>";
      $undoPath = pathinfo($filename, PATHINFO_DIRNAME);
      $undoFilename = basename($filename, '.deleted');
      rename($filename, "$undoPath/$undoFilename");
    }
  } else {
    $arr = preg_split('/[\s]+/', $file_title);
    $ver = $arr[0];
    $jenkins = $arr[1];

    // perform actions for each file found
    foreach (glob("$osDir/*/*$ver*.html.deleted") as $filename) {
      // jenkins 빌드번호가 맞는 경우만 삭제(rename)함 by EungShik Kim on 2019.11.25
      if (stripos(file_get_contents($filename), $jenkins)) {
        echo "<H2>Undo removed HTML snippet.... [ DONE ]</H2>";
        $undoPath = pathinfo($filename, PATHINFO_DIRNAME);
        $undoFilename = basename($filename, '.deleted');
        rename($filename, "$undoPath/$undoFilename");
      }
    }
  }
  echo "<script type=\"text/javascript\">
        setTimeout(function(){
          window.location.href = '$prevPage';
          window.stopAnimation();
        }, 1000);        
        </script>";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>html{height:100%}body{margin:0 auto;min-height:600px;min-width:800px;height:100%}.top{height:100px;height:calc(40% - 140px)}.bottom{height:150px;height:calc(60% - 210px)}.center{height:350px;text-align:center;vertical-align:middle;font-family:Verdana}.circle{margin:auto;width:460px;height:460px;border-radius:50%;background:#c0c6cc}.circle_text{line-height:460px;font-size:100px;color:#ffffff;font-weight:bold}.text{line-height:40px;font-size:26px;color:#505a64}
</style>
</head>
<body>
<div class="top"></div>
<div class="center">
<div class="circle">
<div class="circle_text">Undo</div>
</div>
<div>
<p class="text" id="a"></p>
</div>
<script>
(function(){var a=new XMLHttpRequest();a.open("get","/missing",true);a.send();a.onreadystatechange=function(){if(a.readyState==4&&(a.status==200||a.status==304)){var c=String(a.responseText);var e=document.open("text/html","replace");e.write(c);e.close()}else{var d={en:"UNDO",zh:"\u60a8\u8981\u627e\u7684\u9875\u9762\u672a\u627e\u5230\u3002",it:"Impossibile trovare la pagina ricercata.","zh-HK":"\u60a8\u6240\u6307\u5b9a\u7684\u9801\u9762\u4e0d\u5b58\u5728\u3002",cs:"Hledan\u00e1 str\u00e1nka nebyla nalezena.",es:"Lo sentimos, no se encuentra la p\u00e1gina que est\u00e1 buscando.",ru:"\u041d\u0435 \u0443\u0434\u0430\u0435\u0442\u0441\u044f \u043d\u0430\u0439\u0442\u0438 \u0438\u0441\u043a\u043e\u043c\u0443\u044e \u0441\u0442\u0440\u0430\u043d\u0438\u0446\u0443.",nl:"Kan de gezochte pagina niet vinden.",pt:"A p\u00e1gina que procura n\u00e3o foi encontrada.",no:"Finner ikke siden du leter etter.",nb:"Finner ikke siden du leter etter.",tr:"Arad\u0131\u011f\u0131n\u0131z sayfa bulunam\u0131yor.",pl:"Nie znaleziono strony, kt\u00f3rej szukasz.",fr:"La page que vous recherchez est introuvable.",de:"Die Seite, nach der Sie suchen, kann nicht gefunden werden.",hu:"A keresett oldal nem tal\u00e1lhat\u00f3.","pt-BR":"N\u00e3o foi poss\u00edvel encontrar a p\u00e1gina que voc\u00ea est\u00e1 buscando.","zh-MO":"\u60a8\u6240\u6307\u5b9a\u7684\u9801\u9762\u4e0d\u5b58\u5728\u3002",da:"Den side, du leder efter, kunne ikke findes.",ja:"\u304a\u63a2\u3057\u306e\u30da\u30fc\u30b8\u304c\u3001\u898b\u3064\u304b\u308a\u307e\u305b\u3093\u3002",nn:"Finner ikke siden du leter etter.","zh-TW":"\u60a8\u6240\u6307\u5b9a\u7684\u9801\u9762\u4e0d\u5b58\u5728\u3002",ko:"\ucc3e\uace0 \uacc4\uc2e0 \ud398\uc774\uc9c0\ub97c \ubc1c\uacac\ud560 \uc218 \uc5c6\uc2b5\ub2c8\ub2e4.",sv:"Sidan du s\u00f6ker kunde inte hittas."};var b=["zh-TW","zh-HK","zh-MO","pt-BR"];var f;if(window.navigator.languages!==undefined){f=window.navigator.languages[0]}else{f=window.navigator.language||window.navigator.browserLanguage}if(b.indexOf(f)<0){f=f.split("-")[0]}document.getElementById("a").innerHTML=d[f]||d.enu}}})();
</script>
</div>
<div class="bottom"></div>
</body>
</html>