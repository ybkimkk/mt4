<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'refusemt4'){
	admin_action_log();

	$Id = FPostInt('sid');
	$info = $DB->getDRow("select * from t_bankcode where id = '{$Id}'");
	if ($info) {
		$reply = FPostStr('reply');
		if (!$reply) {
			this_error(L("请填写拒绝原因"));
		}
		
		$data = array();
		$data['remark'] = $reply;
		$data['status'] = '2';
		$data['checktime'] = time();
		$DB->update("t_bankcode",$data,"where id = '{$Id}'");
		
		this_success(L("操作成功"));
	} else {
		this_error(L("数据不存在"));
	}
}else if($Clause == 'viewcheck_bank'){
	admin_action_log();

	$Id = FPostInt('id');
	
	$data = array();
	$data['status'] = 1;
	$data['checktime'] = time();
	
	$DB->update("t_bankcode",$data,"where id = '{$Id}'");

	this_success(L("操作成功"));
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('deposit_bank/' . $Clause . '.php');

