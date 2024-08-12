<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/include/function.php');

function timer_save_log(){
	global $logStr;
	$fp = fopen('logs/' . date('Y-m-d-H') . '.txt', 'a+b');
	fwrite($fp, date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']) . "\r\n\r\n");
	fwrite($fp, $logStr);
	fwrite($fp, "----------------\r\n\r\n");
	fclose($fp);
	exit;
}

if(FGetStr('key') != '5798432'){
	echo 'KEY错误';
	exit;
}

$logStr = '';

plugin_tc_api_update_webtv($debug = 1);

plugin_tc_api_update_ta($debug = 1);

//执行 ta图片下载

//执行 webtv下载

timer_save_log();