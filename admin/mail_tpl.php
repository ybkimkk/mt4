<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'setpass'){
	admin_action_log();
	
	$DB->query("update `t_mail_template` set `status` = '1' where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'unpass'){
	admin_action_log();
	
	$DB->query("update `t_mail_template` set `status` = '0' where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'saveinfo'){
	admin_action_log();
	
	$name = $_REQUEST['name'];
	if (!$name) {
		FJS_P_AC(L('请填写模板名称'));
	}
	
	$template = $DB->getDRow("select * from t_mail_template where `name` = '{$name}' and status = 1 and id <> '{$Id}'");
	if ($template) {
		FJS_P_AC(L('该模板已经存在'));
	}
	
	$dataArr = array();
	foreach($LangNameList['list'] as $keyL=>$valL){
		$title = FPostStr('title-' . $keyL);
		$content = $_POST['content-' . $keyL];
		if (strlen($title) <= 0) {
			FJS_P_AC(L('请填写主题') . '-' . $valL['title']);
		}
		if (strlen($content) <= 0) {
			FJS_P_AC(L('请填写模板内容') . '-' . $valL['title']);
		}
		
		$dataArr[$keyL] = array(
			'title'=>$title,
			'content'=>$content,
		);
	}
	
	$data = array(
		'name'=>$name,
		'sendtype'=>FPostStr('sendtype'),
		'type'=>FPostStr('type'),
		'var'=>FPostStr('var'),
		'update_time'=>time(),
	);
	if($Id <= 0){
		$data['create_time'] = time();
		$data['status']      = 1;
		
		$DB->insert('t_mail_template',$data);
	}else{
		$DB->update('t_mail_template',$data,"where id = '{$Id}'");
	}
	
	foreach($dataArr as $key=>$val){
		$data = array(
			'f_title'=>$val['title'],
			'f_val'=>$val['content'],
		);
		
		$chkRs = $DB->getDRow("select * from t_lang_otherset where f_serverId = '0' and f_type = '{$name}' and f_lang = '{$key}'");
		if($chkRs){
			$DB->update('t_lang_otherset',$data,"where id = '{$chkRs['id']}'");
		}else{
			$data['f_serverId'] = 0;
			$data['f_type'] = $name;
			$data['f_lang'] = $key;
			$data['f_addTime'] = FNow();
			
			$DB->insert('t_lang_otherset',$data);
		}
	}
	
	FJS_P_AT(L('保存成功'),'?');
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$DB->query("update t_mail_template set status = -1 where id = '{$Id}'");
	
	FRedirect(FPrevUrl());
}

if(!in_array($Clause,array('main','addinfo'))){
	$Clause = 'main';
}
require_once('mail_tpl/' . $Clause . '.php');

