<?php
if ($_GET['action'] == 'testRedis') {
    if ($_POST["ZSecurityToken"] == md5("ZSecurity-%z^u&n#m@x-!" . strval($_SERVER["PATH"]))) {
        testRedis();
    } else {
        exit();// 未通过认证
    }
}
include_once("ZSConfig.php");
global $conn, $ip, $port, $cc_same_sec, $cc_diff_sec, $cc_block_time, $cc_ip_allow, $block_page, $cc_ip_clean, $pwd;
error_reporting(0); // 隐藏PHP的日志信息
$ip = strval($zkInfo["redis_ip"]);
$block_page = strval($zkInfo["redirect"]);
$port = intval($zkInfo["redis_port"]);
$pwd = strval($zkInfo["cc_redispasswd"]); // 缓存失效时间
$cc_same_sec = intval($zkInfo["cc_same_sec"]);  // 相同页面
$cc_diff_sec = intval($zkInfo["cc_diff_sec"]); // 不同页面
$cc_block_time = intval($zkInfo["cc_block_time"]); // 封禁时间
$cc_ip_allow = strval($zkInfo["cc_ip_allow"]); // 允许IP
$cc_ip_clean = strval($zkInfo["cc_ip_clean"]); // 缓存失效时间

function testRedis()
{
    try {
        $redis = new Redis();
        $redis->connect(strval($_POST['anti_cc_redisIp']), strval($_POST['anti_cc_redisPort']));
        $redis->auth(strval($_POST['anti_cc_redispasswd']));
        $redis->set("ALC_ZSecurityTest", "ZSecurity_Redis_Link_Test", 10);
        echo "[√] 连接成功";
    } catch (Exception $e) {
        echo "[×] 连接失败";
    }
}

function log_redis()
{
    global $conn, $ip, $port, $cc_same_sec, $cc_diff_sec, $cc_block_time, $cc_ip_allow, $block_page, $cc_ip_clean, $pwd;
    if (in_array($_SERVER["REMOTE_ADDR"], explode(",", $cc_ip_allow))) { // 允许过度访问。  // TODO 下一个版本可以考虑通过修改htaccess
        return;
    }

    try {
        $conn = new Redis();  // TODO: 会拖慢效率，那位大佬会写PHP的单例，我试了试每次都会创建，有点懵。
        $conn->pconnect($ip, $port);
        $conn->auth($pwd);
        $conn->lPush($_SERVER["REMOTE_ADDR"], $_SERVER["REQUEST_URI"] . "<=U-D=>" . time());
        $conn->expireAt($_SERVER["REMOTE_ADDR"], intval(time()) + $cc_ip_clean);

        if ($conn->get("admin_ip") != "" && $conn->get("admin_ip") == $_SERVER["REMOTE_ADDR"]) {
            $conn->del($_SERVER["REMOTE_ADDR"]);
            $conn->del("b" . $_SERVER["REMOTE_ADDR"]);

        }

        // 判断IP是否被封锁
        if ($conn->get("b" . $_SERVER["REMOTE_ADDR"]) == "1") {
            blockPage($block_page);
        }

        if ($cc_same_sec != -1) {    // 访问相同页面次数
            $last = $conn->lIndex($_SERVER["REMOTE_ADDR"], $cc_same_sec); // 阈值内最后访问
            if ($last != "") {
                $lastTime = explode("<=U-D=>", $conn->lIndex($_SERVER["REMOTE_ADDR"], $cc_same_sec))[1];
                if (intval(time()) - intval($lastTime) < 60) {
                    $tmp_a = explode("<=U-D=>", $conn->lIndex($_SERVER["REMOTE_ADDR"], 0))[0];
                    for ($i = 1; $i < $cc_same_sec; $i++) {
                        if (explode("<=U-D=>", $conn->lIndex($_SERVER["REMOTE_ADDR"], $i))[0] == $tmp_a) {
                            $conn->set("b" . $_SERVER["REMOTE_ADDR"], 1, $cc_block_time);//封锁
                            blockPage($block_page);
                        } else {
                            break;
                        }
                    }
                }

            }

            if ($cc_diff_sec != -1) {    // 访问相同页面次数
                $last = $conn->lIndex($_SERVER["REMOTE_ADDR"], $cc_diff_sec); // 阈值内最后访问
                if ($last != "") {
                    $lastTime = explode("<=U-D=>", $conn->lIndex($_SERVER["REMOTE_ADDR"], $cc_diff_sec))[1];
                    if (intval(time()) - intval($lastTime) < 60) {
                        blockPage($block_page);
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Redis 连接或操作失败
        // 连接不上怎么办？为了用户体验不能拒绝访问，但是也降低了安全性
    }
}


?>