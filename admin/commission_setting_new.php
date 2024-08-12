<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();

	$data = array();
	$data['ACCOUNT'] = FPostStr('ACCOUNT');
	
	$data['MODEL_TYPE'] = FPostStr('MODEL_TYPE');
	
	$data['LEVEL'] = FPostInt('LEVEL');
	if ($data['LEVEL'] <= 0) {
		if($data['MODEL_TYPE'] != 'direct'){
			this_error(L('请选择等级'));
		}
	}

	if(!$_POST['SYMBOL_TYPE']){
		this_error(L('请选择交易种类'));
	}
	if (in_array('all_symbol',$_POST['SYMBOL_TYPE'])) {
		$arr_symbol = $DB->getField("select type_name from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1", true);
	}else{
		$arr_symbol = $_POST['SYMBOL_TYPE'];
		
		foreach($arr_symbol as $key=>$val){
			$tempRs = $DB->getDRow("select * from t_type where type_name = '{$val}' and status = 1 and server_id = '{$DRAdmin['server_id']}'"); //查询交易种类
			if (!$tempRs) {
				this_error(L('当前交易种类不存在') . "：" . $val);
			}
		}
	}
	$data['SYMBOL_TYPE'] = implode(',',$arr_symbol);
	if (!$data['SYMBOL_TYPE']) {
		this_error(L('请选择交易种类'));
	}

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
	$data['GROUP_NAME'] = implode(',',$arr_group);
	if (!$data['GROUP_NAME']) {
		this_error(L('请选择MT分组'));
	}

	$data['CAL_TYPE_ZK'] = FPostStr('CAL_TYPE_ZK');
	$data['CAL_NUM_ZK'] = floatval(FPostStr('CAL_NUM_ZK'));
	
	$data['CAL_TYPE_JC'] = FPostStr('CAL_TYPE_JC');
	$data['CAL_NUM_JC'] = floatval(FPostStr('CAL_NUM_JC'));
	
	//----------

	if($data['MODEL_TYPE'] == 'direct'){
		if($data['CAL_NUM_ZK'] <= 0){
			this_error(L('请填写 直接客户返佣标准 数值'));
		}
	}else{
		$data['BONUS_TYPE'] = FPostInt('BONUS_TYPE');
		
		if($data['BONUS_TYPE'] >= 1){
			//外佣
			if($data['CAL_NUM_ZK'] <= 0){
				this_error(L('请填写 直接客户返佣标准 数值'));
			}
		}else{
			//内佣
			$data['CAL_TYPE_JJ_2'] = FPostStr('CAL_TYPE_JJ_2');
			$data['CAL_NUM_JJ_2'] = floatval(FPostStr('CAL_NUM_JJ_2'));
			
			$data['CAL_TYPE_JJ_1'] = FPostStr('CAL_TYPE_JJ_1');
			$data['CAL_NUM_JJ_1'] = floatval(FPostStr('CAL_NUM_JJ_1'));
			
			$data['CAL_TYPE_JJ_0'] = FPostStr('CAL_TYPE_JJ_0');
			$data['CAL_NUM_JJ_0'] = floatval(FPostStr('CAL_NUM_JJ_0'));
			
			$data['CAL_NUM_GROUP_2'] = floatval(FPostStr('CAL_NUM_GROUP_2'));
			$data['CAL_NUM_GROUP_1'] = floatval(FPostStr('CAL_NUM_GROUP_1'));
			$data['CAL_NUM_GROUP_0'] = floatval(FPostStr('CAL_NUM_GROUP_0'));
		
			$data['MODEL_JG_CAL_TYPE'] = FPostStr('MODEL_JG_CAL_TYPE');
			
			//----------
			
			if($data['CAL_NUM_ZK'] < 0 || 
				$data['CAL_NUM_JJ_2'] < 0 || 
				$data['CAL_NUM_JJ_1'] < 0 || 
				$data['CAL_NUM_JJ_0'] < 0 || 
				$data['CAL_NUM_GROUP_2'] < 0 || 
				$data['CAL_NUM_GROUP_1'] < 0 || 
				$data['CAL_NUM_GROUP_0'] < 0){
					this_error(L('数值不能为负数'));
			}
			
			if($data['CAL_NUM_ZK'] <= 0 && 
				$data['CAL_NUM_JJ_2'] <= 0 && 
				$data['CAL_NUM_JJ_1'] <= 0 && 
				$data['CAL_NUM_JJ_0'] <= 0 && 
				$data['CAL_NUM_GROUP_2'] <= 0 && 
				$data['CAL_NUM_GROUP_1'] <= 0 && 
				$data['CAL_NUM_GROUP_0'] <= 0){
					this_error(L('数值 不能全部为0'));
			}
			
			if(strlen($data['MODEL_JG_CAL_TYPE']) <= 0){
				//this_error(L('其它项，请选择'));
			}
		}
	}
	
	
	//检测重复：返佣模式、等级、交易种类、MT分组、返佣模式
	foreach ($arr_symbol as $symbolkey => $symbolval) {
		foreach ($arr_group as $groupkey => $groupval) {
			$map = array();
			$map["SERVER_ID"] = $DRAdmin['server_id'];
			$map["STATUS"] = array("in", "1,3");
			$map['ACCOUNT'] = intval($data['ACCOUNT']);
			$map["MODEL_TYPE"] = $data['MODEL_TYPE'];
			$map['LEVEL'] = $data['LEVEL'];
			$map['SYMBOL_TYPE'] = array('like', '%,' . $symbolval . ',%');
			$map['GROUP_NAME'] = array('like', '%,' . str_replace('\\','\\\\\\\\',$groupval) . ',%');
			$map["BONUS_TYPE"] = intval($data['BONUS_TYPE']);
			$map["ID"] = array("neq", $Id);
			
			$mapStr = cz_where_to_str($map);
			
			//echo "select * from t_sale_setting_new {$mapStr}";exit;
			
			$result = $DB->getDRow("select * from t_sale_setting_new {$mapStr}");
			if($result){
				this_error("与其它返佣重复，请检查");//：【" . $symbolval . "】【" . $groupval . "】
			}
		}
	}

	//1=启用，2=备份（修改时，备份、然后新增记录），3=禁用，4=删除
	$data['STATUS'] = 1;

	//如果是修改，备份、然后新增记录
	if ($Id > 0) {
		$DB->query("update t_sale_setting_new set `STATUS` = 2,UPDATE_TIME = '" . FNow() . "' where ID = '{$Id}'");
	}
	
	$data['SYMBOL_TYPE'] = ',' . $data['SYMBOL_TYPE'] . ',';
	$data['GROUP_NAME'] = ',' . $data['GROUP_NAME'] . ',';

	$data['CREATE_TIME'] = FNow();
	$data['UPDATE_TIME'] = FNow();
	$data["SERVER_ID"] = $DRAdmin['server_id'];

	$DB->insert("t_sale_setting_new",$data);
	
	this_success(L('操作成功'));
}else if($Clause == 'savesettingstatus'){
	admin_action_log();

	$Id = FPostInt('ID');
	$STATUS = FPostStr('STATUS');
	$data = $DB->getDRow("select * from t_sale_setting_new where ID = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == $STATUS) {
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_sale_setting_new set `STATUS` = '{$STATUS}' where ID = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
	exit;
}else if($Clause == 'deletesetting'){
	admin_action_log();

	$Id = FPostInt('ID');
	$data = $DB->getDRow("select * from t_sale_setting_new where ID = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == '4') {//删除
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_sale_setting_new set `STATUS` = '4' where ID = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
}


if(!in_array($Clause,array('main','addinfo'))){
	$Clause = 'main';
}
require_once('commission_setting_new/' . $Clause . '.php');



