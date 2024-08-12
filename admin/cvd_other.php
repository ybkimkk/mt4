<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if ($Clause == 'jspost') {
	$id = FPostInt('id');

	$rs = $DB->getDRow("select * from t_sale_commission_other where id = '{$id}'");
	if(!$rs){
		this_error(L("信息不存在"));
	}
	if($rs['f_isJs'] > 0) {
		this_error(L("该信息已经发放，不可重复发放"));
	}

	admin_action_log();

	if($rs['f_cal'] > 0){
		$DB->query("update t_member set amount = amount + (" . $rs['f_cal'] . ") where id = '{$rs['f_uid']}'");
	}

	$DB->query("update t_sale_commission_other set f_isJs = 1,f_jsTime = '" . date('Y-m-d H:i:s') . "' where id = '{$id}'");

	this_success(L("操作成功"));
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('cvd_other/' . $Clause . '.php');

