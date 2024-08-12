<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'canceloutmoney'){
	admin_action_log();

	$id = $_REQUEST['id'];
	
	$info = $DB->getDRow("select * from t_outmoney where id = '{$id}'");
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
	if ($info['status'] != 0) {
		this_error(L("取消失败"));
	}
	$inid = 0;
	
	//$tranDb = new Model();
	//$tranDb->startTrans();
	
	/*if ($info['status'] == 8 && $info['outid'] > 0) {
		$server = $DB->getDRow("select * from t_mt4_server where id = '{$info['server_id']}' and status = 1");
		if (!$server)
			this_error(L("mt服务器不存在"));
		try {
			$mtapi = new MtApiModel($server['ver']);
			$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
			$retarry = $mtapi->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
			if ($retarry['ret'] != 0) {
				//$tranDb->rollback();
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
	}*/
	
	$data = array();
	//if ($inid > 0) {
	//	$data['content'] = '资金退回MT，单号：' . $inid;
	//} else {
		$data['content'] = '已取消出金';
	//}
	$data['adminid'] = $DRAdmin['id'];
	$data['status'] = '-1'; // 已取消
	$data['reply_time'] = time();
	$data['processing_ip'] = get_client_ip();
	$id = $DB->update("t_outmoney",$data,"where id = '{$id}'");
	
	this_success(L("取消成功"));
}else if($Clause == 'checkmtlogin'){
	$mt_login = FPostInt('mt_login');
	$mt_login2 = FPostInt('mt_login2');
	
	if($mt_login <= 0){
		echo json_encode(array('msg'=>L('请输入收款MT帐号')));
		exit;
	}
	if($mt_login != $mt_login2){
		echo json_encode(array('msg'=>L('确认收款账号不正确，请重新填写')));
		exit;
	}
	
	$DR1 = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$mt_login}' and status = 1");
	if(!$DR1){
		echo json_encode(array('msg'=>L('转入账户不存在') . '(001)'));
		exit;
	}
	
	$DR2 = $DB->getDRow("select * from t_member where id = '{$DR1['member_id']}'");
	if(!$DR2){
		echo json_encode(array('msg'=>L('转入账户不存在') . '(002)'));
		exit;
	}
	
	if(!chk_mt_is_root_agent($DRAdmin['id'],$DR2['id'])){
		echo json_encode(array('msg'=>L('转入账户与您不属于同一根代理')));
		exit;
	}
	
	echo json_encode(array('msg'=>$DR2['realname']));
	exit;
}



if(!in_array($Clause,array('main','mtlist','addinfo','step2'))){
	$Clause = 'main';
}
require_once('out_money/' . $Clause . '.php');

