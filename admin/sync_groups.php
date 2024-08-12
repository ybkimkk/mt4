<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'syncgroups'){
	admin_action_log();

	$server = $DB->getDRow("select * from `t_mt4_server` where id = '" . $DRAdmin['server_id'] . "' and `status` = 1");
	if($server){
		$mt4api = new MtApiModel($server['ver']);
		$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
		$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
		if ($retarry['ret'] != '0'){
			ajaxReturn('',L($retarry['info']),0);
		}

		$groups = $mt4api->GetGroups(1);
		$groups = $groups['ret'];
		$count = 0;
		$arr = explode("|",$groups);
		$total = count($arr)-1;
		$groups_fail='';
		$nowtime = time();
		foreach($arr as $u){
			$u = iconv("GB2312", "UTF-8", $u);
			$u = str_replace('\\','\\\\',$u);
			if($u == 'manager') continue;
			if(stripos($u,'manager') !== false) continue;
			
			if(strlen($u) > 0){
				$result = $DB->getDRow("select * from `t_groups` where `group` = '{$u}' and `server_id` = '{$server['id']}'");
				if(!$result){
					$DB->query("insert into `t_groups` set `group` = '{$u}',`server_id` = '{$server['id']}',`create_time` = '" . $nowtime . "',`status` = 1,`type` = 'B',`create_user` = '" . $DRAdmin['id'] . "'");
					
					$count++;
				}else{
					$DB->query("update `t_groups` set `create_time` = '" . $nowtime . "',`create_user` = '" . $DRAdmin['id'] . "' where id = '{$result['id']}'");
					
					$count++;
				}
			}
			
		}
		
		//删除未更新的数据
		$DB->query("delete from `t_groups` where `create_time` < '{$nowtime}' and `server_id` = '{$server['id']}'");
	}
	
	ajaxReturn('',L("同步成功"),1);
}else if($Clause == 'viewtype'){
	$type = FPostStr('type');
	
	$groups = '';
	$data = $DB->getDTable("select `group` from `t_groups` where `type` = '{$type}'");
	foreach ($data as $groupkey =>$group){
		$groups = $groups . $group['group'] . ',';
	}
	$data['groups'] = $groups;

	ajaxReturn($data,'',1);
}else if($Clause == 'upremark'){
	admin_action_log();
	
	foreach ($_POST as $key => $value) {
		if (false !== stripos($key, 'remark_')) {
			$log = explode('_', $key);
			$id = $log[1];
			$remark = $value;
			$DB->query("update t_groups set `group_remark` = '{$remark}' where id = '{$id}'");  
		}   
	}
	
	ajaxReturn('',L("保存成功"),1);
}else if($Clause == 'savedefaultgroup'){
	admin_action_log();
	
	$groups = array();
	foreach($LangNameList['list'] as $keyL=>$valL){
		$group = FPostStr('defaultgroup-' . $keyL);
		if(strlen($group) <= 0){
			ajaxReturn('',L('请选择默认开户MT组') . ':' . $valL['title'],0);
		}
		
		$groups[$keyL] = str_replace('\\','\\\\',$group);
	}
	
	foreach($groups as $key=>$val){
		$chkRs = $DB->getDRow("select * from t_lang_otherset where f_serverId = '" . $DRAdmin['server_id'] . "' and f_type = '默认开户MT组' and f_lang = '{$key}'");
		if($chkRs){
			$DB->query("update t_lang_otherset set f_val = '{$val}' where id = '{$chkRs['id']}'");
		}else{
			$DB->query("insert into t_lang_otherset set f_serverId = '" . $DRAdmin['server_id'] . "',f_type = '默认开户MT组',f_lang = '{$key}',f_val = '{$val}',f_addTime = '" . FNow() . "'");
		}
	}

	//$group = str_replace('\\','\\\\',$group);
	
	//$DB->query("update `t_groups` set `status` = 1,update_time = '" . time() . "',update_user = '" . $DRAdmin['id'] . "' where `status` = 0 and server_id = " . $DRAdmin['server_id'] . "");
	//$DB->query("update `t_groups` set `status` = 0,update_time = '" . time() . "',update_user = '" . $DRAdmin['id'] . "' where `group` = '{$group}' and server_id = " . $DRAdmin['server_id'] . "");
	 
	 ajaxReturn('',L("保存成功"),1);
}else if($Clause == 'savetype'){
	admin_action_log();
	
	$type = FPostStr('type');
	if(strlen($type) <= 0){
		ajaxReturn('',L('请选择A BOOK 或者 B Book'),0);
	}
	
	$DB->startTrans();

	if($type=='A'){
		$DB->query("update `t_groups` set `type` = 'B' where `type` = 'A' and server_id = " . $DRAdmin['server_id'] . "");
	}
	
	$groups_array = $_POST['groups'];
	if(is_array($groups_array)){
		foreach($groups_array as $groupkey =>$groupval){
			$DB->query("update `t_groups` set `type` = '{$type}',update_time = '" . time() . "',update_user = '" . $DRAdmin['id'] . "' where `group` = '{$groupval}' and server_id = " . $DRAdmin['server_id'] . "");
		}
	}
	
	$DB->commit();
	
	ajaxReturn('',L("保存成功"),1);
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('sync_groups/' . $Clause . '.php');

