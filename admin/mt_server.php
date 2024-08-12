<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveopenmt'){
	exit;
	/*
		$direct_start_number = $this->_request('direct_start_number');
		$direct_end_number = $this->_request('direct_end_number');
		$agent_start_number = $this->_request('agent_start_number');
		$agent_end_number = $this->_request('agent_end_number');
		$member_start_number = $this->_request('member_start_number');
		$member_end_number = $this->_request('member_end_number');
		
		if($direct_start_number){
			if(!is_numeric($direct_start_number)){
				$this->error(L("直客号码段最小值为数字"));
			}elseif (floatval($direct_start_number)<$server['start_number']) {
				$this->error(L("直客号码段最小值应不小于服务器号段最小值"));
			}elseif(!$direct_end_number){
				$this->error("您已经填写了直客号码段最小值，直客号码段最大值不能为空");
			}else{
				$data['direct_start_number'] = $direct_start_number;
			}
		}else{
			$data['direct_start_number'] = null;
		}
		if($direct_end_number){
			if(!is_numeric($direct_end_number)){
				$this->error(L("直客号码段最大值为数字"));
			}elseif($direct_start_number>=$direct_end_number){
				$this->error(L("直客号码段最大值应大于直客号码段最小值"));
			}elseif(floatval($direct_end_number)>$server['end_number']){
				$this->error(L("直客号码段最大值应不大于服务器号段最大值"));
			}elseif(!$direct_start_number){
				$this->error("您已经填写了直客号码段最大值，直客号码段最小值不能为空");
			}else{
				$data['direct_end_number'] = $direct_end_number;
			}
		}else{
			$data['direct_end_number'] = null;
		}
		if($agent_start_number){
			if(!is_numeric($agent_start_number)){
				$this->error(L("代理号码段最小值为数字"));
			}elseif (floatval($agent_start_number)<$server['start_number']) {
				$this->error(L("代理号码段最小值应不小于服务器号段最小值"));
			}elseif(!$agent_end_number){
				$this->error("您已经填写了代理号码段最小值，代理号码段最大值不能为空");
			}else{
				$data['agent_start_number'] = $agent_start_number;
			}
		}else{
			$data['agent_start_number'] = null;
		}
		if($agent_end_number){
			if(!is_numeric($agent_end_number)){
				$this->error(L("代理号码段最大值为数字"));
			}elseif($agent_start_number>=$agent_end_number){
				$this->error(L("代理号码段最大值应大于代理号码段最小值"));
			}elseif (floatval($agent_end_number)>$server['end_number']) {
				$this->error(L("代理号码段最大值应不大于服务器号段最大值"));
			}elseif(!$agent_start_number){
				$this->error("您已经填写了代理号码段最大值，代理号码段最小值不能为空");
			}else{
				$data['agent_end_number'] = $agent_end_number;
			}
		}else{
			$data['agent_end_number'] = null;
		}
		if($member_start_number){
			if(!is_numeric($member_start_number)){
				$this->error(L("员工号码段最小值为数字"));
			}elseif(floatval($member_start_number)<$server['start_number']){
				$this->error(L("员工号码段最小值应不小于服务器号段最小值"));
			}elseif(!$member_end_number){
				$this->error("您已经填写了员工号码段最小值，员工号码段最大值不能为空");
			}else{
				$data['member_start_number'] = $member_start_number;
			}
		}else{
			$data['member_start_number'] = null;
		}
		if($member_end_number){
			if(!is_numeric($member_end_number)){
				$this->error(L("员工号码段最大值为数字"));
			}elseif($member_start_number>=$member_end_number){
				$this->error(L("员工号码段最大值应大于员工号码段最小值"));
			}elseif (floatval($member_end_number)>$server['end_number']) {
				$this->error(L("员工号码段最大值应不大于服务器号段最大值"));
			}elseif(!$member_start_number){
				$this->error("您已经填写了员工号码段最大值，员工号码段最小值不能为空");
			}else{
				$data['member_end_number'] = $member_end_number;
			}
		}else{
			$data['member_end_number'] = null;
		}
		if($data['direct_start_number'] && $data['agent_start_number']){
			$r = $this->is_cross($data['direct_start_number'],$data['direct_end_number'],$data['agent_start_number'],$data['agent_end_number']);
			if(!$r){
				$this->error(L("号码段重复"));
			}
		}
		if($data['direct_start_number'] && $data['member_start_number']){
			$r = $this->is_cross($data['direct_start_number'],$data['direct_end_number'],$data['member_start_number'],$data['member_end_number']);
			if(!$r){
				$this->error(L("号码段重复"));
			}
		}
		if($data['agent_start_number'] && $data['member_start_number']){
			$r = $this->is_cross($data['agent_start_number'],$data['agent_end_number'],$data['member_start_number'],$data['member_end_number']);
			if(!$r){
				$this->error(L("号码段重复"));
			}
		}
		$result = M('mt4_server')->where($where)->save($data);
		if($result){
			$this->success(L("设置成功"));
		}else{
			$this->error(L("设置失败"));
		}
	*/
}else if($Clause == 'getinfo'){
	$Id = FPostInt('id');
	
	$data = $DB->getDRow("select * from `t_mt4_server` where id = '{$Id}'");

	ajaxReturn($data,'',1);
}else if($Clause == 'saveinfo'){
	admin_action_log();
	
	$Id = FPostInt('id');

	$sqlFVArr = array(
		'mt4_server'=>FPostStr('mt4_server'),
		'mt4_name'=>FPostStr('mt4_name'),
		'mt4_manager'=>FPostStr('mt4_manager'),
		'mt4_password'=>FPostStr('mt4_password'),
		'real'=>FPostInt('real'),
		'start_number'=>FPostInt('start_number'),
		'end_number'=>FPostInt('end_number'),
		'default_open_svr'=>FPostInt('default_open_svr'),
		'ver'=>FPostInt('ver'),
	);
	if(FPostStr('time_zone')){
		$sqlFVArr['time_zone'] = FPostStr('time_zone');
	}
	
	if (strlen($sqlFVArr['mt4_name']) <= 0) {
		ajaxReturn('',L('mt4服务器名称不能为空！'),0);
	}
	if (strlen($sqlFVArr['mt4_server']) <= 0) {
		ajaxReturn('',L('mt4服务器地址不能为空！！'),0);
	}
	if (strlen($sqlFVArr['mt4_manager']) <= 0) {
		ajaxReturn('',L('mt4管理员账号不能为空'),0);
	}
	if (strlen($sqlFVArr['mt4_password']) <= 0 && $Id <= 0) {
		ajaxReturn('',L('mt4管理员密码不能为空'),0);
	}

	//if($sqlFVArr['start_number']){
		if (!is_numeric($sqlFVArr['start_number'])) {
			ajaxReturn('',L('号码段最小值为数字'),0);
		}
		if(!$sqlFVArr['end_number']){
			//ajaxReturn('',L('您已经填写了号码段最小值，号码段最大值不能为空'),0);
		}	
		if (floatval($sqlFVArr['start_number'])<2) {
			ajaxReturn('',L('号码段最小值大于2'),0);
		}
	//}
	//if($sqlFVArr['end_number']){
		if (!is_numeric($sqlFVArr['end_number'])) {
			ajaxReturn('',L('号码段最大值为数字'),0);
		}		
		if (floatval($sqlFVArr['end_number'])<3) {
			ajaxReturn('',L('号码段最大值大于3'),0);
		}
		if(!$sqlFVArr['start_number']){
			//ajaxReturn('',L('您已经填写了号码段最大值，号码段最小值不能为空'),0);
		}
		if($sqlFVArr['start_number']>=$sqlFVArr['end_number']){
			ajaxReturn('',L('号码段最大值应大于号码段最小值'),0);
		}
	//}
        
	if($sqlFVArr['real']==1){
		$defaulnum = intval($DB->getField("select count(*) as count1 from `t_mt4_server` where `status` = '1' and `real` = '1' and `default_open_svr` = 1 and id <> '{$Id}'"));
		if($defaulnum > 0 && $sqlFVArr['default_open_svr'] == 1){
			ajaxReturn('',L('MT4真实仓中，默认开户MT Manage服务器只能设置一个'),0);
		}

		$truenum = intval($DB->getField("select count(*) as count1 from `t_mt4_server` where `status` = '1' and `real` = '1' and id <> '{$Id}'"));
	  
		$truemt4nums = C('OPEN_TRUE_NUMBER');//真实仓
		if(!$truemt4nums) $truemt4nums = 1;
		if($truemt4nums<=$truenum){
			ajaxReturn('',L('无法再添加真实仓'),0);
		}
	}else{
		$simunum = intval($DB->getField("select count(*) as count1 from `t_mt4_server` where `status` = '1' and `real` = '0' and id <> '{$Id}'"));
		
		$simumt4nums = C('OPEN_SIMU_NUMBER');//模拟仓
		if(!$simumt4nums) $simumt4nums = 1;
		if($simumt4nums <= $simunum){
			ajaxReturn('',L('无法再添加模拟仓'),0);
		}
	}
	
	$mt4api = new MtApiModel($sqlFVArr['ver']);
	
	//----------------------
	if($Id > 0){//更新
		$DRChk = $DB->getDRow("select * from `t_mt4_server` where id = '{$Id}'");
		if ($DRChk) {
			if($sqlFVArr['mt4_password']){//输入密码校验
				$retarry = $mt4api->connectMT4($sqlFVArr['mt4_server'], $sqlFVArr['mt4_manager'], $sqlFVArr['mt4_password']);
				if ($retarry['ret'] != 0){
					ajaxReturn('',L($retarry['info']),0);
				}
				
				if(empty($sqlFVArr['time_zone'])){
					$time_zone = $mt4api->GetTimezone();
					if($time_zone['ret'] >= -12 && $time_zone['ret'] <= 12){
					   $sqlFVArr['time_zone'] = $time_zone['ret'];
					}
				}
				
				$sqlFVArr['mt4_password'] = think_encrypt($sqlFVArr['mt4_password'], C('PASSWORD_KEY'));
			}else{
				unset($sqlFVArr['mt4_password']);
			}
		  
			$sqlFVArr['update_time'] = time();
			
			$affectId = $DB->update('t_mt4_server',$sqlFVArr,"where id = '{$Id}'");
			
			ajaxReturn('',L("保存成功"),1);
		}else{
			ajaxReturn('',L('数据不存在'),0);
		}
	}else{
		//新增
		$DRChk = $DB->getDRow("select * from `t_mt4_server` where `mt4_server` = '{$sqlFVArr['mt4_server']}' and `mt4_manager` = '{$sqlFVArr['mt4_manager']}' and `mt4_password` = '{$sqlFVArr['mt4_password']}' and `status` = 1");
		if ($DRChk) {
			ajaxReturn('',L('MT4服务器已经存在'),0);
		}
		
		$sqlFVArr['create_time'] = time();
		$sqlFVArr['update_time'] = time();
		$sqlFVArr['status'] = "1";
		
		$retarry = $mt4api->connectMT4($sqlFVArr['mt4_server'], $sqlFVArr['mt4_manager'], $sqlFVArr['mt4_password']);
		if ($retarry['ret'] != 0){
			ajaxReturn('',L($retarry['info']),0);
		}
		if(empty($sqlFVArr['time_zone'])){
			$time_zone = $mt4api->GetTimezone();
			if($time_zone['ret'] >= -12 && $time_zone['ret'] <= 12){
			   $sqlFVArr['time_zone'] = $time_zone['ret'];
			}
		}
		$sqlFVArr['db_name'] = FPostStr('db_name');
		$sqlFVArr['mt4_password'] = think_encrypt($sqlFVArr['mt4_password'], C('PASSWORD_KEY'));
		
		$affectId = $DB->insert('t_mt4_server',$sqlFVArr);
		
		initAdmin($affectId);
		
		if(!$DB->getDRow("select * from t_config_server where server_id = '{$affectId}' and configname = 'MUST_REG_FIELD'")){
			$DB->insert('t_config_server',array(
												'configname'=>'MUST_REG_FIELD',
												'configvalue'=>'nickname',
												'server_id'=>$affectId,
												'create_time'=>time(),
												'update_time'=>time(),
			));
		}
		if(!$DB->getDRow("select * from t_config_server where server_id = '{$affectId}' and configname = 'REGEX_FILEDS'")){
			$DB->insert('t_config_server',array(
												'configname'=>'REGEX_FILEDS',
												'configvalue'=>'register_invent_codes',
												'server_id'=>$affectId,
												'create_time'=>time(),
												'update_time'=>time(),
			));
		}
		
		ajaxReturn('',L("保存成功"),1);
	}
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$where = "where id = '{$Id}' and `status` >= 0";
	$DB->query("update `t_mt4_server` set `status` = -1 {$where}");
	
	ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main','openmt'))){
	$Clause = 'main';
}
require_once('mt_server/' . $Clause . '.php');

