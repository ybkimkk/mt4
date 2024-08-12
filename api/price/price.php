<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

/*header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Expose-Headers: *");

header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");*/

header('Content-type:text/json');

$server = $DB->getDRow("select * from t_mt4_server where `status` = 1 order by `real` desc,default_open_svr desc");
if(!$server) {
	echo json_encode(array('state'=>'fail','msg'=>'mt4_server no find'));
	exit;
}

$symbol = FGetStr('symbol');
if(strlen($symbol) <= 0){
	echo json_encode(array('state'=>'fail','msg'=>'symbol is null'));
	exit;
}
$symbol = explode(',',$symbol);

if($server['ver'] == 5){
	$query = $DB->query("select * from " . $server['db_name'] . ".mt5_prices where Symbol in ('" . implode("','",$symbol) . "')");
	$arr = array('state'=>'success');
	while($rs = $DB->fetchArray($query)){
		$arr['symbol'][$rs['Symbol']] = array('BID'=>number_format($rs['BidLast'],5,'.','')*1,'ASK'=>number_format($rs['AskLast'],5,'.','')*1);
	}
	
	echo json_encode($arr);
	exit;
}else{
	$query = $DB->query("select * from " . $server['db_name'] . ".mt4_prices where SYMBOL in ('" . implode("','",$symbol) . "')");
	$arr = array('state'=>'success');
	while($rs = $DB->fetchArray($query)){
		$arr['symbol'][$rs['SYMBOL']] = array('BID'=>number_format($rs['BID'],5,'.','')*1,'ASK'=>number_format($rs['ASK'],5,'.','')*1);
	}
	
	echo json_encode($arr);
	exit;
}

