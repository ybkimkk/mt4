<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'upcard'){
	admin_action_log();
	
	$certificate = FPostIds('certificate');
	$inid = FPostInt('inid');
	
	if (strlen($certificate) <= 0) {
		ajaxReturn('',L('您还没有上传汇款凭证'),-1);
	}
	
	$DB->query("update t_inmoney set certificate = '{$certificate}' where id = '{$inid}' and member_id = '{$DRAdmin['id']}'");
	
	ajaxReturn('',L('更新成功'),1);
}

if(!in_array($Clause,array('main','showinfo','addinfo','step2'))){
	$Clause = 'main';
}
require_once('in_money/' . $Clause . '.php');

