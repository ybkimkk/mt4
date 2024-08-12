<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

if($Clause == 'managerdepoist'){
	admin_action_log();

	$server_id = $DRAdmin['server_id'];

	$mtid = FPostInt('mtid');
	$number = round(FPostStr('number'), 2);
	$remark = FPostStr('remark');
	$member_id = $DB->getField("select member_id from t_member_mtlogin where `loginid` = '{$mtid}' and `mtserver` = '{$server_id}' and `status` = 1");
	if (!$mtid) {
		this_error(L("清填写入金帐号"));
	}
	if ($number <= 0) {
		this_error(L("清填写入金金额"));
	}
	if ($number <= 0) {
		this_error(L("清填写入金金额"));
	}
	if (!$remark) {
		this_error(L("清填写备注信息"));
	}
	if ($mtid != FPostStr('mtlogin2')) {
		this_error(L("确认收款账号不正确，请重新填写"));
	}
	if (!$member_id) {
		this_error(L("入金会员不存在"));
	}

	$pay                   = $DB->getDRow("select * from `t_pay` where `server_id` = '{$server_id}' and `Status` = 1 order by Id asc");
	$type         = $pay['PayCode'];
	if($pay['f_pa'] == 'auto'){
		$autoPa = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL = '{$pay['f_symbolsER']}'");
		if($autoPa){
			$pay['f_fixedER'] = $autoPa['BID'];
			//ASK
		}
	}
	
	$data = array();
	$data['member_id'] = $member_id;
	$data['mtid'] = $mtid;
	$data['number'] = $number;
	$data['type'] = $type;
	$data['payno'] = date('YmdHis') . rand(100000, 999999);
	$data['create_time'] = time();
	$data['status'] = 0;
	$data['adminid'] = $DRAdmin['id'];
	$data['server_id'] = $server_id;
	$existpayno = false;
	do {
		$data['payno'] = date('YmdHis') . rand(100000, 999999);
		//检测订单是否重复，重复重新生成	
		$existpayno = $DB->getDRow("select * from t_inmoney where `payno` = '{$data['payno']}'");
	} while ($existpayno);
	$data['exchange'] = $pay['f_fixedER'];
	$data['price'] = round(floatval($data['exchange']) * floatval($number), 2); //实际支付价格
	//额外参数，必须以extend开头
	$id = $DB->insert("t_inmoney",$data);
	
	if ($id > 0) {
		$info = $DB->getDRow("select * from t_inmoney where `id` = '{$id}'");
		$server = $DB->getDRow("select * from t_mt4_server where id = '{$server_id}' and `status` = 1");
		if (!$server)
			this_error(L("mt服务器不存在"));

		$mtapi = new MtApiModel($server['ver']);
		$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
		$retarry = $mtapi->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
		if ($retarry['ret'] != 0) {
			this_error($retarry['info']);
		}
		$inid = $mtapi->balance($info['mtid'], $info['number'], "Deposit maxib#" . $info['id'], 1); //MT4入金
		$inmsg = $inid['info'];
		$inid = $inid['ret'];

		if ($inid == '-88') {//入金失败
			this_error(L("重复入金"));
		} else if ($inid <= '-1') {//入金失败
			this_error(L("入金失败") . $inmsg);
		}
	}

	$data = array();
	$data['status'] = 9;
	$data['paystatus'] = 1;
	$data['adminid'] = $DRAdmin['id'];
	$data['visit_time'] = time();
	$data['inid'] = $inid;
	$data['intime'] = time();
	$data['content'] = "CRM入金，" . $remark;
	$ids = $DB->update("t_inmoney",$data,"where id = '{$id}'");
	if ($ids) {
		this_success(L("操作成功"));
	} else {
		this_error(L("操作失败"));
	}
}else if($Clause == 'visitinmoney'){
	admin_action_log();

	$Id = FPostInt('id');
	
	$info = $DB->getDRow("select * from t_inmoney where id = '{$Id}'");
	if (!$info) {
		this_error(L("入金信息不存在"));
	}
	if ($info['status'] == 1) {
		this_error(L("该信息已被驳回"));
	}
	if ($info['status'] < 0) {
		this_error(L("该信息已被作废"));
	}
	if ($info['status'] == 9) {
		this_error(L("该信息已经审核通过了"));
	}

	$mtlogin = $DB->getDRow("select * from t_member_mtlogin where `loginid` = '{$info['mtid']}' and `member_id` = '{$info['member_id']}' and `status` = 1");
	if (!$mtlogin)
		this_error(L("该账号未绑定"));

	$server = $DB->getDRow("select * from t_mt4_server where `id` = '{$mtlogin['mtserver']}' and `status` = 1");
	if (!$server)
		this_error(L("mt服务器不存在"));
		
	$reportModel = new ReportModel($server['db_name']);
	$comment = "Deposit maxib#" . $info['id'] . $server['ver'];
	$ticket = $reportModel->getTicketByComment('6', $comment, $server['ver'],$info['mtid']);
	if ($ticket) {
		$inid = $ticket['TICKET'];
	} else {
		$sessionLastAct = S("deposit_" . $info['mtid'] . "_" . $info['id']);
		if (time() - $sessionLastAct < 120 && $sessionLastAct > 0) {
			this_error(L("请勿频繁审核当前入金申请！请2分钟后重试"));
		}

		S("deposit_" . $info['mtid'] . "_" . $info['id'], time());
		
		$mtapi = new MtApiModel($server['ver']);
		$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
		$retarry = $mtapi->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
		if ($retarry['ret'] != 0) {
			this_error($retarry['info']);
		}
		$inid = $mtapi->balance($info['mtid'], $info['number'], "Deposit maxib#" . $info['id'], 1); //MT4入金
		$inmsg = $inid['info'];
		$inid = $inid['ret'];
		
		if ($inid == '-88') {//入金失败
			this_error(L("重复入金"));
		} else if ($inid <= '-1') {//入金失败
			this_error(L("入金失败") . $inmsg);
		}
	}

	$data = array();
	$data['status'] = 9;
	$data['paystatus'] = 1;
	$data['adminid'] = $DRAdmin['id'];
	$data['visit_time'] = time();
	$data['inid'] = $inid;
	$data['intime'] = time();
	$data['content'] = "手动审核";
	$data['processing_ip'] = get_client_ip();
	$id = $DB->update("t_inmoney",$data,"where id = '{$Id}'");

	if ($id) {
		//$id = D("inmoney")->where("id = '{$Id}'")->save(array('inid'=>$ids,'intime'=>time()));
		$result = api('gold://Mail/sendBalanceInEmail', array($Id));
		
		$credit_ret = credit_credit($info, 'inmoney');
		$msg = "";
		if ($credit_ret) {
			$msg = L("满足条件，自动为您发起赠金申请");
			$list = api('gold://Mail/creditInEmail', array($info['id']));
		}
		
		this_success(L("操作成功"));
	} else {
		this_error(L("操作失败"));
	}
}else if($Clause == 'resetinmoney'){
	admin_action_log();

	$Id = FPostInt('id');

	$info = $DB->getDRow("select * from t_inmoney where id = '{$Id}'");
	if (!$info) {
		this_error(L("出金信息不存在"));
	}
	if ($info['status'] == 1) {
		this_error(L("该信息已被驳回"));
	}
	if ($info['status'] < 0) {
		this_error(L("该信息已被作废"));
	}
	if ($info['status'] == 9) {
		this_error(L("该信息已经审核通过了"));
	}
	
	$data = array();
	$data['content'] = FPostStr('content');
	if (!$data['content']) {
		this_error(L("请填写驳回理由"));
	}

	$data['adminid'] = $DRAdmin['id'];
	$data['status'] = 1;
	$data['visit_time'] = time();
	$data['processing_ip'] = get_client_ip();
	$id = $DB->update("t_inmoney",$data,"where id = '{$Id}'");
	if ($id) {
		$result = api('gold://Mail/rejectBalanceInEmail', array($Id));
		this_success(L("驳回成功"));
	} else {
		this_error(L("驳回失败"));
	}
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('deposit_waitin/' . $Clause . '.php');

