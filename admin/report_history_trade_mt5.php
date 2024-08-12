<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if ($DRAdmin['ver'] != '5') {
	$url = $_SERVER["REQUEST_URI"];
	$url = str_ireplace('report_history_trade_mt5.php','report_history_trade.php',$url);
	FRedirect($url);
}

if ($Clause == 'saveuser') {

}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('report_history_trade_mt5/' . $Clause . '.php');

