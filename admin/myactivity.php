<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'join'){
	admin_action_log();
	
	$rs1 = $DB->getDRow("select * from t_activity_list where id = " . $Id);
	if($rs1){
		if(time() > strtotime($rs1['start_time']) && time() < strtotime($rs1['end_time'])){
			$rs = $DB->getDRow("select * from t_activity_join where f_uid = '" . $DRAdmin['id'] . "' and f_pid = " . $Id);
			if(!$rs){
				$DB->query("insert into t_activity_join set f_pid = '" . $Id . "',f_uid = '" . $DRAdmin['id'] . "',f_addTime = '" . date('Y-m-d H:i:s') . "',f_status = '待审核'");
			}else{
				if($rs['f_status'] == '已拒绝' && $rs['f_canJoinAgain'] > 0){
					$DB->query("update t_activity_join set f_addTime = '" . date('Y-m-d H:i:s') . "',f_status = '待审核' where id = " . $rs['id']);
				}
			}
		}
	}
	
	FRedirect(FPrevUrl());
}else if($Clause == 'unjoin'){
	admin_action_log();
	
	$rs1 = $DB->getDRow("select * from t_activity_list where id = " . $Id);
	if($rs1){
		if(time() > strtotime($rs1['start_time']) && time() < strtotime($rs1['end_time'])){
			$rs = $DB->getDRow("select * from t_activity_join where f_uid = '" . $DRAdmin['id'] . "' and f_pid = " . $Id);
			if($rs){
				if($rs['f_status'] == '审核通过'){
					$DB->query("update t_activity_join set f_cancelTime = '" . date('Y-m-d H:i:s') . "',f_cancelStatus = '待审核' where id = " . $rs['id']);
				}else{
					$DB->query("delete from t_activity_join where id = " . $rs['id']);
				}
			}
		}
	}
	
	FRedirect(FPrevUrl());
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('myactivity/' . $Clause . '.php');

