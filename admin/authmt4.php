<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'getmtlogin'){
	admin_action_log();

	$rid = FPostInt('rid');
	
	$sessionLastAct = S("loginapply" . $rid);
	if (time() - $sessionLastAct < 300 && $sessionLastAct > 0) {
		ajaxReturn('',L("请不要重复提交开户"),0);
	}
	
	$mtgroup = FPostStr('mtgroup');
	if (strlen($mtgroup) <= 0) {
		ajaxReturn('',L("开户组不能为空"),0);
	}

	$apply = $DB->getDRow("select * from `t_mt4_apply` where id = '{$rid}'");
	if (!$apply) {
		ajaxReturn('',L("审核记录不存在"),0);
	}
	
	S("loginapply" . $rid, time());
	
	if ($apply['status'] != 0) {
		ajaxReturn('',L("该申请记录已处理"),0);
	}
	
	$id = FPostInt('id');
	$mtserver = FPostInt('mtserver');
	$leverage = FPostInt('leverage');
	$loginid = FPostStr('loginid');
	$password = FPostStr('password');
	
	$group = iconv("UTF-8", "GB2312",  $mtgroup);
	$data = api('gold://Mt4api/genericmtlogin', array($id, $mtserver, $group, $leverage, $apply['name'], $loginid, $password));
	if ($data['status'] == '0') {
		$savedata = array();
		$savedata['group'] = $mtgroup;
		$savedata['last_group'] = $mtgroup;
		$savedata['last_leverage'] = $leverage;
		$savedata['last_server_id'] = $mtserver;
		$savedata['login'] = $data['data']['user'];
		$savedata['password'] = think_encrypt($data['data']['pass'], C('PASSWORD_KEY'));
		$savedata['check_time'] = time();
		//$savedata['rdpass']=$data['data']['rdpass'];
		$ret = $DB->update("t_mt4_apply",$savedata,"where id = '{$rid}'");
		
		ajaxReturn('',L($data['data']),1);
	} else {
		S("loginapply" . $rid, null);
		
		ajaxReturn('',L($data['info']),0);
	}
}else if($Clause == 'updatemark'){
	admin_action_log();

	$Id = FPostInt('id_mark');
	
	$data = array();
	$data['remark'] = FPostStr('mark');
	$DB->update("t_member",$data,"where id = '{$Id}'");
	
	ajaxReturn('',L("保存成功"),1);
}else if($Clause == 'refusemt4'){
	admin_action_log();

	$reply = FPostStr('reply');
	if (strlen($reply) <= 0) {
		ajaxReturn('',L("请填写拒绝原因"),0);
	}
	
	$sid = FPostInt('sid');
	$info = $DB->getDRow("select * from `t_mt4_apply` where id = '{$sid}'");
	if ($info) {
		$data = array();
		$data['reply'] = $reply;
		$data['status'] = '-1';
		$data['check_time'] = time();
		$ret = $DB->update('t_mt4_apply',$data,"where id = '{$sid}'");
		
		if ($ret > 0) {
			$rst = api('gold://Mail/sendresetMt4Notify', array($info['id'], $info['email'], $info['name'], $reply, $DRAdmin['server_id']));
			
			ajaxReturn('',L("拒绝成功"),1);
		} else {
			ajaxReturn('',L("拒绝失败"),0);
		}
	} else {
		ajaxReturn('',L("数据不存在"),0);
	}
}else if($Clause == 'resendmt4mail'){
	admin_action_log();

	$apply = $DB->getDRow("select * from `t_mt4_apply` where id = '{$Id}'");
	if (!$apply) {
		ajaxReturn('',L("数据不存在"),0);
	}
	
	if ($apply['status'] == '1') {
		$password = think_decrypt($apply['password'], C('PASSWORD_KEY'));
		
		$data = api('gold://Mail/sendMtCheckedmail', array($apply['member_id'], $apply['login'], $password,$apply['rdpass']));
		
		ajaxReturn('',L("发送成功"),1);
	}

	ajaxReturn('',L("该信息未审核"),0);
}else if($Clause == 'savemtlogin'){
	admin_action_log();

	$memberid = $_REQUEST['id'];
	$applyid = $_REQUEST['rid'];
	$mtserver = $_REQUEST['mtserver'];
	$loginid = $_REQUEST['loginid'];
	$mtgroup = $_REQUEST['mtgroup'];
	$password = $_REQUEST['password'];
	$rdpassword = $_REQUEST['rdpassword'];
	if (!$mtgroup) {
		this_error(L("开户组为空，不能开户"));
	}
	$member = $DB->getDRow("select * from t_member where id = '{$memberid}' and status = 1");

	if ($member['server_id'] != $mtserver) {
		//选择不同服务器，则切换
		$mtlogin = $DB->getDRow("select * from t_member_mtlogin where member_id = '{$memberid}' and status = 1 and mtserver = '{$member['server_id']}'");
		if ($mtlogin) {
			$member['id'] = '';
			$member['parent_id'] = '0';
			$member['server_id'] = $mtserver;
			$member['avatar'] = '';
			$member['amount'] = 0;
			//如果已经存在，则获取id
			$existmember = $DB->getDRow("select * from t_member where email = '{$member['email']}' and status = 1 and server_id = '{$mtserver}'");
			if ($existmember) {
				$memberid = $existmember['id'];
			} else {
				$data['f_roleId'] = C('DEAFAULT_ROLE');

				$memberid = $DB->insert("t_member",$member);
			}
		} else {
			$existmember = $DB->getDRow("select * from t_member where email = '{$member['email']}' and status = 1 and server_id = '{$mtserver}'");
			if (!$existmember) {
				$DB->query("update t_member set server_id = '{$mtserver}',parent_id = 0 where id = '{$memberid}' and status = 1");
			}
		}
	}

	$apply = $DB->getDRow("select * from t_mt4_apply where id = '{$applyid}'");
	if (!$apply) {
		ajaxReturn('', L("审核记录不存在"), 0);
	}
	if ($apply['mt4_server_id'] != $_REQUEST['mtserver']) {
		$DB->query("update t_mt4_apply set mt4_server_id = '" . $_REQUEST['mtserver'] . "' where id = '{$applyid}'");
	}

	//mt4开户
	$checkresult = api('gold://Mt4api/checkMt4', array($loginid, $password, $memberid, $mtserver, $mtgroup, $applyid));
	if ($checkresult['status'] == '0') {
		if(C("OPEN_MT_DISABLE") == 1){
			$DB->query("update t_member_mtlogin set manager_disable = 0 where loginid = '{$loginid}' and mtserver = '{$mtserver}'");
		}
		$result = api('gold://Mail/sendMtCheckedmail', array($memberid, $loginid, $password,$rdpassword));
		creaet_inmoney($memberid, $loginid, $mtserver); //模拟仓入金
		
		this_success(L("操作成功"));
	} else {
		this_error(L("操作失败") . $checkresult['info']);
	}
}

if(!in_array($Clause,array('main','showinfo'))){
	$Clause = 'main';
}
require_once('authmt4/' . $Clause . '.php');

