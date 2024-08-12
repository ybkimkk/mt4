<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'getinfo'){
	$Id = FPostInt('id');
	
	$data = $DB->getDRow("select * from `t_pay` where Id = '{$Id}'");

	ajaxReturn($data,'',1);
}else if($Clause == 'updatestatus'){
	admin_action_log();

	$Id = FPostInt('id');
	$Status = FPostInt('status');
	
	$DB->query("update `t_pay` set `Status` = '{$Status}' where Id = '{$Id}'");

	ajaxReturn('',L("设置成功"),1);
}else if($Clause == 'saveinfo'){
	admin_action_log();

	$Id = FPostInt('Id');
	
	$PayCode = FPostStr('PayCode');
	if($Id <= 0){
		if(strlen($PayCode) <= 0){
			ajaxReturn('',L('支付代码') . ' ' . L('不能为空'),0);
		}
	}
	
	$PayName = FPostStr('PayName');
	if(strlen($PayName) <= 0){
		ajaxReturn('',L('支付名称') . ' ' . L('不能为空'),0);
	}
	
	$PayKey = FPostStr('PayKey');
	if(strlen($PayKey) <= 0){
		ajaxReturn('',L('商户号') . ' ' . L('不能为空'),0);
	}
	
	$PaySignKey = FPostStr('PaySignKey');
	if(strlen($PaySignKey) <= 0){
		ajaxReturn('',L('签名KEY') . ' ' . L('不能为空'),0);
	}

	$NotifyUrl = FPostStr('NotifyUrl');
	if(strlen($NotifyUrl) <= 0){
		//ajaxReturn('',L('通知URL') . ' ' . L('不能为空'),0);
	}
	
	$ReturnUrl = FPostStr('ReturnUrl');
	if(strlen($ReturnUrl) <= 0){
		//ajaxReturn('',L('系统返回URL') . ' ' . L('不能为空'),0);
	}
	
	$DRChk = $DB->getDRow("select * from `t_pay` where `PayCode` = '{$PayCode}' and Id <> '{$Id}'");
	if($DRChk){
		ajaxReturn('',L('当前支付方式已经存在'),0);
	}
	
	$sqlFVArr = array(
		'PayName'=>$PayName,
		'PayKey'=>$PayKey,
		'f_payFolder'=>FPostStr('f_payFolder'),
		'PaySignKey'=>$PaySignKey,
		//'PartnerId'=>FPostStr('PartnerId'),
		//'PartnerKey'=>FPostStr('PartnerKey'),
		//'NotifyUrl'=>$NotifyUrl,
		//'ErrorUrl'=>FPostStr('ErrorUrl'),
		//'ReturnUrl'=>$ReturnUrl,
		'maxpaynumber'=>FPostStr('maxpaynumber'),
		'access_group'=>FPostStr('access_group'),
		'description'=>FPostStr('description'),
		//'lang'=>FPostStr('lang'),
		'f_currencyId'=>FPostInt('f_currencyId'),
		'sort'=>FPostStr('sort'),
		'f_fillMorePayInfo'=>FPostInt('f_fillMorePayInfo'),
		'submit_gateway'=>FPostStr('submit_gateway'),
		'f_pic1'=>FPostStr('pic1'),
	);
	if($Id <= 0){
		$sqlFVArr['PayCode'] = $PayCode;
		$sqlFVArr['server_id'] = $DRAdmin['server_id'];
		$sqlFVArr['CreateTime'] = time();
		$sqlFVArr['Status'] = 1;
		
		$affectId = $DB->insert('t_pay',$sqlFVArr);
	}else{
		$affectId = $DB->update('t_pay',$sqlFVArr,"where Id = '{$Id}'");
	}

	ajaxReturn('',L("保存成功"),1);
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('pay_config/' . $Clause . '.php');

