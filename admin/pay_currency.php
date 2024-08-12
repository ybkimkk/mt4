<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'getinfo'){
	$Id = FPostInt('id');
	
	$data = $DB->getDRow("select * from `t_pay_currency` where id = '{$Id}'");

	ajaxReturn($data,'',1);
}else if($Clause == 'updatestatus'){
	admin_action_log();

	$Id = FPostInt('id');
	$Status = FPostInt('status');
	
	$DB->query("update `t_pay_currency` set `f_status` = '{$Status}' where id = '{$Id}'");

	ajaxReturn('',L("设置成功"),1);
}else if($Clause == 'saveinfo'){
	admin_action_log();

	$Id = FPostInt('Id');
	
	$f_title = FPostStr('f_title');
	if(strlen($f_title) <= 0){
		ajaxReturn('',L('名称') . ' ' . L('不能为空'),0);
	}
	
	$f_pa = FPostStr('f_pa');
	if(strlen($f_pa) <= 0){
		ajaxReturn('',L('货币符号') . ' ' . L('不能为空'),0);
	}
	
	$f_ers = FPostStr('f_ers');
	if(strlen($f_ers) <= 0){
		ajaxReturn('',L('汇率来源') . ' ' . L('不能为空'),0);
	}
	
	$f_fixedER = floatval(FPostStr('f_fixedER'));
	if($f_ers == 'fixed' && $f_fixedER <= 0){
		ajaxReturn('',L('入金汇率') . ' ' . L('不能为空'),0);
	}
	
	$f_fixedEROut = floatval(FPostStr('f_fixedEROut'));
	if($f_ers == 'fixed' && $f_fixedEROut <= 0){
		ajaxReturn('',L('出金汇率') . ' ' . L('不能为空'),0);
	}

	$f_symbolsER = FPostStr('f_symbolsER');
	if($f_ers == 'auto' && strlen($f_symbolsER) <= 0){
		ajaxReturn('',L('自动读取汇率') . ' ' . L('不能为空'),0);
	}
	
	$f_erAlgo = FPostStr('f_erAlgo');
	if(strlen($f_erAlgo) <= 0){
		ajaxReturn('',L('汇率算法') . ' ' . L('不能为空'),0);
	}
	
	$sqlFVArr = array(
		'f_title'=>$f_title,
		'f_pa'=>$f_pa,
		'f_ers'=>$f_ers,
		'f_fixedER'=>$f_fixedER,
		'f_fixedEROut'=>$f_fixedEROut,
		'f_symbolsER'=>$f_symbolsER,
		'f_erAlgo'=>$f_erAlgo,
	);
	if($Id <= 0){
		$sqlFVArr['f_status'] = 1;
		$sqlFVArr['f_addTime'] = FNow();
		
		$affectId = $DB->insert('t_pay_currency',$sqlFVArr);
	}else{
		$affectId = $DB->update('t_pay_currency',$sqlFVArr,"where Id = '{$Id}'");
	}

	ajaxReturn('',L("保存成功"),1);
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('pay_currency/' . $Clause . '.php');

