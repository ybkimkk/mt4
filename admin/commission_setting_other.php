<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();

	$data = array();

	$data['f_title'] = FPostStr('f_title');

	$data['MODEL_TYPE'] = FPostStr('MODEL_TYPE');
	
	$data['LEVEL'] = FPostInt('LEVEL');

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


	$data['GROUP_TYPE'] = FPostInt('GROUP_TYPE');
	if($data['GROUP_TYPE'] <= 0){
		this_error(L('请选择 统计团队'));
	}

	$data['TC_DATE_S'] = FPostStr('TC_DATE_S');
	$data['TC_DATE_E'] = FPostStr('TC_DATE_E');
	if(strlen($data['TC_DATE_S']) <= 0){
		this_error(L('平仓时间 请填写完整'));
	}
	if(strlen($data['TC_DATE_E']) <= 0){
		this_error(L('平仓时间 请填写完整'));
	}
	$data['TC_DATE_S'] .= ' 00:00:00';
	$data['TC_DATE_E'] .= ' 23:59:59';

	$data['LIMIT_MIN_SS'] = floatval(FPostStr('LIMIT_MIN_SS'));
	if($data['LIMIT_MIN_SS'] <= 0){
		this_error(L('请填写 达到手数'));
	}

	$data['CAL_TYPE_AGENT'] = FPostStr('CAL_TYPE_AGENT');
	$data['CAL_NUM_AGENT'] = floatval(FPostStr('CAL_NUM_AGENT'));
	if($data['CAL_NUM_AGENT'] <= 0){
		this_error(L('请填写 返佣标准'));
	}

	$data['SYMBOL_TYPE'] = ',' . $data['SYMBOL_TYPE'] . ',';
	$data['GROUP_NAME'] = ',' . $data['GROUP_NAME'] . ',';

	if ($Id > 0) {
		$DB->update("t_sale_setting_other",$data,"ID = '{$Id}'");
	}else{
		//1=启用，2=备份（修改时，备份、然后新增记录），3=禁用，4=删除
		$data['STATUS'] = 1;

		$data['CREATE_TIME'] = FNow();
		$data['UPDATE_TIME'] = FNow();
		$data["SERVER_ID"] = $DRAdmin['server_id'];

		$DB->insert("t_sale_setting_other",$data);
	}
	
	this_success(L('操作成功'));
}else if($Clause == 'savesettingstatus'){
	admin_action_log();

	$Id = FPostInt('ID');
	$STATUS = FPostStr('STATUS');
	$data = $DB->getDRow("select * from t_sale_setting_other where ID = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == $STATUS) {
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_sale_setting_other set `STATUS` = '{$STATUS}' where ID = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
	exit;
}else if($Clause == 'deletesetting'){
	admin_action_log();

	$Id = FPostInt('ID');
	$data = $DB->getDRow("select * from t_sale_setting_other where ID = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == '4') {//删除
			ajaxReturn($data, L('操作出错'), 1); //已经被修改过
		} else {
			$result = $DB->query("update t_sale_setting_other set `STATUS` = '4' where ID = '{$Id}'");
			ajaxReturn($data, L('操作成功'), 0);
		}
	}
	ajaxReturn($data, L('数据不存在'), 2);
}


if(!in_array($Clause,array('main','addinfo'))){
	$Clause = 'main';
}
require_once('commission_setting_other/' . $Clause . '.php');



