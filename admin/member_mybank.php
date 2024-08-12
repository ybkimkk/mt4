<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if ($Clause == 'saveinfo') {
	admin_action_log();

	$map = array();
	$map['status'] = '0';
	$map['creattime'] = time();
	$map['accountNum'] = FPostStr('accountNum');
	$map['swiftCode'] = FPostStr('swiftCode');
	$map['bankName'] = FPostStr('bankName');
	$map['accountName'] = FPostStr('accountName');
	$map['bankCard'] = FPostStr('bankCard');
	$map['remark'] = '';
	
	if(strlen($map['bankName']) <= 0){
		FJS_P_AC(L("请输入您的银行名称"));
	}
	if(strlen($map['accountName']) <= 0){
		FJS_P_AC(L("请输入您的银行开户姓名"));
	}
	if(strlen($map['accountNum']) <= 0){
		FJS_P_AC(L("请输入您的银行账号"));
	}	

	$rsChk = $DB->getDRow("select * from t_bankcode where accountNum = '{$map['accountNum']}' and member_id = '{$map['member_id']}' and id <> '{$Id}'");
	if ($rsChk) {
		if($rsChk['status'] == 1){
			FJS_P_AC(L("此银行卡已被绑定"));
		}else{
			FJS_P_AC(L("此银行卡已提交绑定，未审核，请通知管理员"));
		}
	}
	
	$map['email'] = $DB->getField("select email from t_member where id = '{$DRAdmin['id']}' and server_id = '{$DRAdmin['server_id']}'");
	
	if($Id > 0){
		$map['checktime'] = NULL;
		
		$list = $DB->update("t_bankcode",$map,"where id = '{$Id}' and member_id = '{$DRAdmin['id']}'");
	}else{
		$map['member_id'] = $DRAdmin['id'];
		$map['server_id'] = $DRAdmin['server_id'];
		
		$list = $DB->insert("t_bankcode",$map);
	}
	if ($list) {
		FJS_P_AT(L("操作成功,等待管理员审核"), '?');
	} else {
		FJS_P_AC(L("操作失败"));
	}
}else if ($Clause == 'delinfo') {
	admin_action_log();
	
	$DB->query("update `t_bankcode` set `status` = 3,checktime = '" . time() . "' where id = '{$Id}' and `member_id` = '{$DRAdmin['id']}'");
	
	ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main','addinfo'))){
	$Clause = 'main';
}
require_once('member_mybank/' . $Clause . '.php');

