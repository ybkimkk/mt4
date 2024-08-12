<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');


if($Clause == 'dowithdraw'){
	admin_action_log();

	$amount = round(floatval(FPostStr('amount')),2);
	if ($amount <= 0)
		this_error(L('提现金额必须为正数'));

	$memberLogin = $DB->getDRow("select * from t_member_mtlogin where member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['serverid']}' and status = 1 and mt_type = 0");//主账户
	if(!$memberLogin){
		this_error(L('未找到MT账号'));
	}
	$mt4login = $memberLogin['loginid'];

	$lessAmount = $DRAdmin['amount'] - $amount;
	if ($lessAmount < 0)
		this_error(L('余额不足'));
		
	$server = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['serverid']}'");
	if (!$server)
		this_error(L('mt4服务器不存在'));
		
	$balance['AMOUNT'] = -$amount;
	$balance['MEMBER_ID'] = $DRAdmin['id'];
	$balance['CREATE_TIME'] = time();
	$balance['SERVER_ID'] = $DRAdmin['serverid'];
	$balance['TYPE'] = "1"; //提现

	$BALANCE_ID = $DB->insert("t_sale_commission_balance",$balance); //保存记录

	if ($BALANCE_ID > 0) {

		$inid = -1;
		try {
			$mt4api = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != '0') {
				this_error($retarry['info']);
			}
			
			$inid = $mt4api->balance($mt4login, $amount, "Commission maxib#" . $BALANCE_ID, 1); //MT4入金
			$inid = $inid['ret'];
		} catch (Exception $e) {
			this_error(L('连接mt4api接口异常'));
		}
		
		if ($inid <= 0 && $inid != '-88') {//入金失败
			this_error(L('MT4服务器入金失败'));
		}
		if ($inid == '-88') {
			this_error(L('重复入金'));
		}
			
		//入金成功
		$DB->query("update t_member set amount = '{$lessAmount}' where id = '{$DRAdmin['id']}'"); //扣除体现金额

		$bdata['IN_ID'] = $inid;
		$bdata['IN_TIME'] = time();
		$bdata['MT4_TIME'] = time() + 3600 * floatval(8 - $server['time_zone']);
		$bdata['MEMO'] = 'MT订单#' . $inid . ' 帐户信息#' . $mt4login . '@' . $server['mt4_name']; //提现帐户 保存入金ID
		$DB->update("t_sale_commission_balance",$bdata,"where ID = '{$BALANCE_ID}'");

		this_success(L('提现成功'));

	} else {
		this_error(L('提现记录录入失败'));
	}

}else if($Clause == 'dowithaccount'){
	admin_action_log();
	
	if (in_array($DRAdmin['userType'], array('member,agent'))) {
		this_error(L('此功能只允许代理或者员工使用'));
	}
	
	$amount = round(floatval(FPostStr('forwordnumber')),2);

	if ($amount <= 0) {
		this_error(L('金额只能为正数'));
	}
	if (round($DRAdmin['amount'], 2) < $amount) {
		this_error(L('您的余额不足'));
	}
	
	$password = FPostStr('password');
	if (strlen($password) <= 0) {
		this_error(L('请输入验证密码'));
	}

	$memberLogin = $DB->getDRow("select * from t_member_mtlogin where member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['serverid']}' and status = 1 and mt_type = 0");//主账户
	if(!$memberLogin){
		this_error(L('未找到MT账号'));
	}

	$server = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['serverid']}' and status = 1");
	if (!$server)
		this_error(L('mt4服务器不存在'));
	
	$info = '';
	try {
		$mt4api = new MtApiModel($server['ver']);
		$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
		$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
		if ($retarry['ret'] != '0')
			this_error($retarry['info']);

		$info = $mt4api->checkpassword($memberLogin['loginid'], $password);
		$info = $info['ret'];
		if ($info != 0) {
			this_error(L('您的MT主帐号密码不正确'));
		}
	} catch (Exception $e) {
		this_error(L('连接mt4api接口异常'));
	}
	
	/* 验证MT4帐号密码 */
	$getid = FPostInt('getlogin');
	if ($getid <= 0) {
		this_error(L('请选择要转入的账户'));
	}
	
	$getmember = $DB->getDRow("select * from t_member where id = '{$getid}' and status = 1 and server_id = '{$DRAdmin['serverid']}'");
	if (!$getmember) {
		this_error(L('转入的收款账户不存在或者未激活'));
	}

	$account = getunderCustomerIds($DRAdmin['id']);
	if (!in_array($getid, $account)) {
		this_error("收款帐号必须是您的下级");
	}

	//$get_memberlogin = $DB->getDRow("select * from t_member_mtlogin where member_id = '{$getid}' and mtserver = '{$DRAdmin['serverid']}' and status = 1 and mt_type = 0");//主账户

	//-----------------------------------
	$DB->startTrans();

	$out = $DB->query("update t_member set amount = amount - {$amount} where id = '{$DRAdmin['id']}'");
	loginfo('out_amount',date("Y-m-d H:i:s") . " " . $DRAdmin['id'] . "用户资金转移至" . $getid . "金额为：" . $amount);
	
	$in = $DB->query("update t_member set amount = amount + {$amount} where id = '{$getid}'");
	loginfo('out_amount',date("Y-m-d H:i:s") . " " . $getid . "用户收到" . $getid . "金额为：" . $amount);
	
	$amountLogArr = array('member_id' => $DRAdmin['id'], 
						'server_id' => $DRAdmin['serverid'], 
						'amount' => $amount, 
						'getmember' => $getid, 
						'create_time' => time(), 
						//'login' => $memberLogin['loginid'], 
						//'getlogin' => $get_memberlogin['loginid'],
						);
	$log = $DB->insert("t_member_amount_log",$amountLogArr);
	
	if ($out && $in && $log) {
		$DB->commit();
		
		this_success(L('转账成功'));
	} else {
		$DB->rollback();
		
		this_error(L('转账失败'));
	}
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('out_amount/' . $Clause . '.php');

