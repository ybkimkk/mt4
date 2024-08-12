<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');


function savezye(){
	global $DB;
	
	admin_action_log();

	$Id = FGetInt('id');
	
	$rs = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");
	if (!$rs) {
		FJS_AB(L('赠金信息不存在'));
	}
	if ($rs['Status'] == -1) {
		FJS_AB(L('该信息已被驳回'));
	}
	if ($rs['Status'] < 0) {
		FJS_AB(L('该信息已被作废'));
	}
	if ($rs['f_zye_endBackTime'] > 0) {
		FJS_AB(L('该信息已经审核通过了'));
	}	
	
	$server  = $DB->getDRow("select * from `t_mt4_server` where Id = '{$rs['ServerId']}'");
	if(!$server){
		FJS_AB(L('未找到服务器') . "：" . $rs['ServerId']);
	}
	
	$loginid = $rs['MtLogin'];
	$number = $rs['Result'];
	
	$reportModel = new ReportModel($server['db_name'],$server['ver']);
	
	//------------------执行赠金开始
	$ticket = $reportModel->getTicketByComment('6',"Credit to Balance#" . $rs['Id'],$server['ver'],$loginid);
	if(!$ticket){
		try {
			$mt4api = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != 0) {
				//$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：" . $retarry['info'] . "' where Id = '{$rs['Id']}'");

				FJS_AB(L('api错误') . '(1)' . "：" . $retarry['info']);
			}
			$inid = $mt4api->balance($loginid, $number, "Credit to Balance#" . $rs['Id'], 1); //MT4入金
			$inid = $inid['ret'];
		} catch (Exception $e) {
			//$DB->query("update t_credit_record set f_zye_endBackTime = '" . time() . "',f_zye_endBackAbout = 'api错误：连接mtapi接口异常' where Id = '{$rs['Id']}'");

			FJS_AB(L('api错误') . '(1)' . "：" . L('连接mtapi接口异常'));
		}
		if ($inid == '-88') {//入金失败
			//$DB->query("update t_credit_record set f_zye_endBackTime = '" . time() . "',f_zye_endBackAbout = 'api错误：重复入金' where Id = '{$rs['Id']}'");

			FJS_AB(L('api错误') . '(1)' . "：" . L('重复'));
		} else if ($inid <= '-1') {//入金失败
			//$DB->query("update t_credit_record set f_zye_endBackTime = '" . time() . "',f_zye_endBackAbout = 'api错误：入金审核失败' where Id = '{$rs['Id']}'");

			FJS_AB(L('api错误') . '(1)' . "：" . L('审核失败'));
		}

		$data = array();
		$data['f_zye_endBackTime'] = time();
		$data['f_zye_endBackTicket'] = $inid;
		$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");
	}
		
	//扣回
	$ticket = $reportModel->getTicketByComment('7',"Credit " . $rs['Object'] . " discount#" . $rs['Id'],$server['ver'],$loginid);
	if(!$ticket){
		if($mt4api){
			$inid = $mt4api->credit($loginid, -$number, "Credit " . $rs['Object'] . " discount#" . $rs['Id']); //扣回MT赠金
			$inid = $inid['ret'];
		}else{
			try {
				$mt4api = new MtApiModel($server['ver']);
				$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
				$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
				if ($retarry['ret'] != 0) {
					//$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：" . $retarry['info'] . "' where Id = '{$rs['Id']}'");
	
					FJS_AB(L('api错误') . '(0)' . "：" . $retarry['info']);
				}
				$inid = $mt4api->credit($loginid, -$number, "Credit " . $rs['Object'] . " discount#" . $rs['Id']); //扣回MT赠金
				$inid = $inid['ret'];
			} catch (Exception $e) {
				//$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：连接mtapi接口异常' where Id = '{$rs['Id']}'");
	
				FJS_AB(L('api错误') . '(0)' . "：" . L('连接mtapi接口异常'));
			}
			if ($inid == '-88') {//入金失败
				//$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：重复赠金' where Id = '{$rs['Id']}'");
	
				FJS_AB(L('api错误') . '(0)' . "：" . L('重复'));
			} else if ($inid <= '-1') {//入金失败
				//$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：赠金审核失败' where Id = '{$rs['Id']}'");
	
				FJS_AB(L('api错误') . '(0)' . "：" . L('审核失败'));
			}
		}

		$data = array();
		$data['f_endBackTime'] = time();
		$data['f_endBackTicket'] = $inid;
		$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");
	}
	
	//统计转记录
	$rsChk = $DB->getDRow("select * from t_credit_record_tickets where f_login = '{$loginid}' and f_recordId = '{$Id}'");
	if(!$rsChk){
		$jysj = FGetInt('jysj');
		$zye_lot_info = unserialize($rs['f_zye_lot_info']);
		if($zye_lot_info){
			if($zye_lot_info['tickets']){
				$tickets = array_keys($zye_lot_info['tickets']);
				$tickets = implode(',',$tickets);
			}else{
				$tickets = '0';
			}		
			
			$query1 = $DB->query("select a.*,b.f_again,b.id as bid from (select * from {$zye_lot_info['db_name']}.mt4_trades where TICKET in (" . $tickets . ")) a left join t_credit_record_tickets b on a.TICKET = b.f_ticket and b.f_recordId = '{$Id}' order by a.CLOSE_TIME asc");
			while($rs1 = $DB->fetchArray($query1)){
				if(intval($rs1['bid']) <= 0){
					$bs = $zye_lot_info['tickets'][$rs['TICKET']];
					
					$data = array();
					$data['f_login'] = $loginid;
					$data['f_recordId'] = $Id;
					$data['f_ticket'] = $rs1['TICKET'];
					$data['f_bs'] = $bs;
					$data['f_again'] = $jysj;
					$data['f_addTime'] = FNow();
					$DB->insert('t_credit_record_tickets',$data);
				}
			}
		}
	}

	FJS_AT(L('转余额审核成功'),FPrevUrl());
}

