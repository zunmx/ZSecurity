<?php
header("X-Powered-By:ALC_SmileLang");
header("Server:ALC_WebServer");
header("Waf:ALC_WAF_0xf2");

include_once("ZSConfig.php");

//echo $zkInfo["domain"]."---".$_SERVER['HTTP_HOST'] . strcmp($_SERVER['HTTP_HOST'], $zkInfo["domain"]);
function zlog($content)
{
    echo '<script>console.log("' . $content . '")</script>';
}
ob_start();
//zlog("注入成功-->");
//zlog($zkInfo["ip"].$zkInfo["domain"].$zkInfo["redirect"]);
if ($zkInfo["ip"] == "") {
//    zlog("ip留空");
} else {
    if (strcmp($_SERVER['HTTP_HOST'], $zkInfo["ip"])==0) { // 主机名等于ip地址
        blockPage($zkInfo["redirect"]);
        exit();
    }

}
if ($zkInfo["domain"] == "") {
//    zlog("domain留空");
} else {
    if (strcmp($_SERVER['HTTP_HOST'], $zkInfo["domain"])!=0) { // 主机名不等于域名
        blockPage($zkInfo["redirect"]);
        exit();
    }
}


function blockPage($url)
{
    if ($url != ""){
        header("location: ".$url );
    }
    echo @readfile("block.html",true);
    ob_end_flush();
    exit();
}


?>
