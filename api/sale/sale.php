<?php
header("Content-type: text/html;charset=utf-8");
define('CC_WEB_IS_DEBUG', 0);
if(CC_WEB_IS_DEBUG){
	error_reporting(E_ALL & ~E_NOTICE);
	@ini_set("display_errors", "On");
}else{
	error_reporting(E_ERROR | E_PARSE);
	@ini_set("display_errors", "Off");
}
@date_default_timezone_set('Asia/Shanghai');
@ini_set('Asia/Shanghai');
@set_time_limit(0);

//------------------------------------------------------

$domain = 'https://office.am-broker.com';

//------------------------------------------------------

function postData($url, $data) {
    $ch = curl_init();
    $timeout = 300;
    //$header = 'Content-type: text/json';//定义content-type为json
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设置是否返回信息
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置HTTP头

	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    $handles = curl_exec($ch);
    if (curl_errno($ch)) {//出错则显示错误信息
       // return curl_error($ch);
    }
    curl_close($ch);
    return $handles;
}

postData($domain . '/api/sale/run.php?secret=3b118bb41a666d250c7d9d2efcfa9040&taskId=1&daemon=1',array());

echo 'call ok';