function unzye(){
	global $DB;
	
	admin_action_log();

	$Id = FGetInt('id');
	
	$rs = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");
	if (!$rs) {
		FJS_AB(L('赠金信息不存在'));
	}
	if ($rs['Status'] == -1) {
		FJS_AB(L('该信息已被驳回'));
	}
	if ($rs['Status'] < 0) {
		FJS_AB(L('该信息已被作废'));
	}
	if ($rs['f_zye_endBackTime'] > 0) {
		FJS_AB(L('该信息已经审核通过了'));
	}
	
	$DB->query("update t_credit_record set f_zye_endTimeChkState = -2 where Id = '{$Id}'");
	
	FJS_AT(L('驳回成功'),FPrevUrl());
}

function saveback(){
	global $DB;
	
	admin_action_log();

	$Id = FGetInt('id');
	
	$rs = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");
	if (!$rs) {
		FJS_AB(L('赠金信息不存在'));
	}
	if ($rs['Status'] == -1) {
		FJS_AB(L('该信息已被驳回'));
	}
	if ($rs['Status'] < 0) {
		FJS_AB(L('该信息已被作废'));
	}
	if ($rs['f_zye_endBackTime'] > 0) {
		FJS_AB(L('该信息已经审核通过了'));
	}	
	
	$server  = $DB->getDRow("select * from `t_mt4_server` where Id = '{$rs['ServerId']}'");
	if(!$server){
		FJS_AB(L('未找到服务器') . "：" . $rs['ServerId']);
	}
	
	$loginid = $rs['MtLogin'];
	$number = $rs['Result'];
		
	//扣回
	$reportModel = new ReportModel($server['db_name'],$server['ver']);
	$ticket = $reportModel->getTicketByComment('7',"Credit " . $rs['Object'] . " discount#" . $rs['Id'],$server['ver'],$loginid);
	if(!$ticket){
		try {
			$mt4api = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != 0) {
				$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：" . $retarry['info'] . "' where Id = '{$rs['Id']}'");

				FJS_AB(L('api错误') . '(0)' . "：" . $retarry['info']);
			}
			$inid = $mt4api->credit($loginid, -$number, "Credit " . $rs['Object'] . " discount#" . $rs['Id']); //扣回MT赠金
			$inid = $inid['ret'];
		} catch (Exception $e) {
			$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：连接mtapi接口异常' where Id = '{$rs['Id']}'");

			FJS_AB(L('api错误') . '(0)' . "：" . L('连接mtapi接口异常'));
		}
		if ($inid == '-88') {//入金失败
			$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：重复赠金' where Id = '{$rs['Id']}'");

			FJS_AB(L('api错误') . '(0)' . "：" . L('重复'));
		} else if ($inid <= '-1') {//入金失败
			$DB->query("update t_credit_record set f_endBackTime = '" . time() . "',f_endBackAbout = 'api错误：赠金审核失败' where Id = '{$rs['Id']}'");

			FJS_AB(L('api错误') . '(0)' . "：" . L('审核失败'));
		}

		$data = array();
		$data['f_endBackTime'] = time();
		$data['f_endBackTicket'] = $inid;
		$DB->update('t_credit_record',$data,"Id = '{$rs['Id']}'");
	}

	FJS_AT(L('扣回成功'),FPrevUrl());
}

