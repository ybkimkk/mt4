<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();

	$data = array(
		'PayCode'=>FPostStr('PayCode'),
		'PayName'=>FPostStr('PayName'),
		'f_currencyId'=>FPostInt('f_currencyId'),
		'f_fee'=>FPostStr('f_fee'),
		'f_payIsOnline'=>FPostInt('f_payIsOnline'),
		'PayKey'=>FPostStr('PayKey'),
		'PaySignKey'=>FPostStr('PaySignKey'),
		'submit_gateway'=>FPostStr('submit_gateway'),
		'sort'=>FPostInt('sort'),
	);
	
	if($Id <= 0){
		if(strlen($data['PayCode']) <= 0){
			FJS_P_AC(L('Key') . ' ' . L('不能为空'));
		}
	}
	if(strlen($data['PayName']) <= 0){
		FJS_P_AC(L('名称') . ' ' . L('不能为空'));
	}
	if($data['f_currencyId'] <= 0){
		ajaxReturn('',L('商户号') . ' ' . L('不能为空'),0);
	}
	if($data['f_payIsOnline'] > 0){
		if(strlen($data['PayKey']) <= 0){
			FJS_P_AC(L('商户号') . ' ' . L('不能为空'));
		}
		if(strlen($data['PaySignKey']) <= 0){
			FJS_P_AC(L('密钥') . ' ' . L('不能为空'));
		}
		if(strlen($data['submit_gateway']) <= 0){
			FJS_P_AC(L('网关地址') . ' ' . L('不能为空'));
		}
	}
	
	$DRChk = $DB->getDRow("select * from `t_out_config` where `PayCode` = '{$data['PayCode']}' and Id <> '{$Id}'");
	if($DRChk){
		FJS_P_AC(L('Key') . ' ' . L('已经存在'));
	}	
	
	if($Id <= 0){
		$data['server_id'] = $DRAdmin['server_id'];
		$data['status'] = 1;
		$id = $DB->insert("t_out_config",$data);
	}else{
		$id = $DB->update("t_out_config",$data,"where id = '{$Id}'");
	}

	FJS_P_AT(L('保存成功'),'?');
}else if($Clause == 'forbid'){
	admin_action_log();
	
	$DB->query("update t_out_config set status = 0 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'resume'){
	admin_action_log();
	
	$DB->query("update t_out_config set status = 1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$DB->query("update t_out_config set status = -1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}

if(!in_array($Clause,array('main','addinfo'))){
	$Clause = 'main';
}
require_once('out_config/' . $Clause . '.php');

