<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

if($Clause == 'check'){
	admin_action_log();

	$Id = FPostInt('id');
	
	$info = $DB->getDRow("select * from t_credit_record where Id = '{$Id}'");
	if (!$info) {
		ajaxReturn('',L('赠金信息不存在'),0);
	}
	if ($info['status'] == -1) {
		ajaxReturn('',L('该信息已被驳回'),0);
	}
	if ($info['status'] < 0) {
		ajaxReturn('',L('该信息已被作废'),0);
	}
	if ($info['status'] == 9) {
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

	loginfo('creaet_inmoney',"后台审核注册赠金：" . $info['MemberId'] . '|' . $info['MtLogin'] . '|$' . $info['Result'] . '|' . $info['ServerId']);

	$reportModel = new ReportModel($server['db_name'],$server['ver']);
	$ticket = $reportModel->getTicketByComment('7',"Credit reg#" . $info['Id'],$server['ver'],$info['MtLogin']);
	if(!$ticket){
		try {
			$mt4api = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != 0) {
				ajaxReturn('',$retarry['info'],0);
			}
			$inid = $mt4api->credit($info['MtLogin'], $info['Result'], "Credit reg#" . $info['Id']); //MT赠金
			$inid = $inid['ret'];
		} catch (Exception $e) {
			loginfo('creaet_inmoney',"api错误：" . L('连接mtapi接口异常'));

			ajaxReturn('',L('连接mtapi接口异常'),0);
		}
		if ($inid == '-88') {//入金失败
			loginfo('creaet_inmoney',"api错误：" . L('重复赠金'));

			ajaxReturn('',L('重复赠金'),0);
		} else if ($inid <= '-1') {//入金失败
			loginfo('creaet_inmoney',"api错误：" . L('赠金审核失败') . '|' . $inid);

			ajaxReturn('',L('赠金审核失败'),0);
		}

		$data = array();
		$data['Status'] = 1;
		$data['CheckUser'] = $DRAdmin['id'];
		$data['CheckTime'] = time();
		$data['Ticket'] = $inid;
		$data['InTime'] = time();
		$DB->update('t_credit_record',$data,"Id = '{$Id}'");

		loginfo('creaet_inmoney',"赠金成功：$" . $info['Result']);

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
	if ($info['status'] == -1) {
		ajaxReturn('',L('该信息已被驳回'),0);
	}
	if ($info['status'] < 0) {
		ajaxReturn('',L('该信息已被作废'),0);
	}
	if ($info['status'] == 1) {
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
	$DB->update('t_credit_record',$data,"Id = '{$Id}'");

	ajaxReturn('',L('驳回成功'),1);
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('credits_reg/' . $Clause . '.php');