if($Clause == 'savezye'){
	savezye();
}else if($Clause == 'unzye'){
	unzye();
}else if($Clause == 'saveback'){
	saveback();
}else if($Clause == 'check'){
	admin_action_log();

	$Id = FPostInt('id');
	
	$info = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");
	if (!$info) {
		ajaxReturn('',L('赠金信息不存在'),0);
	}
	if ($info['Status'] == -1) {
		ajaxReturn('',L('该信息已被驳回'),0);
	}
	if ($info['Status'] < 0) {
		ajaxReturn('',L('该信息已被作废'),0);
	}
	if ($info['Status'] == 9) {
		ajaxReturn('',L('该信息已经审核通过了'),0);
	}

	$mtlogin = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$info['MtLogin']}' and member_id = '{$info['MemberId']}' and status = 1");
	if (!$mtlogin){
		ajaxReturn('',L('该账号未绑定'),0);
	}
	
	$server = $DB->getDRow("select * from t_mt4_server where id = '{$info['ServerId']}' and status = 1");
	if (!$server){
		ajaxReturn('',L('mt服务器不存在'),0);
	}

	$reportModel = new ReportModel($server['db_name'],$server['ver']);
	$ticket = $reportModel->getTicketByComment('7',"Credit maxib#" . $info['Id'],$server['ver'],$info['MtLogin']);
	if(!$ticket){
		try {
			$mt4api = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != 0) {
				ajaxReturn('',$retarry['info'],0);
			}
			$inid = $mt4api->credit($info['MtLogin'], $info['Result'], "Credit maxib#" . $info['Id']); //MT赠金
			$inid = $inid['ret'];
		} catch (Exception $e) {
			ajaxReturn('',L('连接mtapi接口异常'),0);
		}
		if ($inid == '-88') {//入金失败
			ajaxReturn('',L('重复赠金'),0);
		} else if ($inid <= '-1') {//入金失败
			ajaxReturn('',L('赠金审核失败'),0);
		}

		$data = array();
		$data['Status'] = 1;
		$data['CheckUser'] = $DRAdmin['id'];
		$data['CheckTime'] = time();
		$data['Ticket'] = $inid;
		$data['InTime'] = time();
		$DB->update('t_credit_record',$data,"Id = '{$Id}'");

		$result = api('gold://Mail/sendCreditInEmail', array($info['Id']));

		ajaxReturn('',L('赠金审核成功'),1);
	}else{
		ajaxReturn('',L('重复赠金'),0);
	}	
}else if($Clause == 'back'){
	admin_action_log();

	$Id = FPostInt('id');
	
	$info = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");
	if (!$info) {
		ajaxReturn('',L('赠金信息不存在'),0);
	}
	if ($info['Status'] == -1) {
		ajaxReturn('',L('该信息已被驳回'),0);
	}
	if ($info['Status'] < 0) {
		ajaxReturn('',L('该信息已被作废'),0);
	}
	if ($info['Status'] == 1) {
		ajaxReturn('',L('该信息已经审核通过了'),0);
	}
	
	$data = array();
	$content = FPostStr('content');
	if (strlen($content) <= 0) {
		ajaxReturn('',L('请填写驳回理由'),0);
	}

	$data['CheckUser'] = $DRAdmin['id'];
	$data['CheckTime'] = time();
	$data['Memo'] = $content;
	$data['Status'] = -1;
	
	$data['f_endTime'] = 0;
	$data['f_zye_endTime'] = 0;
	
	$DB->update('t_credit_record',$data,"Id = '{$Id}'");

	$result = api('gold://Mail/rejectedCreditInEmail', array($info['Id']));
	
	ajaxReturn('',L('驳回成功'),1);
}

if(!in_array($Clause,array('main','showinfo','zyeinfo'))){
	$Clause = 'main';
}
require_once('credits_check/' . $Clause . '.php');

