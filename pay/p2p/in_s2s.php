<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

//----------------------------------------------------

$PayCode = 'p2p';

loginfo_debug('pay_in_s2s_' . $PayCode);

$getarray = $_POST;
/*
$GLOBALS["HTTP_RAW_POST_DATA"];
if (!$getarray) {
	$getarray = file_get_contents("php://input");
}
*/
if (!$getarray) {
	$getarray = $_GET;
	foreach($getarray as $key=>$val){
		if($val == 'null'){
			$getarray[$key] = '';
		}
	}
}
if (!$getarray) {
	echo 'Illegal callback';
	exit;
}
if ($getarray['status'] == 'SUCCEEDED') {//支付成功
	$DRPay = $DB->getDRow("select * from t_pay where PayCode = '{$PayCode}'");
	if (!$DRPay) {
		echo 'Payment method does not exist';
		exit;
	}
	
	ksort($getarray);
	reset($getarray);
	
	$signStr = '';
	foreach($getarray as $key=>$val){
		if(strlen($val) > 0 && $key != 'signature'){
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' . trim($DRPay['PaySignKey']);
	
	$chkSign = strtoupper(md5($signStr));
	
	if($chkSign != $getarray['signature']){
		echo 'Signature error';
		exit;
	}

	echo "success";
	
	$systime = date("Y-m-d h:i:s");

	pay_updateOrder($getarray['outTradeNo'], $getarray['amount'], $systime, '1', '支付成功', $getarray['transId'], '', '', '', $PayCode);
	
	exit;
}else {
	echo "state fail";
	exit;
}

