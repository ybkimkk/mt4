<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveinfo'){
	admin_action_log();
	
	$reply = FPostStr('reply');
	if(strlen($reply) > 0){
		$status = 1;
	}else{
		$status = 0;
	}
	
	$sqlFVArr = array(
		'content'=>$reply,
		
		'a_id'=>$DRAdmin['id'],
		'server_id'=>$DRAdmin['server_id'],
	);
	
	$DRAnswer = $DB->getDRow("select * from `t_message_board_answer` where `mb_id` = '{$Id}'");
	if($DRAnswer){
		$affectId = $DB->update('t_message_board_answer',$sqlFVArr,"where id = '{$DRAnswer['id']}'");
	}else{
		$sqlFVArr['mb_id'] = $Id;
		$sqlFVArr['addtime'] = time();
		
		$affectId = $DB->insert('t_message_board_answer',$sqlFVArr);
	}
	
	$DB->query("update `t_message_board` set `status` = '{$status}' where id = '{$Id}'");

	FJS_AT(L('保存成功'),'?');
}else if($Clause == 'delinfo'){
	admin_action_log();
	
	$DB->query("delete from t_message_board where id = '{$Id}'");
	
	ajaxReturn('',L('删除数据成功'),1);
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('guestbook/' . $Clause . '.php');

