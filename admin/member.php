<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');


if ($Clause == 'saveeditamount') {
	if(!chk_in_access('修改客户')){
		FJS_P_AC(L("您没有权限编辑"));
	}
	
	admin_action_log();
	
	$editamount_uid = FPostInt('editamount_uid');
	$adAmount = floatval(FPostStr('adAmount'));
	$adAbout = FPostStr('adAbout');
	if (!$editamount_uid) {
		this_error(L("当前用户ID为空"));
	}
	if ($adAmount == 0) {
		this_error(L("金额不能为0"));
	}
	if (strlen($adAbout) <= 0) {
		this_error(L("备注不能为空"));
	}

	$memberuser = $DB->getDRow("select * from t_member where id = '{$editamount_uid}' and server_id = '{$DRAdmin['id']}'");
	if ($memberuser) {
		$balance = array();
		$balance['AMOUNT'] = $adAmount;
		$balance['MEMBER_ID'] = $editamount_uid;
		$balance['CREATE_TIME'] = time();
		$balance['TYPE'] = "0";
		$balance['MEMO'] = $adAbout;//"管理员操作";
		$balance['SERVER_ID'] = $DRAdmin['id'];
		$DB->insert("t_sale_commission_balance",$balance);
		
		$DB->query("update t_member set amount = amount + (" . $adAmount . ") where id = '{$editamount_uid}' and server_id = '{$DRAdmin['id']}'");
	}else{
		this_error(L("未找到该用户"));
	}
	
	this_success(L('更新角色用户成功'));
}else if ($Clause == 'savemark') {
	admin_action_log();

	$checked = FPostStr('checked');
	$uncheck = FPostStr('uncheck');
	
	if(strlen($checked) > 0){
		$DB->update("t_member",array('f_isS' => 1),"where id in ({$checked})");
	}
	if(strlen($uncheck) > 0){
		$DB->update("t_member",array('f_isS' => 0),"where id in ({$uncheck})");
	}

	this_success(L('更新角色用户成功'));
}else if ($Clause == 'saveuser') {
	admin_action_log();

	$user_id = FPostInt('user_id');
	$role_id = FPostInt('role_id');
	
	$DB->update("t_member",array('f_roleId' => $role_id),"where id = '{$user_id}'");

	this_success(L('更新角色用户成功'));
}else if ($Clause == 'savealloc') {
	admin_action_log();

	$mid = FPostInt('mem_id');
	$parentId = FPostInt('parent_id');
	$parentName = FPostStr('parent_name');
	$userType = FPostStr('userType');
	$level = FPostInt('level');
	$rank_id = FPostInt('rank_id');
	if (!$mid) {
		this_error(L("当前用户ID为空"));
	}
	if ($parentName == '') {
		this_error(L("上级客户为空"));
	}
	
	$parentmember = $DB->getDRow("select * from t_member where id = '{$parentId}' and `status` = 1");
	if ($parentmember) {
		if ($parentmember['nickname'] != $parentName) {
			this_error(L("您填写的归属账户名称不存在，请重新选择"));
		}
	} else {
		if ($parentId != 0) {
			this_error(L("归属上级账户不存在"));
		} else if ($parentName != L("无上级") && $parentName != L("无")) {
			this_error(L("您选择的上级账户不存在"));
		}
	}
	if (checkDie($mid, $parentId)) {
		this_error(L("您的上下级设置中，存在死循环，请仔细检查上下级关系，无法完成设置"));
	}
	if($DRAdmin['_dataRange'] <= 0){
		if($mid != $DRAdmin['id']){
			this_error(L("您选择的账户不正确") . '(0)');
		}
	}else if($DRAdmin['_dataRange'] <= 1){
		$uid = $DRAdmin['id'];
		$member_id_arr = getunderCustomerIds($uid);
		if(!in_array($mid, $member_id_arr)){
			 this_error(L("您选择的账户不正确") . '(1)');
		}
	}
	$member = $DB->getDRow("select * from t_member where id = '{$mid}'");
	$myinfo = $DRAdmin;
	
	//$udata = array();
	
	$data = array();
	$data['status'] = 1;
	$data['userType'] = $userType;
	if ($userType == "agent") {
		//IB代理商
		if (!$level) {
			this_error(L("代理等级参数错误") . '!');
		}
		$memberlevel = $DB->getField("select `level` from t_member where id = '{$DRAdmin['id']}'");
		if ($DRAdmin['_dataRange'] <= 1 && $memberlevel >= (int)$level && $myinfo['userType'] != 'member') {
			this_error(L("代理等级参数错误") . '!');
		}
		if ($member['userType'] != 'agent') {
			//$udata['role_id'] = 3;
		}
		$data['rank_id'] = $rank_id;
		$data['level'] = $level;
	} else if ($userType == "direct") {
		if ($member['userType'] != 'direct') {
			$data['level'] = '1';
			//$udata['role_id'] = 2;
		}
	} else if ($userType == "member") {
		if ($member['userType'] != 'member') {
			//$udata['role_id'] = 20;
		}
		if ($member['id'] == $myinfo['id'] && $DRAdmin['_dataRange'] <= 1) {
			this_error(L("员工不能分配自己") . '!');
		}
		if ($DRAdmin['_dataRange'] <= 1 && $member['level'] <= $myinfo['level']) {
			this_error(L("员工等级参数错误") . '!');
		}
		if ($DRAdmin['_dataRange'] <= 1 && $level <= $myinfo['level']) {
			this_error(L("员工等级参数错误") . '!');
		}

		$data['level'] = $level;
	}
	$data['update_time'] = time();
	$data['parent_id'] = $parentId;

	$msg = $DB->update("t_member",$data,"where id = '{$mid}'");
	
	/*分配后保存详情*/
	$change['userid'] = $mid;
	$change['nickname'] = $member['nickname'];
	$loginid = $DB->getField("select `loginid` from t_member_mtlogin where member_id = '{$mid}' and mtserver = '{$DRAdmin['server_id']}'");
	if($loginid){
		$change['loginid'] = $loginid;
	}else{
		$change['loginid'] = '';
	}
	$change['old_parentid'] = $member['parent_id']; //原上级id
	$change['now_parentid'] = $parentId; //现上级id
	$change['old_userType'] = $member['userType'];  //原用户类型
	$change['now_userType'] = $userType; //现用户类型
	$change['old_level'] = $member['level'];  //原用户等级
	$change['now_level'] = (int)$data['level']; //现用户等级
	$change['adminid'] = $DRAdmin['id']; //操作人id;
	$change['update_time'] = time();
	$change['operation_ip'] = FGetClientIP();
	$change['server_id'] = $DRAdmin['server_id'];
	if($change['old_parentid'] != $change['now_parentid'] || $change['old_userType'] != $change['now_userType'] || $change['old_level'] != $change['now_level']){
		$DB->insert("t_member_change",$change); 
	}
	
	this_success(L("设置成功"));
}else if ($Clause == 'queryparentmemberbyname') {
	$key = FGetStr('parent_name');
	$member_id = FGetInt('member_id');
	
	$noparent['id'] = 0;
	$noparent['nickname'] = L("无上级");
	
	$str = '';
	if ($DRAdmin['_dataRange'] <= 0) {
		$data['value'] = array($noparent);
		echo json_encode($data);
		exit;
	}else if ($DRAdmin['_dataRange'] <= 1) {
		$uid = $DRAdmin['id'];
		$member_id_arr = getunderCustomerIds($uid);
		$member_id_arr = array_merge(array($uid), $member_id_arr);
		$str = " and a.id in(" . implode(',', $member_id_arr) . ")";
	}
	
	$where = "where a.`status` = '1'";
	if ($_REQUEST['userType'] == 'member') {
		$where .= " and `userType` = 'member'";
	}
	$where .= " and (nickname like '%{$key}%' or email = '{$key}' or loginid = '{$key}')" . $str;
	$where .= " and a.id <> '{$member_id}'";
	$where .= " and `admin` <> 1";
	$where .= " and server_id = '{$DRAdmin['server_id']}'";
	$memberList = $DB->getDTable("select a.id,a.nickname,a.email,a.userType from t_member a left join t_member_mtlogin b on a.id = b.member_id {$where} group by a.id");

	foreach ($memberList as $key => $val) {
		if ($val['userType'] == 'member') {
			$memberList[$key]['userType'] = L("员工");
		} else if ($val['userType'] == 'agent') {
			$memberList[$key]['userType'] = L("代理");
		} else if ($val['userType'] == 'direct') {
			$memberList[$key]['userType'] = L("直接客户");
		} else {
			$memberList[$key]['userType'] = '';
		}
	}

	$data = array();
	if (count($memberList) > 0) {
		$arry = array_merge(array($noparent), $memberList);
		$data['value'] = $arry;
		echo json_encode($data);
	} else {
		$data['value'] = array($noparent);
		echo json_encode($data);
	}
	exit;
}else if ($Clause == 'viewmember') {
	$Id = FPostInt('id');

	$member = $DB->getDRow("select * from t_member where id = '{$Id}'");
	if ($member) {
		//限定能授权的级别范围
		if ($member['parent_id'] == 0) {
			$member['parent_name'] = L("无");
		} else {
			$parentmember = $DB->getDRow("select * from t_member where id = '{$member['parent_id']}'");
			if ($parentmember) {
				$member['parent_name'] = $parentmember['nickname'];
			} else {
				$member['parent_name'] = L("上级用户不存在");
			}
		}
		
		//查询当前登录账户信息
		$loginmember = $DRAdmin;
		
		//限定能授权的级别范围
		if ($DRAdmin['_dataRange'] <= 1) {
			$member['super_level'] = $loginmember['level'];
			$member['super_rank'] = $loginmember['rank_id'];
		} else {
			$member['super_level'] = 0;
			$member['super_rank'] = 0;
		}
		ajaxReturn($member, L("查询成功"), 1);
	}
	ajaxReturn($member, L("查询失败"), 0);
}else if ($Clause == 'savepwd') {
	admin_action_log();

	$password = FPostStr('password');
	$confirm = FPostStr('confirm');

	$pattern = "/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/";
	if (!preg_match($pattern, $password)) {
		FJS_P_AC(L("密码必须是6-12位且包含大小写字母和数字"));
	}
	if ($password != $confirm) {
		FJS_P_AC(L("密码和确认密码不一致"));
	}
	
	$DRInfo = $DB->getDRow("select * from `t_member` where id = '{$Id}'");
	if(!$DRInfo){
		FJS_AB(L("查询数据失败"));
	}
	
	if ($DRAdmin['_dataRange'] <= 0) {
		if($DRInfo['id'] != $DRAdmin['id']){
			FJS_AB(L("您没有权限编辑") . '(0)');
		}
	}else if ($DRAdmin['_dataRange'] <= 1) {
		$idarr = getunderCustomerIds($DRAdmin['id']);
		$idarr = array_merge(array($DRAdmin['id']), $idarr);
		if (!in_array($DRInfo['id'], $idarr)) {
			FJS_AB(L("您没有权限编辑") . '(1)');
		}
	}
	
	$DB->query("update `t_member` set `password` = '" . md5($password) . "' where id = '{$Id}'");
	
	FJS_P_AT(L("保存成功"),'?');
}else if ($Clause == 'saveinfo') {
	if($Id > 0){
		if(!chk_in_access('修改客户')){
			FJS_P_AC(L("您没有权限编辑"));
		}
	}
	
	admin_action_log();

	$server_id = $DRAdmin['server_id'];

	$email = FPostStr('email');
	if(strlen($email) <= 0){
		FJS_P_AC(L('请输入您的邮箱,格式') . '32233232@qq.com');
	}
	$data = $DB->getDRow("select * from `t_member` where `email` = '{$email}' and server_id = '{$server_id}' and `status` in (0,1) and id <> '{$Id}'");
	if ($data) {
		FJS_P_AC(L("邮箱号码已经被注册"));
	}
	
	$nickname = FPostStr('nickname');
	if(strlen($nickname) <= 0){
		FJS_P_AC(L('请输入您的英文名或者姓名拼音'));
	}

	$data = array(
				'email'=>$email,
				'nickname'=>FPostStr('nickname'),
				'phone'=>FPostStr('phone'),
				'chineseName'=>FPostStr('chineseName'),
				'nationality'=>FPostStr('nationality'),
				'birthDate'=>FPostStr('birthDate'),
				'livingState'=>FPostStr('livingState'),
				'province'=>FPostStr('province'),
				'city'=>FPostStr('city'),
				'residentialAddress'=>FPostStr('residentialAddress'),
				'residenceTime'=>FPostStr('residenceTime'),
				'residentialTelephone'=>FPostStr('residentialTelephone'),
				'bankName'=>FPostStr('bankName'),
				'accountName'=>FPostStr('accountName'),
				'accountNum'=>FPostStr('accountNum'),
				'bankCard'=>FPostStr('bankCard'),
				'swiftCode'=>FPostStr('swiftCode'),
				'realname'=>FPostStr('realname'),
				'identity'=>FPostStr('identity'),
				'identityOpposite'=>FPostStr('identityOpposite'),
				'identityBack'=>FPostStr('identityBack'),
				'addressProof'=>FPostStr('addressProof'),
	);
	
	/* 检测重复字段 */
	if (strlen($data['identity'])) {//这是个定制的写法，因为只允许配置这个identity不允许重复
		$checkunque = explode(',', C("CHECK_FIELD_UNQUE"));
		$fields = unserialize(html_entity_decode($DB->getField("select `extra` from `t_config` where `name` = 'REGEX_FILEDS'")));
		foreach ($checkunque as $key => $value) {
			if(strlen($data[$value])){
				$fieldused = $DB->getDRow("select * from `t_member` where `{$value}` = '" . $data[$value] . "' and `server_id` = '{$server_id}' and `status` in (0,1) and id <> '{$Id}'");
				if ($fieldused && $value) {
					FJS_P_AC($fields[$value] . ' ' . L("已存在"));
				}
			}
		}
	}
	
	if($Id > 0){
		if ($DRAdmin['_dataRange'] <= 0) {
			if($Id != $DRAdmin['id']){
				FJS_P_AC(L("您没有权限编辑") . '(0)');
			}
		}else if ($DRAdmin['_dataRange'] <= 1) {
			$idarr = getunderCustomerIds($DRAdmin['id']);
			$idarr = array_merge(array($DRAdmin['id']), $idarr);
			if (!in_array($Id, $idarr)) {
				FJS_P_AC(L("您没有权限编辑") . '(1)');
			}
		}
		
		$ids = $DB->update("t_member",$data,"where id = '{$Id}'");
		
		FJS_P_AT(L("保存成功"),'?');
	}else{
		$password = FPostStr('password');
		$confirm = FPostStr('confirm');
	
		$pattern = "/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/";
		if (!preg_match($pattern, $password)) {
			FJS_P_AC(L("密码必须是6-12位且包含大小写字母和数字"));
		}
		if ($password != $confirm) {
			FJS_P_AC(L("密码和确认密码不一致"));
		}
			
		if (!$data['leverage']) {
			$data['leverage'] = C('DEFAULT_LEVER');
		}
		$data['password'] = md5($password);
		$data['status'] = 1; //激活状态
		$data['server_id'] = $server_id; //激活状态
		$data['headimg'] = '/assets/images/facedefault.jpeg';
		$data['create_time'] = time();
		$data['update_time'] = time();
		
		$data['register_group'] = get_lang_otherset_val('默认开户MT组');
		
		$data['f_roleId'] = C('DEAFAULT_ROLE');
		$data['userType'] = 'direct';
	
		$ids = $DB->insert("t_member",$data);
	
		if ($ids) {
			$info = $DB->getDRow("select * from `t_member` where `id` = '{$ids}'");
			$server = $DB->getDRow("select * from `t_mt4_server` where id = '{$server_id}' and `status` = 1");
			
			$datas = array();
			$datas['name'] = $info['nickname'];
			$datas['mt4_server_id'] = $server['id'];
			$datas['email'] = $info['email'];
			//客户选择杠杆
			if ($info['leverage']) {
				$datas['leverage'] = $info['leverage'];
			}else {
				$datas['leverage'] = getConfigValue('DEFAULT_LEVER', $server_id);
			}
			//默认杠杆
			$datas['member_id'] = $info['id'];
			$datas['status'] = 0;
			$datas['phone'] = $info['phone'];
			$datas['city'] = $info['city'];
			$datas['address'] = $info['address'];
			$datas['create_time'] = time();
			
			$mt4group = $data['register_group'];
				
			/*
			//报表数据库
			$DBR = new CMySQLi();
			$DBR->connnect($DBSettings['dbHost'],$DBSettings['dbUser'],$DBSettings['dbPwd'],$server['db_name'],$DBSettings['dbPort']);
			
			if ($info['parent_id'] != '0') {
				//查找上级的返佣分组
				$mtmap = "where `member_id` = '{$info['parent_id']}' and `mtserver` = '{$server_id}' and `status` = 1";
				//推广码优先 如果有推广码 则分组跟随推广码所在的分组
				if ($info['invent_code']) {
					$mtmap .= " and `loginid` = '{$info['invent_code']}'";
				}else{
					$mtmap .= " and `mt_type` = 0";
				}
				$group = $DB->getDRow("select * from `t_member_mtlogin` {$mtmap}");
				if ($group) {
					$mt4user = $DBR->getDRow("select * from `mt4_users` where `LOGIN` = '{$group['loginid']}'");
					if ($mt4user) {
						$mt4group = $mt4user['GROUP'];
					}
				}
			}	
			if (!$mt4group) {
				//如果有同名账户，则选择同名账户的原有组
				$samenamelogin = $DB->getDRow("select * from `t_member_mtlogin` where `member_id` = '{$info['id']}' and `mtserver` = '{$server_id}' and `mt_type` = 0 and `status` = 1");
				if ($samenamelogin) {
					$mt4user = $DBR->getDRow("select * from `mt4_users` where `LOGIN` = '{$samenamelogin['loginid']}'");
					if ($mt4user) {
						$mt4group = $mt4user['GROUP'];
					}
				}
			}
			if (!$mt4group) {
				//如果上级无分组，则选择第一个组
				$group = $DB->getDRow("select * from `t_groups` where `server_id` = '{$server['id']}' and `status` = 0");
				if ($server) {
					$mt4group = $group['group'];
				}
			}
			$datas['group'] = $mt4group;
			*/
			
			$datas['group'] = $data['register_group'];
			
			$id = $DB->insert("t_mt4_apply",$datas);
			
			if ($id) {
				$MT4_AUTO_CHECKED = getConfigValue('MT4_AUTO_CHECKED', $server_id);
				if ($MT4_AUTO_CHECKED == '1') {
					//开启自动审核           
					$openfield = getConfigValue('OPEN_MT4_FILEDS', $server_id); //系统开户字段
	
					$datas['name'] = $openfield == 'chineseName' ? $info['chineseName'] : $info['nickname'];
					$result = api('gold://Mt4api/genericmtlogin', array($info['id'], $server['id'], $mt4group, $datas['leverage'], $datas['name'], '', ''));
					if ($result['status'] == '0') {
						//生成成功
						$checkresult = api('gold://Mt4api/checkMt4', array($result['data']['user'], $result['data']['pass'], $info['id'], $server['id'], $mt4group, $id));
						creaet_inmoney($ids, $result['data']['user'], $server_id); //开户后自动入金
						if ($checkresult['status'] == '0') {
							//发送开户邮件给客户
							$resultchecked = api('gold://Mail/sendMtCheckedmail', array($info['id'], $result['data']['user'], $result['data']['pass']));
							if ($resultchecked['status'] == '0') {
								//发送开户邮件成功
								$rst = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], "autochecked", $server['id']));
								FJS_P_AT(L("新增成功，并已自动开户，开户信息已经发送到您填写的邮箱中"),'?');
							} else {
								//发送失败，通知管理员
								$rst = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], "发送开户邮件给客户失败，请手动处理！", $server['id']));
								FJS_P_AT(L("新增成功，并已自动开户，等待邮件发送"),'?');
							}
						}
					} else {
						//审核失败 发送邮件提醒管理员人为审核
						$result = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], $result['info'], $server['id']));
					}
				} else {
					$result = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], '', $server['id']));
				}
	
				FJS_P_AT(L("新增数据成功"),'?');
				
			} else {
				FJS_P_AC(L("新增数据失败"));
			}
		} else {
			FJS_P_AC(L("新增数据失败"));
		}
	}
} else if($Clause == 'delete') {
	$Id = FPostInt("id");
	
	$access_ = 0;
	if($Id != $DRAdmin['id']){
		$access_ = chk_in_access('删除客户');
	}
	if(!$access_){
		ajaxReturn($data, L("您没有权限编辑"), 1);
	}
	
	admin_action_log();

	
	$data = $DB->getDRow("select * from t_member where id = '{$Id}'");
	if ($data) {
		if ($data['STATUS'] == '-1') {
			//删除
			ajaxReturn($data, L("操作出错"), 1);
			//已经被修改过
		} else {
			$result = $DB->query("update t_member set status = -1 where id = '{$Id}'");
			if ($result > 0) {
				$DB->query("update t_member_mtlogin set status = -1 where member_id = '{$Id}'");
				$DB->query("update t_member set parent_id = 0 where parent_id = '{$Id}'");

				ajaxReturn($data, L("操作成功"), 0);
			} else {
				ajaxReturn($data, L("操作失败"), 3);
				//保存失败
			}
		}
	}
	ajaxReturn($data, L("数据不存在"), 2);
}else if($Clause == 'can_login') {
	admin_action_log();

	$id = $_REQUEST['id'];
	$banned_login = $_REQUEST['banned_login'];
	$DB->query("update t_member set banned_login = '{$banned_login}' where id = '{$id}'");
	this_success(L('修改成功'));
}else if($Clause == 'gettree') {
	//gettree();
	exit;
}


if(!in_array($Clause,array('main','member_login_log','addinfo','detail','editpwd'))){
	$Clause = 'main';
}
require_once('member/' . $Clause . '.php');

