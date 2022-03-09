<?php
header("X-Powered-By:ALC_SmileLang");
header("Server:ALC_WebServer");
header("Waf:ALC_WAF_0xf3");
include_once("Anti_CC.php");
include_once("ZSConfig.php");
if ($zkInfo["cc"] == "1") {
    log_redis();
}
ob_start();
//function zlog($content)
//{
//    echo '<script>console.log("' . $content . '")</script>';
//}
if ($zkInfo["ip"] == "") {
//    zlog("ip留空");
} else {
    if (strcmp($_SERVER['HTTP_HOST'], $zkInfo["ip"]) == 0) { // 主机名等于ip地址
        blockPage($zkInfo["redirect"]);
        exit();
    }

}
if ($zkInfo["domain"] == "") {
//    zlog("domain留空");
} else {
    $domain = $zkInfo["domain"];
    $explode = explode(",", $domain);
    if (!in_array($_SERVER["HTTP_HOST"],$explode)){
        blockPage($zkInfo["redirect"]);
        exit();
    }
}


function blockPage($url)
{
    if ($url != "") {
        header("location: " . $url);
    }
    @readfile("block.html", true);
    ob_end_flush();
    exit();
}


?>
