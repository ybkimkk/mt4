<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

if($Clause == 'resetoutnoney'){
	$Id = FPostInt('id');

	$info = $DB->getDRow("select * from t_outmoney where id = '{$Id}'");
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
	
	$data['content'] = FPostStr('content');
	if (!$data['content']) {
		this_error(L("请填写驳回理由"));
	}

	$inid = 0;
	if ($info['status'] == 8 && $info['outid'] > 0) {
		$server = $DB->getDRow("select * from t_mt4_server where id = '{$info['server_id']}' and status = 1");
		if (!$server){
			this_error(L("mt服务器不存在"));
		}
		
		try {
			$mtapi = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mtapi->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != 0) {
				this_error($retarry['info']);
			}

			$inid = $mtapi->balance($info['mtid'], $info['number'], "Deposit ib back#" . $info['id'], 0); //MT4出金
			$inmsg = $inid['info'];
			$inid = $inid['ret'];
		} catch (Exception $e) {
			this_error(L("连接mtapi接口异常"));
		}
		
		if ($inid <= 0 && $inid != '-88') {//失败
			this_error(L("入金失败") . $inmsg);
		}
		if ($inid == '-88') {//入金失败
			this_error(L("重复出金"));
		}
	}
	
	if ($inid > 0){
		$data['content'] = $data['content'] . '，资金退回MT，单号：' . $inid;
	}
	$data['adminid'] = $DRAdmin['id'];
	$data['status'] = 1;
	$data['reply_time'] = time();
	$data['processing_ip'] = get_client_ip();
	$DB->update("t_outmoney",$data,"where id = '{$Id}'");
	
	$result = api('gold://Mail/rejectBalanceOutEmail', array($Id));
	
	this_success($data['content']);
}else if($Clause == 'visitoutmoney'){
	admin_action_log();

	$Id = FPostInt('id');
	$info = $DB->getDRow("select * from t_outmoney where id = '{$Id}'");
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
	
	
	$outAllMoney = floatval($info['number']) + floatval($info['fee']);
	
	
	//需要接口调用实时的余额
	$mtuser = getuser($info['mtid']);
	if (!$mtuser) {
		this_error(L("当前MT用户不存在"));
	}

	$mtlogin = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$info['mtid']}' and member_id = '{$info['member_id']}' and status = 1 and mtserver = '{$DRAdmin['server_id']}'");
	if (!$mtlogin){
		this_error(L("该账号未绑定"));
	}

	$server = $DB->getDRow("select * from t_mt4_server where id = '{$mtlogin['mtserver']}' and status = 1");
	if (!$server){
		this_error(L("mt服务器不存在"));
	}

	$comment = "Withdraw maxib#" . $info['id'];
	/*if ($info['type'] == '3')
		$comment = "Withdraw ib#" . $info['id'] . " to#" . $info['forwordmtlogin'];*/
	$outid = 1;

	$reportModel = new ReportModel($server['db_name'], $server['ver']);
	if($server['ver'] == 5){
		$ticket = $reportModel->getTicketByComment('2', $comment, $server['ver'], $info['mtid']);
	}else{
		$ticket = $reportModel->getTicketByComment('6', $comment, $server['ver'], $info['mtid']);
	}
	if ($ticket) {
		if($server['ver'] == 5){
			$outid = $ticket['PositionID'];
		}else{
			$outid = $ticket['TICKET'];
		}
	} else {
		$mtapi = new MtApiModel($server['ver']);
		$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
		$retarry = $mtapi->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
		if ($retarry['ret'] != 0) {
			this_error($retarry['info']);
		}

		/* 实时查询客户余额 */
		if ($info['status'] == 0) {
			$mybalance = $mtapi->GetValidMoney($info['mtid'], 0); //我的MT4余额
			
			$canoutmoney = round($mybalance['ret'], 2);
			if ($canoutmoney <= 0) {
				this_error(L("可用出金为0，不能出金！") . " $" . $canoutmoney);
			}

			if ($canoutmoney < round($outAllMoney, 2)) {
				this_error(L("当前账户可用预付款不足，无法出金审核，最大可用预付款为") . "：$" . $canoutmoney);
			}
		}

		if (C('HAVE_ORDER_CHECK_OUT') != '1' && $info['status'] == 0) {//等于1 不限制出金条件，否则需要检测未平仓订单
			$orders = $reportModel->queryUnClosedOrders($info['mtid'], $server['mt4_server']);
			if (count($orders) > 0)
				this_error(L("账号") . $info['mtid'] . L("有未平仓订单，不能出金，请驳回"));
		}

		/*if ($info['type'] == '3') {//MT4同名转账
			$forwordmtuser = getuser($info['forwordmtlogin']);
			if (!$forwordmtuser) {
				this_error(L("转入MT账户不存在"));
			}
		}*/
		$msg = L("出金成功");
		if ($_COOKIE["withdraw_" . $info['mtid'] . "_" . $info['id']])
			this_error(L("请勿频繁审核当前出金申请！请2分钟后重试"));

		FSetCookie("withdraw_" . $info['mtid'] . "_" . $info['id'], $comment, time() + 120);

		if ($info['status'] == 0) {
			$outid = $mtapi->balance($info['mtid'], -$outAllMoney, $comment, 0); //MT4出金
			$outmsg = $outid['info'];
			$outid = $outid['ret'];
		}
		/*if ($info['type'] == '3' && $outid > 0) {//MT4同名转账
			if ($outid > 0) {
				$fromcomment = "Deposit ib#" . $info['id'] . " from#" . $info['mtid'];
				$fromticket = $reportModel->getTicketByComment('6', $fromcomment, $server['ver'], $info['forwordmtlogin']);
				if ($fromticket) {
					$inid = $fromticket['TICKET'];
				} else {
					$inid = $mtapi->balance($info['forwordmtlogin'], $outAllMoney, "Deposit ib#" . $info['id'] . " from#" . $info['mtid'], 0); //MT4出金
					$inid = $inid['ret'];
				}
				if ($inid > 0) {
					$msg = "出金成功，并转入账户" . $info['forwordmtlogin'];
				} else {
					$msg = "出金成功，转入" . $info['forwordmtlogin'] . '失败，错误代码:' . $outmsg . '，请在MT4后台手动入金';
				}
			}
		}*/

		if ($outid <= 0 && $outid != '-88') {//失败
			this_error(L("出金失败") . ":" . $outmsg);
		}
		if ($outid == '-88') {//入金失败
			this_error(L("重复出金"));
		}
	}
	
	$data = array();
	$data['outid'] = $outid;
	$data['status'] = 9;
	$data['content'] = $msg;
	$data['adminid'] = $DRAdmin['id'];
	$data['visit_time'] = time();
	$data['processing_ip'] = get_client_ip();
	$DB->update("t_outmoney",$data,"where id = '{$Id}'");
	
	$result = api('gold://Mail/sendBalanceOutEmail', array($Id));
	this_success($msg);
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('deposit_waitout/' . $Clause . '.php');

