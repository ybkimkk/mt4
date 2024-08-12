<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

//----------------------------------------------------

require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/usepay/_config.php');

$PayCode = 'usepay';

loginfo_debug('pay_in_s2s_' . $PayCode);

$DRPay = $DB->getDRow("select * from t_pay where PayCode = '{$PayCode}'");
if (!$DRPay) {
	echo 'Payment method does not exist';
	exit;
}

$ciphertext = isset($_REQUEST['ciphertext'])?$_REQUEST['ciphertext']:'';
$rsahelper = new RSAHelper(trim($DRPay['PaySignKey']));
$jsonData = @$rsahelper->publicDecrypt($ciphertext);

unset($rsahelper);

if(empty($jsonData)){exit('FAIL1');}

$arrayData = @json_decode($jsonData,true);
if(empty($arrayData)){exit('FAIL2');}

if(isset($arrayData['paystatus']) && $arrayData['paystatus']==1){
	//判定支付成功，商户可将业务代码写在此处。
	//以下是部分常用字段，更多字段说明请参考API文档
    //$arrayData['amount'];//支付的金额
    //$arrayData['requestid'];//支付的订单号
    //$arrayData['orderno'];//支付平台系统订单号
	//$arrayData['accNo'];//客户支付卡号(如无数据将返回空)
    //$arrayData['paystatus'];//支付状态【1，支付成功；0，待确认；-1，支付失败；-2，金额异常；-3，支付已取消；-4，未知的支付异常】
    //$arrayData['paystatusdesc'];//支付状态描述
	
	$systime = date("Y-m-d h:i:s");

	pay_updateOrder($arrayData['requestid'], $arrayData['amount'], $systime, '1', '支付成功', $arrayData['orderno'], '', '', '', $PayCode);
	
	exit('SUCCESS');
}else{
	echo "state fail";
	exit;
}




