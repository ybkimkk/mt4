<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if ($Clause == 'deleteaccount') {
	admin_action_log();

	$map = array();
	$map['id'] = $_REQUEST['id'];
	if ($DRAdmin['_dataRange'] <= 1) {
		$map['member_id'] = $DRAdmin['id'];
	}
	$id = $DB->query("update t_member_mtlogin set status = -1 " . cz_where_to_str($map));
	if ($id > 0) {
		ajaxReturn(null, L("删除成功"), 0);
	} else {
		ajaxReturn(null, "", 1);
	}
} else if($Clause == 'forwordmanager') {
	admin_action_log();

	$id = $_REQUEST['id'];
	$mtserver = $_REQUEST['mtserver'];
	$userinfo = $_REQUEST['userinfo'];
	$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$id}' and status = 1");
	if (!$data) {
		this_error(L("账户不存在"));
	}
	if ($mtserver == $data['mtserver']) {
		this_error(L("没有转移"));
	}
	$result = forwordMt($data['member_id'], $data['loginid'], $data['mtserver'], $mtserver);
	if ($result['status'] == 'fail') {
		this_error(L($result['msg']));
	} else {
		this_success(L("转移成功"));
	}
}else if($Clause == 'viewaccount') {
	admin_action_log();
	$Id = $_REQUEST['id'];
	$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$Id}'");
	if ($data) {
		if ($DRAdmin['_dataRange'] <= 0) {
			if ($data['member_id'] != $DRAdmin['id']) {
				this_error(L("没有操作权限"));
			}
		}else if ($DRAdmin['_dataRange'] <= 1) {
			$member_id_arr = getunderCustomerIds($DRAdmin['id']);
			if (!in_array($data['member_id'], $member_id_arr)) {
				this_error(L("没有操作权限"));
			}
		}
		
		$info = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_users where LOGIN = '{$data['loginid']}'");
		$member = $DB->getDRow("select nickname from t_member where id = '{$data['member_id']}'");
		$data['LEVERAGE'] = $info['LEVERAGE'];
		$data['GROUP'] = $info['GROUP'];
		$data['nickname'] = $member['nickname'];
		$data['disable'] = $info['ENABLE'];
		$data['readonly'] = $info['ENABLE_READONLY'];
		ajaxReturn($data, "", 1);
	} else {
		this_error(L("您的MT帐号密码不正确"));
	}
}else if($Clause == 'savelogininfo_disable') {
	admin_action_log();

	$Id = $_REQUEST['id'];
	
	$readonly = $_REQUEST['readonly']; //只读
	$disable = $_REQUEST['disable']; //禁用
	
	$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$Id}'");
	$server = $DB->getDRow("select * from t_mt4_server where id = '{$data['mtserver']}' and status = 1");
	$info = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_users where LOGIN = '{$data['loginid']}'");

	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0') {
		this_error($retarry['info']);
	}

	if ($info['ENABLE'] != $disable) {
		$rs_dis = $mt4api->SetEnable($data['loginid'], $disable);
		$rs_dis = $rs_dis['ret'];
		if ($rs_dis != 'res:OK') {
			this_error(L("设置账户禁用属性失败"));
		}
	}
	if ($readonly != $info['ENABLE_READONLY']) {
		$rs_rd = $mt4api->SetReadOnly($data['loginid'], $readonly);
		$rs_rd = $rs_rd['ret'];
		if ($rs_rd != 'res:OK') {
			this_error(L("设置账户只读属性失败"));
		}
	}
	
	$DB->query("update t_member_mtlogin set manager_disable = '{$info['ENABLE_READONLY']}' where id = '{$Id}' and status = 1");

	this_success(L("设置成功"));
}else if($Clause == 'savelogininfo_group') {
	admin_action_log();

	$Id = $_REQUEST['id'];
	
	$group = $_REQUEST['mt4group']; //分组
	
	$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$Id}'");
	$server = $DB->getDRow("select * from t_mt4_server where id = '{$data['mtserver']}' and status = 1");
	$info = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_users where LOGIN = '{$data['loginid']}'");
	
	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0') {
		this_error($retarry['info']);
	}

	if ($info['GROUP'] != $group) {
		$group = iconv("UTF-8", "GB2312", $group);
		$rs_cg = $mt4api->ChangeGroup($data['loginid'], $group);
		$rs_cg = $rs_cg['ret'];
		if ($rs_cg != 'res:OK') {
			this_error(L("设置账户分组失败"));
		}
		
		//不必更新数据库中的数据，因为它会自动同步过来（同步到 报表数据库）
	}

	this_success(L("设置成功"));
}else if($Clause == 'savelogininfo_leverage') {
	admin_action_log();

	$Id = $_REQUEST['id'];
	
	$leveages = $_REQUEST['leveages']; //杠杆
	
	$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$Id}'");
	$server = $DB->getDRow("select * from t_mt4_server where id = '{$data['mtserver']}' and status = 1");
	$info = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_users where LOGIN = '{$data['loginid']}'");

	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0') {
		this_error($retarry['info']);
	}

	if ($leveages != $info['LEVERAGE']) {
		$rs_cl = $mt4api->ChangeLevel($data['loginid'], intval($leveages));
		$rs_cl = $rs_cl['ret'];
		if ($rs_cl != 'res:OK') {
			this_error(L("设置账户杠杆失败"));
		}
	}

	this_success(L("设置成功"));
}else if($Clause == 'resetaccoutpwd') {
	admin_action_log();

	$id = $_REQUEST['id'];
	$type = (int) $_REQUEST['setpwdtype'];
	if ($DRAdmin['_dataRange'] >= 2) {
		$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$id}'");
	} else {
		$data = $DB->getDRow("select * from t_member_mtlogin where id = '{$id}' and member_id = '" . $DRAdmin['id'] . "'");
	}
	if (empty($data))
		ajaxReturn(null, L("用户权限不正确或者信息不存在！"), 1);

	$server = $DB->getDRow("select * from t_mt4_server where id = '{$data['mtserver']}' and status = 1");
	if (empty($server))
		ajaxReturn(null, L("Manage信息不存在"), 1);

	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0') {
		this_error($retarry['info']);
	}

	$newpwd = rand_string(8, -1); //生成新密码
	$pre = $mt4api->SetPass($data['loginid'], $newpwd, $type);
	$pre = $pre['ret'];
	if ($pre == 'res:OK') {
		$member = $DB->getDRow("select * from t_member where id = '{$data['member_id']}'");
		$str = $type == 1 ? L("只读") : L("交易");
		$resultchecked = api('gold://Mail/sendresetMt4Pwd', array($data['id'], $member['email'], $member['nickname'], $data['loginid'], $str, $newpwd, $data['mtserver']));
		this_success(L("重置成功"));
	} else {
		this_error(L("重置失败"));
	}
}else if($Clause == 'saveaccount') {
	admin_action_log();

	if (!$_REQUEST['mtserver']) {
		this_error(L("MT服务器必须选择"));
	}

	if (!$_REQUEST['loginid']) {
		this_error(L("MT账号不能为空"));
	}

	if (!is_numeric($_REQUEST['loginid'])) {
		this_error(L("MT账户必须为数字"));
	}

	$map['id'] = $_REQUEST['id'];
	$servermap['id'] = $_REQUEST['mtserver'];
	$servermap['status'] = '1';
	$logininfo = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str(array('loginid' => $_REQUEST['loginid'], 'id' => array('neq', $_REQUEST['id']), 'status' => array('egt', 0))));
	if ($logininfo && $DRAdmin['_dataRange'] <= 1) {
		this_error(L("帐号已绑定"));
	}

	//返佣账户唯一
	if ($_REQUEST['mt_type'] == '0') {
		//返佣账户
		$accwhere['mt_type'] = '0';
		$accwhere['mtserver'] = $_REQUEST['mtserver'];
		$accwhere['status'] = '1';
		if ($DRAdmin['_dataRange'] >= 2 && $_REQUEST['memberuserid']) {
			$accwhere['member_id'] = $_REQUEST['memberuserid'];
		} else {
			$accwhere['member_id'] = $DRAdmin['id'];
		}
		$accwhere['mtserver'] = $DRAdmin['server_id'];
		$accdata = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str($accwhere));
		if ($accdata && $accdata['id'] != $map['id']) {
			//支持修改，是同一条记录可以修改
			$str = $DRAdmin['id'] == $accdata['member_id'] ? '您的' : '该';
			this_error(L($str . "账户已经存在主账户，请修改成交易账户"));
		}
	}
	//管理员不要验证密码
	if ($DRAdmin['_dataRange'] >= 2) {
		if ($_REQUEST['memberuserid']) {
			$data['member_id'] = $_REQUEST['memberuserid']; //绑定到新会员这里来
		}
		$info = 0;
	} else {
		$server = $DB->getDRow("select * from t_mt4_server " . cz_where_to_str($servermap));
		$info = '';
		
		$mt4api = new MtApiModel();
		$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
		$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
		if ($retarry['ret'] != '0') {
			this_error($retarry['info']);
		}

		$info = $mt4api->checkpassword($_REQUEST['loginid'], $_REQUEST['password']);
		$info = $info['ret'];
	}

	if (!$info) {
		$data['mtserver'] = $_REQUEST['mtserver'];
		$data['loginid'] = $_REQUEST['loginid'];
		$data['mtpassword'] = think_encrypt($_REQUEST['password'], C('PASSWORD_KEY'));
		$data['create_time'] = time();
		$data['update_time'] = time();
		$data['mt_type'] = $_REQUEST['mt_type'];

		//交易账户
		$data['status'] = "1";
		$ids = 0;
		if ($_REQUEST['id']) {
			//更新
			$map['id'] = $_REQUEST['id'];

			$id = $DB->update("t_member_mtlogin",$data,cz_where_to_str($map));
			$ids = $map['id'];
		} else {
			if ($_REQUEST['memberuserid'] && $DRAdmin['_dataRange'] >= 2) {
				$data['member_id'] = $_REQUEST['memberuserid'];
			} else {
				$data['member_id'] = $DRAdmin['id'];
			}

			$id = $DB->insert("t_member_mtlogin",$data);
			$ids = $id;
		}

		if ($id > 0) {
			if ($logininfo && $DRAdmin['_dataRange'] >= 2) {
				//以前绑定过的会员将被删除
				$DB->query("update t_member_mtlogin set status = -1 " . cz_where_to_str(array('loginid' => $_REQUEST['loginid'], 'id' => array('neq', $ids), 'status' => array('egt', 0))));
			}
			this_success(L("修改成功"));
		} else {
			this_error(L("绑定失败"));
		}
	} else {
		this_error(L("您的MT帐号密码不正确"));
	}
}else if($Clause == 'saveapply') {
	admin_action_log();

	$uid = $DRAdmin['id'];
	$count = intval($DB->getField("select count(*) as count1 from t_mt4_apply " . cz_where_to_str(array('member_id' => $uid, 'status' => 0, 'mt4_server_id' => $DRAdmin['server_id']))));
	if ($count >= 1) {
		this_error(L("您申请的记录正在审核中，无法再次申请"));
	}

	$applytime = $DB->getField("select max(create_time) as max1 from t_mt4_apply where member_id = '{$uid}' and status = 1");
	$now = time();
	if ($now - $applytime < 5 * 60) {
		this_error(L("申请开户次数太频繁，请5分钟后重试"));
	}
	$datas['name'] = str_replace(" ", '', $_REQUEST['name']);
	$datas['mt4_server_id'] = $_REQUEST['mt4_server_id'];
	if ($DRAdmin['server_id'] != $_REQUEST['mt4_server_id']) {
		this_error(L("当前账户已经切换，请刷新重新申请"));
	}

	$mtwhere['status'] = 1;
	$mtwhere['mtserver'] = $DRAdmin['server_id'];
	$mtwhere['member_id'] = $DRAdmin['id'];
	$mtlist = $DB->getField2Arr("select id,loginid from t_member_mtlogin " . cz_where_to_str($mtwhere));
	$mtcount = count($mtlist);
	$number = C('SET_MAX_LOGIN');
	if ($mtcount >= $number) {
		this_error(L("您只能开设") . $number . L("个MT账户"));
	}

	$datas['email'] = $_REQUEST['email'];
	$datas['amount'] = $_REQUEST['amount'];

	$datas['leverage'] = $_REQUEST['leverage'];
	$datas['member_id'] = $uid;
	$datas['status'] = 0;
	$datas['phone'] = $_REQUEST['phone'];
	$datas['city'] = $_REQUEST['city'];
	$datas['address'] = $_REQUEST['address'];
	$datas['create_time'] = time();
	$info = $DB->getDRow("select id,nickname,email,phone,city,address,parent_id,update_time from t_member where id = '{$uid}'");
	if ($info['parent_id'] != '0') {
		//查找上级的返佣分组
		$mtmap = array('member_id' => $info['parent_id'], 'mtserver' => $DRAdmin['server_id'], 'mt_type' => '0', 'status' => '1');
		//推广码优先 如果有推广码 则分组跟随推广码所在的分组
		if ($info['invent_code']) {
			unset($mtmap['mt_type']);
			$mtmap['loginid'] = $info['invent_code'];
		}
		$group = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str($mtmap));
		if ($group) {
			$mt4user = getuser($group['loginid']);
			if ($mt4user) {
				$mt4group = $mt4user['GROUP'];
			}
		}
	}

	if (!$mt4group) {
		//如果有同名账户，则选择同名账户的原有组
		$samenamelogin = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str(array('member_id' => $info['id'], 'mt_type' => '0', 'status' => '1', 'mtserver' => $_REQUEST['mt4_server_id'])));
		if ($samenamelogin) {
			$mt4user = getuser($samenamelogin['loginid']);
			if ($mt4user) {
				$mt4group = $mt4user['GROUP'];
			}
		}
	}
	if (!$mt4group) {
		//如果上级无分组，则选择第一个组
		$group = $DB->getDRow("select * from t_groups " . cz_where_to_str(array('server_id' => $datas['mt4_server_id'], 'status' => '0')));
		if ($group) {
			$mt4group = $group['group'];
		}
	}

	if (!$datas['leverage']) {
		$datas['leverage'] = C('DEFAULT_LEVER');
	}
	//默认杠杆
	$datas['group'] = $mt4group;
	$id = $DB->insert("t_mt4_apply",$datas);
	if ($id) {
		if (C('MT4_AUTO_CHECKED') == '1') {
			//开启自动审核
			$result = api('gold://Mt4api/genericmtlogin', array($info['id'], $datas['mt4_server_id'], $mt4group, $datas['leverage'], $datas['name'], "", ""));
			if ($result['status'] == '0') {
				//生成成功，进入审核阶段
				$checkresult = api('gold://Mt4api/checkMt4', array($result['data']['user'], $result['data']['pass'], $info['id'], $datas['mt4_server_id'], $mt4group, $id));
				creaet_inmoney($info['id'], $result['data']['user'], $datas['mt4_server_id']); //开户后自动入金
				if ($checkresult['status'] == '0') {
					//发送开户邮件给客户
					$resultchecked = api('gold://Mail/sendMtCheckedmail', array($info['id'], $result['data']['user'], $result['data']['pass'],$result['data']['rdpass']));

					if ($resultchecked['status'] == '0') {
						$rst = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], "autochecked", $datas['mt4_server_id']));
						this_success(L("申请开户成功，请查收邮件"));
					} else {
						$rst = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], "发送邮件失败，请手动重发", $datas['mt4_server_id']));
						this_success(L("申请开户成功，等待发送邮件"));
					}
				}
			} else {
				//审核失败 发送邮件提醒管理员人为审核
				api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], "自动审核失败，" . $result['info'], $datas['mt4_server_id']));
				this_success(L("自动开户失败，等待人工审核"));
			}
		} else {
			$result = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], '', $datas['mt4_server_id']));
			this_success(L("申请成功，请等待审核"));
		}
	} else {
		this_error(L("申请失败"));
	}
	
	this_error(L("申请失败") . '(ERR1)');
}


if(!in_array($Clause,array('main','applyaccount'))){
	$Clause = 'main';
}
require_once('mtlogin/' . $Clause . '.php');

