<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();
	
	if (!$_POST['addtime']) {
		$_POST['addtime'] = time();
	} else {
		$_POST['addtime'] = strtotime($_POST['addtime']);
	}
	
	$data = array(
		'f_title'=>FPostStr('f_title'),
		'type'=>FPostInt('type'),
		'addtime'=>FPostStr('addtime'),
		'content'=>FPostStr('content'),
	);
	
	if (!$data['f_title']) {
		FJS_P_AC("标题不能为空");
	}
	if (!$data['addtime']) {
		FJS_P_AC("发布日期不能为空");
	}
	if (!$data['content']) {
		FJS_P_AC("内容不能为空");
	}
	if($Id <= 0){
		$data['server_id'] = $DRAdmin['server_id'];
		$data['status'] = 0;
		$data['m_id'] = $DRAdmin['id'];
		$id = $DB->insert("t_message_board",$data);
	}else{
		$id = $DB->update("t_message_board",$data,"where id = '{$Id}' and m_id = '{$DRAdmin['id']}'");
	}

	FJS_P_AT(L('保存成功'),'?');
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$DB->query("delete from t_message_board where id = '{$Id}' and m_id = '{$DRAdmin['id']}'");
	
	ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main','addinfo','showinfo'))){
	$Clause = 'main';
}
require_once('myguestbook/' . $Clause . '.php');

