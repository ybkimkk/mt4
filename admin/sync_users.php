<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'init_sync'){
	admin_action_log();

	set_time_limit(100000);
	
	loginfo('sync_users',"----------------------初始同步开始-----------------------------------");
	loginfo('sync_users',"访问IP:" . FGetClientIP());
	//检验是否合法

	$mtserver = FPostInt('serverid');
	$cover = FPostInt('cover'); //cover=1空邮箱空手机，采用login@abc.com
	$alias = FPostStr('alias');
	if (strlen($alias) <= 0) {
		$alias = 'mydomain.com';
	}
	
	if ($mtserver <= 0) {
		loginfo('sync_users','服务器ID' . $mtserver . '参数错误，同步停止');
		
		FJS_P_AJ(L('服务器ID' . $mtserver . '参数错误，同步停止'),'window.parent.location.href="?";');
	}

	$server = $DB->getDRow("select * from `t_mt4_server` where id = '{$mtserver}' and `status` = 1");
	if (!$server) {
		loginfo('sync_users','服务器ID' . $mtserver . '不存在，同步停止');
		
		FJS_P_AJ(L('服务器ID' . $mtserver . '不存在，同步停止'),'window.parent.location.href="?";');
	}

	//默认角色
	$defaultRoleVal = C('DEAFAULT_ROLE');
	
	//报表数据库
	$DBR = new CMySQLi();
	$DBR->connnect($DBSettings['dbHost'],$DBSettings['dbUser'],$DBSettings['dbPwd'],$server['db_name'],$DBSettings['dbPort']);
	
	if($server['ver'] == 5){
		//按分组来，因为数据库里的是所有的用户
		$groups = $DB->getField2Arr("select id,`group` from `t_groups` where server_id = '{$server['id']}'");
		if($groups){
			$groupSql = "where `Group` in ('" . str_replace('\\','\\\\',implode("','",$groups)) . "')";
		}else{
			$groupSql = 'where 1 = 2';
		}
		//echo "select Email as EMAIL,Login as LOGIN,Name as NAME,Phone as PHONE,Address as ADDRESS,City as CITY,ID,Agent as AGENT_ACCOUNT from `mt5_users` " . $groupSql;exit;
		$mt4members = $DBR->getDTable("select Email as EMAIL,Login as LOGIN,Name as NAME,Phone as PHONE,Address as ADDRESS,City as CITY,ID,Agent as AGENT_ACCOUNT from `mt5_users` " . $groupSql);
	}else{
		$mt4members = $DBR->getDTable("select * from `mt4_users`");
	}

	//同步用户
	foreach ($mt4members as $j => $mt4user) {
		if (strlen($mt4user['EMAIL']) <= 0) {
			if ($cover > 0) {
				$mt4user['EMAIL'] = $mt4user['LOGIN'] . '@' . $alias;
			} else {
				loginfo('sync_users',$mt4user['NAME'] . '的邮箱都为空，忽略，继续下一个用户');
				continue;
			}
		}
		
		$condition = '';
		if (strlen($mt4user['EMAIL']) > 0) {
			$condition = "where email='" . $mt4user['EMAIL'] . "' AND status in (0,1) ";
		} else {
			continue;
		}

		$condition = $condition . " and server_id = " . $mtserver;
		
		loginfo('sync_users','对用户' . $mt4user['NAME'] . '进行同步操作');
		
		$member = $DB->getDRow("select * from `t_member` {$condition}");
		if ($member) {
			this_addMtlogin($member['id'], $mt4user, $mtserver);
		} else {
			this_addMember($mt4members, $mt4user, $mtserver, $defaultRoleVal);
		}
	}
	
	loginfo('sync_users','同步结束');
	
	FJS_P_AJ(L('同步成功'),'window.parent.location.href="?";');
}



