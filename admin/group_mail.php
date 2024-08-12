<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');


if($Clause == 'savesendgroupmessage'){
	admin_action_log();
	
	$data = array(
		'content'=>FPostStr('content'),
	);
	if (!$data['content']) {
		FJS_P_AC(L('请填写短信内容'));
	}
	
	if($Id <= 0){
		$data['messagelist'] = FPostStr('messagelist');
		
		$server_id           = $DRAdmin['server_id'];
		$data['create_time'] = time();
		$data['server_id']   = $server_id;
		$id                  = $DB->insert("t_message_group",$data);
		if ($id) {
			if ($data['messagelist']) {
				$list = explode(",", $data['messagelist']);
				foreach ($list as $key => $val) {
					$info['groupid']   = $id;
					$info['phone']     = $val;
					$info['member_id'] = intval($DB->getField("select id from t_member where phone = '{$val}' and server_id = '{$server_id}'"));
					$info['server_id'] = $server_id;
					$info['type'] = 1;
					$info['create_time'] = time();
					
					$DB->insert("t_mail_group_user",$info);
				}
			} else {
				$list = $DB->getField2Arr("select phone,id from t_member where server_id = '{$server_id}' and status = 1 and phone <> ''");
				foreach ($list as $key => $val) {
					$info['groupid']   = $id;
					$info['phone']     = $key;
					$info['member_id'] = $val;
					$info['server_id'] = $server_id;
					$info['type'] = 1;
					$info['create_time'] = time();
					
					$DB->insert("t_mail_group_user",$info);
				}
			}
	
			FJS_P_AT(L('创建群发任务成功'),'?type=1');
		} else {
			FJS_P_AC(L('创建群发任务失败'));
		}
	}else{
		$DB->update("t_message_group",$data,"where id = '{$Id}'");
		
		FJS_P_AT(L('修改成功'),'?type=1');
	}
}else if($Clause == 'deletegroupmessage'){
	admin_action_log();
	
	$DB->query("delete from t_message_group where id = '{$Id}'");
	$DB->query("delete from t_mail_group_user where groupid = '{$Id}' and status = 0");
	
	FRedirect(FPrevUrl());
}else if($Clause == 'savesendgroupemail'){
	admin_action_log();
	
	$data = array(
		'title'=>FPostStr('title'),
		'send_email'=>FPostStr('send_email'),
		'email_pass'=>FPostStr('email_pass'),
		'email_port'=>FPostStr('email_port'),
		'email_smtp'=>FPostStr('email_smtp'),
		'email_nickname'=>FPostStr('email_nickname'),
		'content'=>$_POST['content'],//htmlentities
	);
	if (!$data['title']) {
		FJS_P_AC(L('请填写邮件标题'));
	}
	if (!$data['content']) {
		FJS_P_AC(L('请填写邮件内容'));
	}
	if (!$data['send_email']) {
		FJS_P_AC(L('请填写发件邮箱'));
	}
	if (!$data['email_pass']) {
		FJS_P_AC(L('请填写发件邮箱密码'));
	}
	if (!$data['email_port']) {
		FJS_P_AC(L('请填写邮件服务器端口'));
	}
	if (!$data['email_smtp']) {
		FJS_P_AC(L('请填写SMTP服务器地址'));
	}
	if (!$data['email_nickname']) {
		FJS_P_AC(L('发件人昵称'));
	}
	
	if($Id <= 0){
		$data['emaillist'] = FPostStr('emaillist');
		
		$server_id           = $DRAdmin['server_id'];
		$data['create_time'] = time();
		$data['server_id']   = $server_id;
		$id                  = $DB->insert("t_mail_group",$data);
		if ($id) {
			if ($data['emaillist']) {
				$list = explode(",", $data['emaillist']);
				foreach ($list as $key => $val) {
					$info['groupid']   = $id;
					$info['email']     = $val;
					$info['member_id'] = intval($DB->getField("select id from t_member where email = '{$val}' and server_id = '{$server_id}'"));
					$info['server_id'] = $server_id;
					$info['create_time'] = time();
					
					$DB->insert("t_mail_group_user",$info);
				}
			} else {
				$list = $DB->getField2Arr("select email,id from t_member where server_id = '{$server_id}' and status = 1 and email <> ''");
				foreach ($list as $key => $val) {
					$info['groupid']   = $id;
					$info['email']     = $key;
					$info['member_id'] = $val;
					$info['server_id'] = $server_id;
					$info['create_time'] = time();
					
					$DB->insert("t_mail_group_user",$info);
				}
			}
	
			FJS_P_AT(L('创建群发任务成功'),'?');
		} else {
			FJS_P_AC(L('创建群发任务失败'));
		}
	}else{
		$DB->update("t_mail_group",$data,"where id = '{$Id}'");
		
		FJS_P_AT(L('修改成功'),'?');
	}
}else if($Clause == 'deletegroupmail'){
	admin_action_log();
	
	$DB->query("delete from t_mail_group where id = '{$Id}'");
	$DB->query("delete from t_mail_group_user where groupid = '{$Id}' and status = 0");
	
	FRedirect(FPrevUrl());
}

if(!in_array($Clause,array('main','sendgroupemail','sendgroupmessage'))){
	$Clause = 'main';
}
require_once('group_mail/' . $Clause . '.php');

