<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();
	
	if (!$_POST['create_time']) {
		$_POST['create_time'] = time();
	} else {
		$_POST['create_time'] = strtotime($_POST['create_time']);
	}
	
	$dataArr = array();
	foreach($LangNameList['list'] as $keyL=>$valL){
		$title = FPostStr('title-' . $keyL);
		$content = $_POST['content-' . $keyL];
		if (strlen($title) <= 0) {
			FJS_P_AC(L('请填写主题') . '-' . $valL['title']);
		}
		if (strlen($content) <= 0) {
			FJS_P_AC(L('请填写内容') . '-' . $valL['title']);
		}
		
		$dataArr[$keyL] = array(
			'title'=>$title,
			'content'=>$content,
		);
	}
	
	$data = array(
		//'title'=>FPostStr('title'),
		'cid'=>FPostInt('cid'),
		'create_time'=>FPostStr('create_time'),
		//'content'=>$_POST['content'],
		'sort'=>FPostInt('sort'),
		'f_key'=>FPostStr('f_key'),

		'start_time'=>FPostStr('start_time'),
		'end_time'=>FPostStr('end_time'),
	);
	if (!$data['create_time']) {
		FJS_P_AC("发布日期不能为空");
	}
	if($Id <= 0){
		$data['server_id'] = $DRAdmin['server_id'];
		$data['status'] = 1;
		$id = $DB->insert("t_activity_list",$data);
	}else{
		$DB->update("t_activity_list",$data,"where id = '{$Id}'");
		
		$id = $Id;
	}
	
	
	foreach($dataArr as $key=>$val){
		$data = array(
			'f_title'=>$val['title'],
			'f_val'=>$val['content'],
		);
		
		$chkRs = $DB->getDRow("select * from t_lang_otherset where f_serverId = '{$DRAdmin['server_id']}' and f_type = '-_activity_-{$id}' and f_lang = '{$key}'");
		if($chkRs){
			$DB->update('t_lang_otherset',$data,"where id = '{$chkRs['id']}'");
		}else{
			$data['f_serverId'] = $DRAdmin['server_id'];
			$data['f_type'] = '-_activity_-' . $id;
			$data['f_lang'] = $key;
			$data['f_addTime'] = FNow();
			
			$DB->insert('t_lang_otherset',$data);
		}
	}
	
	

	FJS_P_AT(L('保存成功'),'?');
}else if($Clause == 'forbid'){
	admin_action_log();
	
	$DB->query("update t_activity_list set status = 0 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'resume'){
	admin_action_log();
	
	$DB->query("update t_activity_list set status = 1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$DB->query("update t_activity_list set status = -1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'setjoinstate'){
	admin_action_log();
	
	if(FGetInt('state') == 1){
		$DB->query("update t_activity_join set f_status = '审核通过' where id = '{$Id}'");
	}else{
		$DB->query("update t_activity_join set f_status = '已拒绝',f_canJoinAgain = 1 where id = '{$Id}'");
	}
	
	FRedirect(FPrevUrl());
}else if($Clause == 'deljoin'){
	admin_action_log();
	
	$DB->query("delete from t_activity_join where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if ($Clause == 'getjoinin') {
	$Id = FPostInt('id');

	$rs = $DB->getDRow("select a.*,b.* from (select * from t_activity_join where id = '{$Id}') a left join t_member b on a.f_uid = b.id");
	if ($rs) {
		$memlogin = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id = '{$rs['f_uid']}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}' order by mt_type asc");
		if($DRAdmin['ver'] == 5){
			$mt5user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt5_users` where `Login` = '" . current($memlogin) . "'");
			if ($mt5user) {
				$rs['login'] = $mt5user['Login'];
				$rs['group'] = $mt5user['Group'];
			}
		}else{
			$mt4user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt4_users` where `LOGIN` = '" . current($memlogin) . "'");
			if ($mt4user) {
				$rs['login'] = $mt4user['LOGIN'];
				$rs['group'] = $mt4user['GROUP'];
			}
		}
		
		switch($rs['userType']){
			case 'agent':
				$rs['userTypeStr'] = L('代理') . '(' . $rs['level'] . L('级') . ')';
				break;
			case 'direct':
				$rs['userTypeStr'] = L('直接客户');
				break;
			case 'member':
				$rs['userTypeStr'] = L('员工') . '(' . $rs['level'] . L('级') . ')';
				break;
		}
			
		ajaxReturn($rs, L("查询成功"), 1);
	}
	ajaxReturn($rs, L("查询失败"), 0);
}

if(!in_array($Clause,array('main','addinfo','showinfo','joinlist'))){
	$Clause = 'main';
}
require_once('activity/' . $Clause . '.php');

