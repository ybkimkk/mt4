<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'getinfo'){
	$Id = FPostInt('id');
	
	$data = $DB->getDRow("select * from `t_credit_setting` where Id = '{$Id}'");
	if(intval($data['EndTime']) > 0){
		$data['end_time'] = date('Y-m-d', $data['EndTime']);
	}
	if(intval($data['f_startTime']) > 0){
		$data['f_startTime'] = date('Y-m-d', $data['f_startTime']);
	}

	ajaxReturn($data,'',1);
}else if($Clause == 'saveinfo'){
	admin_action_log();
	
	$sqlFVArr = array(
		'Name'=>FPostStr('Name'),
		'Type'=>FPostStr('Type'),
		'Scale'=>FPostStr('Scale'),
		'Condition'=>floatval(FPostStr('Condition')),
		'Result'=>floatval(FPostStr('Result')),
		'ServerId'=>$DRAdmin['server_id'],
		'CreateTime'=>time(),
		'UpdateTime'=>time(),
		'CreateUser'=>$DRAdmin['id'],
		'Memo'=>FPostStr('Memo'),
		'UpdateUser'=>$DRAdmin['id'],
		'Status'=>0,
		
		'f_conditionEnd'=>floatval(FPostStr('f_conditionEnd')),
		'f_startTime'=>FPostStr('f_startTime'),
		'f_overDays'=>FPostInt('f_overDays'),
		
		'f_zye_days'=>FPostInt('f_zye_days'),
		'f_zye_lot'=>floatval(FPostStr('f_zye_lot')),
		'f_zye_keepTimeSecond'=>FPostInt('f_zye_keepTimeSecond'),
		'f_activityId'=>FPostInt('f_activityId'),
	);
	if(strlen(FPostStr('EndTime')) > 0){
		$sqlFVArr['EndTime'] = strtotime(FPostStr('EndTime') . ' 23:59:59');
	}else{
		ajaxReturn('',L('结束日期不能为空'),0);
	}
	if(strlen(FPostStr('f_startTime')) > 0){
		$sqlFVArr['f_startTime'] = strtotime(FPostStr('f_startTime') . ' 00:00:00');
	}else{
		ajaxReturn('',L('开始日期不能为空'),0);
	}
	
	
	//
	if($sqlFVArr['f_zye_days'] > 0 && $sqlFVArr['f_overDays'] > 0){
		if($sqlFVArr['f_zye_days'] >= $sqlFVArr['f_overDays']){
			ajaxReturn('',L('赠金产生后转余额天数 必须小于 赠金到期扣回天数'),0);
		}
	}
	
	
	if ($sqlFVArr['Type'] == 'REG') {
		$sqlFVArr['f_group'] = '';
		$sqlFVArr['Scale'] = 'Fixed';
	}else{
		if(!$_POST['GROUP_NAME']){
			this_error(L('请选择MT分组'));
		}
		if (in_array('all_group',$_POST['GROUP_NAME'])) {
			$arr_group = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'", true);
		}else{
			$arr_group = $_POST['GROUP_NAME'];
			
			foreach($arr_group as $key=>$val){
				$tempRs = $DB->getDRow("select * from t_groups where `group` = '" . str_replace('\\','\\\\',$val) . "' and server_id = '{$DRAdmin['server_id']}'"); //查询交易种类
				if (!$tempRs) {
					this_error(L('当前MT分组不存在') . "：" . $val);
				}
			}
		}
		$sqlFVArr['f_group'] = implode(',',$arr_group);
		if (!$sqlFVArr['f_group']) {
			this_error(L('请选择MT分组'));
		}
		$sqlFVArr['f_group'] = ',' . $sqlFVArr['f_group'] . ',';
	}
	
	
	
	
	$sqlFVArr['f_zye_symbol'] = '';
	if($_POST['zye_symbol']){
		if (in_array('all_zye_symbol',$_POST['zye_symbol'])) {
			$arr_zye_symbol = $DB->getField("select type_name from t_type where server_id = '" . $DRAdmin['server_id'] . "' and status = 1", true);
		}else{
			$arr_zye_symbol = $_POST['zye_symbol'];
			
			foreach($arr_zye_symbol as $key=>$val){
				$tempRs = $DB->getDRow("select * from t_type where type_name = '" . str_replace('\\','\\\\',$val) . "' and server_id = '{$DRAdmin['server_id']}'"); //查询交易种类
				if (!$tempRs) {
					this_error(L('产品不存在') . "：" . $val);
				}
			}
		}
		$sqlFVArr['f_zye_symbol'] = implode(',',$arr_zye_symbol);
		$sqlFVArr['f_zye_symbol'] = ',' . $sqlFVArr['f_zye_symbol'] . ',';
	}

	
	
	
	
	

	if (strlen($sqlFVArr['Name']) <= 0) {
		ajaxReturn('',L('赠金规则不能为空'),0);
	}
	if (strlen($sqlFVArr['Type']) <= 0) {
		ajaxReturn('',L('达标类型不能为空'),0);
	}

	if ($sqlFVArr['Type'] != 'REG') {
		if ($sqlFVArr['Condition'] <= 0) {
			ajaxReturn('',L('达标数值不能为空'),0);
		} 
		if ($sqlFVArr['f_conditionEnd'] <= 0) {
			ajaxReturn('',L('达标数值不能为空'),0);
		} 
	}

	if ($sqlFVArr['Result'] <= 0) {
		ajaxReturn('',L('达标赠金数值不能为空'),0);
	}
	if (strlen($sqlFVArr['Scale']) <= 0) {
		ajaxReturn('',L('达标赠金类型不能为空'),0);
	}
	if ($sqlFVArr['f_overDays'] <= 0) {
		ajaxReturn('',L('赠金到期扣回不能为空'),0);
	}
	
	$Id = FPostInt('Id');
	
	//检测重复：
	/*
	//在冲突的时间内，达标类型相同、达标数值冲突
	$sql = '';
	$sql .= "select * from `t_credit_setting` where Id <> '{$Id}' and `Type` = '{$sqlFVArr['Type']}'";
	$sql .= " and (f_startTime = 0 and EndTime > )";
	//放弃。。。太复杂
	
	$DRChk = $DB->getDRow("select * from `t_credit_setting` where `Type` = '{$sqlFVArr['Type']}' and `Condition` = '{$sqlFVArr['Condition']}' and `Status` in (0,1) and ServerId = '{$DRAdmin['server_id']}' and Id <> '{$Id}'");
	if ($DRChk) {
		if($DRChk['Status'] <= 0){
			ajaxReturn('',L('您平台有相应的赠金规则未发布，请发布即可'),0);
		}else{
			ajaxReturn('',L('您平台有相应的赠金规则在运行中'),0);
		}
	}
	*/
	
	//----------------------
	if($Id > 0){
		/*//把原数据停掉
		$DB->query("update `t_credit_setting` set Status = '-2' where Id = '{$Id}'");
		
		//增加新的数据	
		$sqlFVArr['RelId'] = $Id;	
		$affectId = $DB->insert('t_credit_setting',$sqlFVArr);
		
		//这样可以防止规则并后不一致导致的核对问题*/
		
		$affectId = $DB->update('t_credit_setting',$sqlFVArr,"Id = '{$Id}'");
	}else{
		$affectId = $DB->insert('t_credit_setting',$sqlFVArr);
	}

	ajaxReturn('',L("保存成功"),1);
}else if($Clause == 'savesettingstatus'){
	admin_action_log();

	$Id = FPostInt('ID');
	$STATUS = FPostStr('STATUS');
	$data = $DB->getDRow("select * from t_credit_setting where Id = '{$Id}'");
	if ($data) {
		if ($data['status'] == $STATUS) {
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_credit_setting set `status` = '{$STATUS}' where Id = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
	exit;
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$where = "where Id = '{$Id}' and `status` >= 0";
	$DB->query("update `t_credit_setting` set `status` = -1 {$where}");
	
	ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('credit/' . $Clause . '.php');

