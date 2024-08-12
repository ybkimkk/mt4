<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

//----------------------------------------------------

$PayCode = 'crownpay365';

loginfo_debug('pay_in_s2s_' . $PayCode);

require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/crownpay365/config.php');

$DRPay = $DB->getDRow("select * from t_pay where PayCode = '{$PayCode}'");
if (!$DRPay) {
	echo 'Payment method does not exist';
	exit;
}

$res = new usdt(trim($DRPay['submit_gateway']),trim($DRPay['PayKey']),trim($DRPay['PaySignKey']));

$data = $res->notify();





