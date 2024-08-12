<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

//----------------------------------------------------

//差别在这里
$PayCode = 'usdt1';

loginfo_debug('pay_in_s2s_' . $PayCode);

$getarray = $_REQUEST;
if (empty($getarray)) {
	echo '非法回调';
	exit;
}
if ($getarray['paystate'] == 'success') {//支付成功
	$DRPay = $DB->getDRow("select * from t_pay where PayCode = '{$PayCode}'");
	if (!$DRPay) {
		echo '支付方式不存在';
		exit;
	}
	
	$payInfo['merid'] = $getarray['merid'];   
	$payInfo['paytypekey'] = $getarray['paytypekey'];
	$payInfo['orderid'] = $getarray['orderid'];
	$payInfo['amount'] = $getarray['amount'];
	$payInfo['tradeid'] = $getarray['tradeid'];
	$payInfo['paystate'] = $getarray['paystate'];

	//1、按key先排序
	ksort($payInfo);
	reset($payInfo);
	//2、把值串接一起
	$payInfo2Str = implode('',$payInfo);
	//3、再串上API密钥
	$payInfo2Str .= $DRPay['PaySignKey'];
	//4、进行32位的md5加密，结果即是签名
	$sign = md5($payInfo2Str);
	//5、签名要转换为小写字母的字符串（md5加密结果本身就是小写字母的字符串，这里不管，执行转换为小写，因为它是一个处理逻辑，适用于其它任意语言，如.NET、JAVA）
	$sign = strtolower($sign);

	if ($sign == $getarray['sign']) {//结果验证签名正确
		$systime = date("Y-m-d h:i:s");

		pay_updateOrder($getarray['orderid'], $getarray['amount'], $systime, '1', '支付成功', $getarray['tradeid'], '', '', '', $PayCode);
		
		echo "_CC_PAY_STATE_SUCCESS_";
		exit;
	} else {
		echo '签名失败';
		exit;
	} 
}else {
	echo "fail";
	exit;
}

