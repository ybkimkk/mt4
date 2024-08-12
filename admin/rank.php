<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'getinfo'){
	$Id = FPostInt('id');
	
	$data = $DB->getDRow("select * from `t_ib_rank` where id = '{$Id}'");

	ajaxReturn($data,'',1);
}else if($Clause == 'saveinfo'){
	admin_action_log();
	
	$sqlFVArr = array(
		'rank_name'=>FPostStr('rank_name'),
		'rank'=>FPostInt('rank'),
		'total_deposit'=>FPostInt('total_deposit'),
		'total_customer'=>FPostInt('total_customer'),
		'total_volume'=>FPostInt('total_volume'),
		'customer_level'=>FPostStr('customer_level'),
		'volume_unit'=>FPostStr('volume_unit'),
		'contain_self'=>FPostStr('contain_self'),
		'scale'=>FPostStr('scale'),
		'model_type'=>FPostStr('model_type'),
	);

	if (strlen($sqlFVArr['rank_name']) <= 0) {
		ajaxReturn('',L('等级称不能为空'),0);
	}
	if ($sqlFVArr['rank'] <= 0) {
		ajaxReturn('',L('等级值不能为空'),0);
	}
	if ($sqlFVArr['total_deposit'] <= 0) {
		ajaxReturn('',L('入金量不能为空'),0);
	} 
	if ($sqlFVArr['total_customer'] <= 0) {
		ajaxReturn('',L('客户量不能为空'),0);
	}
	if ($sqlFVArr['total_volume'] <= 0) {
		ajaxReturn('',L('交易数不能为空'),0);
	}
	
	$Id = FPostInt('id');
	
	$DRChk = $DB->getDRow("select * from `t_ib_rank` where `rank` = '{$sqlFVArr['rank']}' and `model_type` = '{$sqlFVArr['model_type']}' and `status` = 1 and server_id = '{$DRAdmin['server_id']}' and id <> '{$Id}'");
	if ($DRChk) {
		ajaxReturn('',L('当前等级已经存在'),0);
	}

	//----------------------
	if($Id > 0){
		$sqlFVArr['update_time'] = time();
		
		$affectId = $DB->update('t_ib_rank',$sqlFVArr,"where id = '{$Id}'");
	}else{
		$sqlFVArr['create_time'] = time();
		$sqlFVArr['server_id'] = $DRAdmin['server_id'];
		$sqlFVArr['update_time'] = time();
		$sqlFVArr['status'] = "1";
		
		$affectId = $DB->insert('t_ib_rank',$sqlFVArr);
	}

	ajaxReturn('',L("保存成功"),1);
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$where = "where id = '{$Id}' and `status` >= 0";
	$DB->query("update `t_ib_rank` set `status` = -1 {$where}");
	
	ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('rank/' . $Clause . '.php');