//绑定mtlogin数据
function this_addMtlogin($memberid, $mt4member, $serverid) {
	global $DB;
	
	$mtlogin = $DB->getDRow("select * from `t_member_mtlogin` where `loginid` = '{$mt4member['LOGIN']}' and `mtserver` = '{$serverid}' and `status` = 1");
	if ($mtlogin) {
		if ($memberid == $mtlogin['member_id']) {//已经被自己绑定
			loginfo('sync_users',$mt4member['LOGIN'] . '已经被' . $mtlogin['member_id'] . ' 英文名：' . $mtmember['nicnname'] . '绑定,无须重复绑定');
			
			return;
		}
		
		$mtmember = $DB->getDRow("select * from `t_member` where `id` = '{$mtlogin['member_id']}'");
		$membername = "无";
		if ($mtmember) {
			//if($mtmember['status']=='1'){//正常状态
			//loginfo('sync_users',$mt4member['LOGIN'].'已经被'.$mtlogin['member_id'].' 英文名：'.$mtmember['nicnname'].'绑定，不能继续绑定');
			//} 
			$membername = $mtmember['nicnname'];
		}
		
		//更新以往的绑定关系
		$retmg = $DB->query("update `t_member_mtlogin` set `status` = -1 where id = '{$mtlogin['id']}'");
		
		loginfo('sync_users','删除' . $mt4member['LOGIN'] . '和会员' . $mtlogin['member_id'] . '以前的绑定关系，删除' . ($retmg == 1 ? '成功' : '失败'));
	}
	
	$sqlFVArr = array();
	$sqlFVArr['member_id'] = $memberid;
	$sqlFVArr['loginid'] = $mt4member['LOGIN'];
	$sqlFVArr['mtserver'] = $serverid;
	$sqlFVArr['create_time'] = time();
	$sqlFVArr['status'] = '1';

	//返佣账户
	$mtlogins = $DB->getDTable("select * from `t_member_mtlogin` where `member_id` = '{$memberid}' and `status` = 1 and `mt_type` = 0");
	if (count($mtlogins) > 0) {//存在返佣账户
		$sqlFVArr['mt_type'] = '1'; //新增交易账户
	} else {//不存在返佣账户
		$sqlFVArr['mt_type'] = '0'; //返佣账户
	}
	
	loginfo('sync_users','绑定会员:' . $memberid . '(' . $mt4member["NAME"] . ')' . ' mtlogin:' . $mt4member['LOGIN'] . ' 账户类型：' . $sqlFVArr['mt_type']);
	
	$affectId = $DB->insert('t_member_mtlogin',$sqlFVArr);
	
	loginfo('sync_users','会员:' . $mt4member["NAME"] . ' mtlogin:' . $affectId . '绑定成功');
}

//增加会员数据
function this_addMember($mt4members, $mt4member, $serverid, $defaultRoleVal) {
	global $DB;
	
	$sqlFVArr = array();
	$sqlFVArr['email'] = $mt4member['EMAIL'];
	$sqlFVArr['nickname'] = $mt4member['NAME'];
	$sqlFVArr['realname'] = $mt4member['NAME'];
	$sqlFVArr['phone'] = $mt4member['PHONE'];
	$sqlFVArr['address'] = $mt4member['ADDRESS'];
	$sqlFVArr['province'] = $mt4member['CITY'];
	$sqlFVArr['identity'] = $mt4member['ID'];
	$sqlFVArr['create_time'] = time();
	$sqlFVArr['update_time'] = time();
	$sqlFVArr['server_id'] = $serverid; //保存ID
	$sqlFVArr['userType'] = "direct"; //直接客户
	$sqlFVArr['password'] = md5(rand_string(8));
	$sqlFVArr['headimg'] = '/assets/images/facedefault.jpeg';
	$sqlFVArr['f_roleId'] = $defaultRoleVal;
	loginfo('sync_users','同步会员:' . $sqlFVArr['nickname'] . '(' . $sqlFVArr['email'] . ')' . ' phone:' . $sqlFVArr['phone'] . ' 账户类型：' . $sqlFVArr['userType']);
	$memid = $DB->insert('t_member',$sqlFVArr);
	if ($memid) {
		loginfo('sync_users','同步会员成功ID:' . $memid);
		
		this_addMtlogin($memid, $mt4member, $serverid);
		
		//更新上下级关系
		if ($mt4member['AGENT_ACCOUNT'] == 0 || $mt4member['AGENT_ACCOUNT'] == 1) {
			loginfo('sync_users',$mt4member['NAME'] . '无上级，忽略更新上下级操作');
		} else {
			foreach ($mt4members as $key2 => $mtuser) {
				if ($mtuser['LOGIN'] == $mt4member['AGENT_ACCOUNT']) {//找到上级
					$mtlogin = $DB->getDRow("select * from `t_member_mtlogin` where `loginid` = '{$mtuser['LOGIN']}' and `mtserver` = '{$serverid}' and `status` = 1");
					if ($mtlogin) {//更新上级
						if ($memid == $mtlogin['member_id']) {
							loginfo('sync_users',$memid . "上级为自己，停止本次更新上级");
							break;
						}
						
						$pdata = array();
						$pdata['parent_id'] = $mtlogin['member_id'];
						$pdata['update_time'] = time();
						$DB->update('t_member',$pdata,"where id = '{$memid}'");
						
						loginfo('sync_users',$memid . '更新上级成功，上级ID:' . $pdata['parent_id']);
					} else {
						loginfo('sync_users',$user['NAME'] . '上级不存在，请手动更新');
					}
				}
			}
		}
	} else {
		loginfo('sync_users','会员:' . $mt4member['NAME'] . '绑定失败');
	}
}











if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('sync_users/' . $Clause . '.php');

