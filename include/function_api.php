<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/MtApiModel.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/mt5_api/mt5_api.php');

function api($url, $param = array()) {
    $arr = explode('://', $url);
	if($arr[0] == 'gold'){
		$fun = $arr[1];
		$fun = 'api_gold_' . str_ireplace('/','_',$fun);
		$result = $fun($param);

		$result === '' && $result    = array('status' => 0, 'info' => '接口调用完成！');
		$result === true && $result  = array('status' => 0, 'info' => '接口调用完成！');
		$result === false && $result = array('status' => 1, 'info' => '接口调用出错！' . $result);
		return $result;
	}else{
		$result = array('status' => 1, 'info' => 'ERROR:Does not support api [' . $arr[0] . ']');
	}
}

//审核赠金提醒邮件
function api_gold_Mail_sendCreditInEmail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);

	loginfo('api_gold_Mail_sendCreditInEmail','sendCreditInEmail args:' . json_encode($map));

	$list = $DB->getDRow("select * from t_credit_record where Id = '{$map['id']}'");
	if ($list) {
		$member = $DB->getDRow("select * from t_member where id = '{$list['MemberId']}'");

		$template = get_mail_template_lang('gold_audit_credit_email');
		$template_phone = get_mail_template_lang('gold_audit_credit_phone');
		if (!$template && !$template_phone) {
			loginfo('api_gold_Mail_sendCreditInEmail',"gold_audit_credit_email or gold_audit_credit_phone template not exist ");
			return false;
		}

		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);
		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $list['MtLogin'];
		$dataArr['amount'] = $list['Result'];
		$dataArr['service_email'] = getConfigValue('EMAIL', $member['server_id']);
		$dataArr['login_url'] = C('LOGIN_URL');
		$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
		$template['back_image'] && $dataArr['back_image'] = $template['back_image'];

		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));

		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'gold_audit_credit_email';
		$smtpConf['table_name'] = 'credit_record';
		$smtpConf['table_id'] = $list['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = Mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_sendCreditInEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'gold_audit_credit_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_sendCreditInEmail',"短信发送结果：" . json_encode($res));
		}
	}
	if(!$res){$res = $res1;}
	return $res;
}

//赠金驳回提醒
function api_gold_Mail_rejectedCreditInEmail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);

	loginfo('api_gold_Mail_rejectedCreditInEmail','rejectedCreditInEmail args:' . json_encode($map));

	$list = $DB->getDRow("select * from t_credit_record where Id = '{$map['id']}'");
	if ($list) {
		$member = $DB->getDRow("select * from t_member where id = '{$list['MemberId']}'");

		$template = get_mail_template_lang('rejected_credit_in_email');
		$template_phone = get_mail_template_lang('rejected_credit_in_phone');
		if (!$template && !$template_phone) {
			loginfo('api_gold_Mail_rejectedCreditInEmail',"rejected_credit_in_email or rejected_credit_in_phone template not exist ");
			return false;
		}

		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);
		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $list['MtLogin'];
		$dataArr['amount'] = $list['Result'];
		$dataArr['message'] = $list['Memo'];
		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));

		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'rejected_credit_in_email';
		$smtpConf['table_name'] = 'credit_record';
		$smtpConf['table_id'] = $list['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = Mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_rejectedCreditInEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'rejected_credit_in_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_rejectedCreditInEmail',"短信发送结果：" . json_encode($res));
		}
	}
	if(!$res){$res = $res1;}
	return $res;
}

//申请出金驳回发送邮件
function api_gold_Mail_rejectBalanceOutEmail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);
	
	loginfo('api_gold_Mail_rejectBalanceOutEmail',"rejectBalanceOutEmail args:" . json_encode($map));
	
	$outmoney = $DB->getDRow("select * from t_outmoney where id = '{$map['id']}'");
	if ($outmoney) {
		$member = $DB->getDRow("select * from t_member where id = '{$outmoney['member_id']}'");
		$template = get_mail_template_lang('reject_balance_out_email');
		$template_phone = get_mail_template_lang('reject_balance_out_phone');
		if (!$template && !$template_phone) {
			loginfo('api_gold_Mail_rejectBalanceOutEmail',"reject_balance_out_email or reject_balance_out_phone template not exist ");
			return false;
		}
		
		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);

		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $outmoney['mtid'];
		$dataArr['amount'] = $outmoney['number'];
		$dataArr['message'] = $outmoney['content'];

		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));
		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'reject_balance_out_email';
		$smtpConf['table_name'] = 'outmoney';
		$smtpConf['table_id'] = $outmoney['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = Mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_rejectBalanceOutEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'reject_balance_out_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_rejectBalanceOutEmail',"短信发送结果：" . json_encode($res));
		}
	}
	if(!$res){$res = $res1;}
	return $res;
}





//重置密码
function api_gold_Mail_resetpasswordmail($param_) {
	global $DB;

	$map = array(
		'email'=>$param_[0],
		'server_id'=>$param_[1],
	);

	loginfo('api_gold_Mail_resetpasswordmail',"resetpasswordmail args:" . json_encode($map));
	$email = $map["email"];
	if ($map["server_id"]) {
		$server_id = $map["server_id"];
	}
	$data = $DB->getDRow("select * from t_member where email = '{$email}' and status = 1 and server_id = '{$server_id}'");
	$template = get_mail_template_lang('reset_password_email');
	if (!$template) {
		loginfo('api_gold_Mail_resetpasswordmail',"reset_password_email template not exist ");
		return false;
	}
	$nowTime = time();
	$key = md5(uuid());
	$dataArr['webname'] = C('APP_WEB_NAME');
	$title = replace_variable($dataArr, $template['title']);
	$url = (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . CC_ADMIN_ROOT_FOLDER . 'setpassword.php?key=' . $key . '&user=' . $data['nickname'] . '&email=' . urlencode($data['email']) . '&mtserver=' . $data['server_id'];
	$dataArr['username'] = $data['nickname'];
	$dataArr['email'] = $map['email'];
	$dataArr['reset_url'] = $url;
	$dataArr['service_email'] = getConfigValue('EMAIL', $data['server_id']);
	$dataArr['login_url'] = C('LOGIN_URL');
	$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
	$template['back_image'] && $dataArr['back_image'] = $template['back_image'];
	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));

	$smtpConf = getSmtpConfig($data['server_id']);
	$smtpConf['temp_name'] = 'reset_password_email';
	$smtpConf['table_name'] = 'member';
	$smtpConf['server_id'] = $data['server_id'];
	$smtpConf['table_id'] = $data['id'];
	//发送提醒邮件
	$res = Mail_send($data['email'], $title, $content, $html = true, null, $smtpConf);
	$DB->query("update t_member set update_time = '{$nowTime}',active_code = '{$key}' where id = '{$data['id']}'");
	loginfo('api_gold_Mail_resetpasswordmail',"resetpasswordmail return " . json_encode($res));
	return $res;
}



//申请入金成功后发送邮件
function api_gold_Mail_sendBalanceInEmail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);

	loginfo('api_gold_Mail_sendBalanceInEmail','sendBalanceInEmail args:' . json_encode($map));
	$inmoney = $DB->getDRow("select * from t_inmoney where id = '{$map['id']}'");
	$member = $DB->getDRow("select * from t_member where id = '{$inmoney['member_id']}'");
	$template = get_mail_template_lang('balance_in_email');
	$template_phone = get_mail_template_lang('balance_in_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_sendBalanceInEmail',"balance_in_email or balance_in_phone template not exist ");
		return false;
	}

	if ($inmoney) {
		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);
		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $inmoney['mtid'];
		$dataArr['amount'] = $inmoney['number'];

		$dataArr['service_email'] = getConfigValue('EMAIL', $member['server_id']);
		$dataArr['login_url'] = C('LOGIN_URL');
		$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
		$template['back_image'] && $dataArr['back_image'] = $template['back_image'];

		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));

		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'balance_in_email';
		$smtpConf['table_name'] = 'inmoney';
		$smtpConf['table_id'] = $inmoney['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = Mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_sendBalanceInEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'balance_in_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_sendBalanceInEmail',"短信发送结果：" . json_encode($res));
		}
	}
	
	//开户成功给员工通知
	//after_sendBalanceInEmail($inmoney['member_id'],$inmoney['mtid'],$inmoney['number']);
	if(!$res){$res = $res1;}
	return $res;
}



//入金管理员邮件提醒
function api_gold_Mail_sendDepositNotify($param_) {
	global $DB;

	$map = array(
		'nickname'=>$param_[0],
		'loginid'=>$param_[1],
		'amount'=>$param_[2],
		'type'=>$param_[3],
		'server_id'=>$param_[4],
	);
	
	
	loginfo('api_gold_Mail_sendDepositNotify',"sendDepositNotify arg：" . json_encode($map));
	
	if ($map['type'] == 'remit') {
		$template = get_mail_template_lang('admin_deposit_notify_offline');
		$template_phone = get_mail_template_lang('admin_deposit_notify_offphone');
		if (!$template && !$template_phone) {
			loginfo('api_gold_Mail_sendDepositNotify',"admin_deposit_notify_offline or admin_deposit_notify_offphone template not exist ");
		}
	} else {
		$template = get_mail_template_lang('admin_deposit_notify_online');
		$template_phone = get_mail_template_lang('admin_deposit_notify_phone');
		if (!$template && !$template_phone) {
			loginfo('api_gold_Mail_sendDepositNotify',"admin_deposit_notify_online or admin_deposit_notify_phone template not exist ");
		}
	}

	$dataArr['webname'] = C('APP_WEB_NAME');
	$title = replace_variable($dataArr, $template['title']);

	$dataArr['username'] = $map['nickname'];
	$dataArr['mt4login'] = $map['loginid'];
	$dataArr['amount'] = $map['amount'];
	$dataArr['op_date'] = date("Y-m-d H:i:s", time());
	$dataArr['service_email'] = getConfigValue('EMAIL', $map['server_id']);
	$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
	$template['back_image'] && $dataArr['back_image'] = $template['back_image'];

	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
	$contentmsg = getConfigValue('MESSAGE_SINGE', $map['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));

	$smtpConf = getSmtpConfig($map['server_id']);
	$smtpConf['temp_name'] = 'admin_deposit_notify_online';
	$smtpConf['table_name'] = 'member_mtlogin';
	$smtpConf['table_id'] = $map['loginid'];
	$smtpConf['server_id'] = $map['server_id'];
	//发送提醒邮件
	if ($content) {
		$mailarray = getConfigValue('NOTIFY_EMAIL', $map['server_id']);
		$mailarray = explode(',', $mailarray);
		foreach ($mailarray as $key => $value) {
			$res = Mail_send($value, $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_sendDepositNotify',"邮件发送结果：" . json_encode($res));
		}
	}
	if ($template_phone) {
		$smtpConf['temp_name'] = $template_phone['name'];
		$notifyphone = getConfigValue('NOTIFY_PHONE', $map['server_id']);
		$phonearray = explode(',', $notifyphone);
		foreach ($phonearray as $key => $value) {
			if ($key >= 5)
				continue;
			$res1 = SendMsg_send($value, $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_sendDepositNotify',"短信发送结果：" . json_encode($res));
		}
	}
	loginfo('api_gold_Mail_sendDepositNotify',"sendDepositNotify return：" . $res);
	if(!$res){$res = $res1;}
	return $res;
}


//MT4拒绝申请提醒
function api_gold_Mail_sendresetMt4Pwd($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
		'email'=>$param_[1],
		'nickname'=>$param_[2],
		'loginid'=>$param_[3],
		'type'=>$param_[4],
		'newpwd'=>$param_[5],
		'server_id'=>$param_[6],
	);
	
	loginfo('api_gold_Mail_sendresetMt4Pwd',"sendresetMt4Pwd arg：" . json_encode($map));
	$template = get_mail_template_lang('user_reset_mt4_pwd');
	$template_phone = get_mail_template_lang('user_reset_mt4_pwd_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_sendresetMt4Pwd',"user_reset_mt4_pwd or user_reset_mt4_pwd_phone  template not exist ");
		return false;
	}

	$dataArr['webname'] = C('APP_WEB_NAME');
	$dataArr['username'] = $map['nickname'];
	$dataArr['loginid'] = $map['loginid'];
	$dataArr['type'] = $map['type'];
	$dataArr['newpwd'] = $map['newpwd'];

	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));

	$smtpConf = getSmtpConfig($map['server_id']);
	$smtpConf['temp_name'] = 'user_reset_mt4_pwd';
	$smtpConf['table_name'] = 'member_mtlogin';
	$smtpConf['table_id'] = $map['id']; //没有ID邮箱代替
	$smtpConf['server_id'] = $map['server_id'];
	if ($content) {
		$res = Mail_send($map['email'], $template['title'], $content, $html = true, null, $smtpConf);
		loginfo('api_gold_Mail_sendresetMt4Pwd',"邮件发送结果：" . json_encode($res));
	}
	if ($template_phone) {
		$smtpConf['temp_name'] = 'user_reset_mt4_pwd_phone';
		
		$member = $DB->getDRow("select * from t_member where email = '{$map['email']}' and status = 1 and server_id = '{$map['server_id']}'");
		$phone = $member['phone'];
		
		$contentmsg = getConfigValue('MESSAGE_SINGE', $map['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));
		$res1 = SendMsg_send($phone, $contentmsg, $smtpConf);
		loginfo('api_gold_Mail_sendresetMt4Pwd',"短信发送结果：" . json_encode($res));
	}
	if(!$res){$res = $res1;}
	return $res;
}


//申请出金成功后发送邮件
function api_gold_Mail_sendBalanceOutEmail($param_) {
	global $DB;
	
	$map = array(
		'id'=>$param_[0],
	);
	
	loginfo('api_gold_Mail_sendBalanceOutEmail',"sendBalanceOutEmail args:" . json_encode($map));
	$outmoney = $DB->getDRow("select * from t_outmoney where id = '{$map['id']}'");
	$member = $DB->getDRow("select * from t_member where id = '{$outmoney['member_id']}'");
	$template = get_mail_template_lang('balance_out_email');
	$template_phone = get_mail_template_lang('balance_out_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_sendBalanceOutEmail',"balance_out_email or balance_out_phone template not exist ");
		return false;
	}

	if ($outmoney) {
		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);

		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $outmoney['mtid'];
		$dataArr['amount'] = $outmoney['number'];

		$dataArr['service_email'] = getConfigValue('EMAIL', $member['server_id']);
		$dataArr['login_url'] = C('LOGIN_URL');
		$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
		$template['back_image'] && $dataArr['back_image'] = $template['back_image'];

		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));
		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'balance_out_email';
		$smtpConf['table_name'] = 'outmoney';
		$smtpConf['table_id'] = $outmoney['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = Mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_sendBalanceOutEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'balance_out_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_sendBalanceOutEmail',"短信发送结果：" . json_encode($res));
		}
	}
	
	//开户成功给员工通知
	//$this->after_sendBalanceOutEmail($outmoney['member_id'],$outmoney['mtid'],$outmoney['number']);
	if(!$res){$res = $res1;}
	return $res;
}

//注册时发送邮箱验证提醒邮件
function api_gold_Mail_sendVemail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);

	loginfo('api_gold_Mail_sendVemail',"sendVemail args:" . json_encode($map));
	
	$data = $DB->getDRow("select * from t_member where id = '{$map['id']}'");

	$template = get_mail_template_lang('active_reg_email');
	if (!$template) {
		loginfo('api_gold_Mail_sendVemail',"active_reg_email template not exist ");
		return false;
	}

	$nowTime = time();
	$dataArr['webname'] = C('APP_WEB_NAME');
	$title = replace_variable($dataArr, $template['title']);
	if (strlen($data['active_code']) < 32) {
		$data['active_code'] = uuid();
		$DB->query("update t_member set active_code = '{$data['active_code']}' where id = '{$map['id']}'");
	}
	
	$url = (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . CC_ADMIN_ROOT_FOLDER . 'chkmail.php?id=' . $data['id'] . '&code=' . $data['active_code'] . '&mtserver=' . $data['server_id'] . '&t=' . time();

	$dataArr['username'] = $data['nickname'];
	$dataArr['active_url'] = $url;
	$dataArr['password'] = $_REQUEST['password'];
	$dataArr['email'] = $data['email'];
	$dataArr['service_email'] = getConfigValue('EMAIL', $data['server_id']);
	$dataArr['login_url'] = C('LOGIN_URL');

	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));

	$smtpConf = getSmtpConfig($data['server_id']);
	$smtpConf['temp_name'] = 'active_reg_email';
	$smtpConf['table_name'] = 'member';
	$smtpConf['server_id'] = $data['server_id'];
	$smtpConf['table_id'] = $map['id'];
	
	loginfo('api_gold_Mail_sendVemail',"sendVemail return " . json_encode($smtpConf));
	
	//发送提醒邮件
	$res = Mail_send($data['email'], $title, $content, $html = true, null, $smtpConf);
	$DB->query("update t_member set update_time = '{$nowTime}' where id = '{$map['id']}'");
	loginfo('api_gold_Mail_sendVemail',"sendVemail return " . json_encode($res));
	
	return $res;
}

//满足赠金申请提醒邮件
function api_gold_Mail_creditInEmail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);
	
	loginfo('api_gold_Mail_creditInEmail','creditInEmail args:' . json_encode($map));
	$inmoney = $DB->getDRow("select * from t_inmoney where id = '{$map['id']}'");
	$member = $DB->getDRow("select * from t_member where id = '{$inmoney['member_id']}'");
	$template = get_mail_template_lang('credit_in_email');
	$template_phone = get_mail_template_lang('credit_in_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_creditInEmail',"credit_in_email or credit_in_phone template not exist ");
		return false;
	}

	if ($inmoney) {
		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);
		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $inmoney['mtid'];
		$dataArr['amount'] = $inmoney['number'];
		$dataArr['service_email'] = getConfigValue('EMAIL', $member['server_id']);
		$dataArr['login_url'] = C('LOGIN_URL');
		$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
		$template['back_image'] && $dataArr['back_image'] = $template['back_image'];

		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));

		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'credit_in_email';
		$smtpConf['table_name'] = 'inmoney';
		$smtpConf['table_id'] = $inmoney['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_creditInEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'credit_in_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_creditInEmail',"短信发送结果：" . json_encode($res));
		}
	}
	if(!$res){$res = $res1;}
	return $res;
}

//申请入金驳回发送邮件
function api_gold_Mail_rejectBalanceInEmail($param_) {
	global $DB;

	$map = array(
		'id'=>$param_[0],
	);

	loginfo('api_gold_Mail_rejectBalanceInEmail','rejectBalanceInEmail args:' . json_encode($map));
	$inmoney = $DB->getDRow("select * from t_inmoney where id = '{$map['id']}'");
	$member = $DB->getDRow("select * from t_member where id = '{$inmoney['member_id']}'");
	$template = get_mail_template_lang('reject_balance_in_email');
	$template_phone = get_mail_template_lang('reject_balance_in_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_rejectBalanceInEmail',"reject_balance_in_email or reject_balance_in_phone template not exist ");
		return false;
	}

	if ($inmoney) {
		$dataArr['webname'] = C('APP_WEB_NAME');
		$title = replace_variable($dataArr, $template['title']);
		$dataArr['username'] = $member['nickname'];
		$dataArr['op_date'] = date("Y-m-d H:i:s", time());
		$dataArr['mt4login'] = $inmoney['mtid'];
		$dataArr['amount'] = $inmoney['number'];
		$dataArr['message'] = $inmoney['content'];


		$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
		$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));

		$smtpConf = getSmtpConfig($member['server_id']);
		$smtpConf['temp_name'] = 'reject_balance_in_email';
		$smtpConf['table_name'] = 'inmoney';
		$smtpConf['table_id'] = $inmoney['id'];
		$smtpConf['server_id'] = $member['server_id'];
		//发送提醒邮件
		if ($content) {
			$res = Mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_rejectBalanceInEmail',"邮件发送结果：" . json_encode($res));
		}
		if ($template_phone) {
			$smtpConf['temp_name'] = 'reject_balance_in_phone';
			$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_rejectBalanceInEmail',"短信发送结果：" . json_encode($res));
		}
	}
	if(!$res){$res = $res1;}
	return $res;
}

//开户申请审核
function api_gold_Mt4api_checkMt4($param_) {
	global $DB;

	$map = array(
		'loginid'=>$param_[0],
		'password'=>$param_[1],
		'memberid'=>$param_[2],
		'mtserver'=>$param_[3],
		'group'=>$param_[4],
		'applyid'=>$param_[5],
	);
	
	loginfo('api_gold_Mt4api_checkMt4',"checkMt4 args:" . json_encode($map));
	
	if (!is_numeric($map['loginid'])) {
		$result = array('status' => -1, 'info' => 'MT4账户' . $map['loginid'] . '格式不正确');
		
		loginfo('api_gold_Mt4api_checkMt4',"checkMt4 not number:" . json_encode($result));
		
		return $result;
	}
	
	$qlogin = $DB->getDRow("select * from `t_member_mtlogin` where `loginid` = '{$map['loginid']}' and `status` = 1 and `mtserver` = '{$map['mtserver']}'"); //查询是否已经绑定
	if ($qlogin) {
		$result = array('status' => -1, 'info' => '当前账号已经绑定');
		
		loginfo('api_gold_Mt4api_checkMt4',"checkMt4 bind:" . json_encode($result));
		
		return $result;
	}
	
	$mt4data = array();
	$mt4data['member_id'] = $map['memberid'];
	$mt4data['mt_type'] = "0";
	$mt4data['status'] = "1";
	$mt4data['mtserver'] = $map['mtserver'];
	$mt4data['loginid'] = $map['loginid'];
	$mt4data['group'] = $map['group'];
	$mt4data['mtpassword'] = think_encrypt($map['password'], C('PASSWORD_KEY'));
	$mt4data['create_time'] = time();
	$mt4data['update_time'] = time();
	$memlogin = $DB->getDRow("select * from `t_member_mtlogin` where `member_id` = '{$map['memberid']}' and `mt_type` = 0 and `status` = 1");
	if ($memlogin) {
		$mt4data['mt_type'] = "1";
	}
	$loginidret = $DB->insert("t_member_mtlogin",$mt4data);
	if ($loginidret) {
		$updateData = array('status' => 1, 
						'check_time' => time(), 
						'login' => $mt4data['loginid'], 
						'password' => $mt4data['mtpassword'], 
						'mt4_server_id' => $mt4data['mtserver'], 
						'group' => $mt4data['group']);
		$DB->update("t_mt4_apply",$updateData,"where id = '{$map['applyid']}'");

		$result = array('status' => 0, 'info' => '审核成功');
		loginfo('api_gold_Mt4api_checkMt4',"checkMt4 return:" . json_encode($result));
		return $result;
	} else {
		$result = array('status' => -1, 'info' => '审核失败');
		loginfo('api_gold_Mt4api_checkMt4',"checkMt4 return:" . json_encode($result));
		return $result;
	}
}


//新申请MT帐号
function api_gold_Mt4api_genericmtlogin($param_){
	global $DB;

	$map = array(
		'id'=>$param_[0],
		'mtserver'=>$param_[1],
		'group'=>$param_[2],
		'leverage'=>$param_[3],
		'name'=>$param_[4],
		'loginid'=>$param_[5],
		'password'=>$param_[6],
	);
	
	loginfo('api_gold_Mt4api_genericmtlogin',"genericmtlogin args:" . json_encode($map));

	$id = $map['id'];	
	$member = $DB->getDRow("select * from `t_member` where id = '{$id}'");
	if (!$member) {
		return array('status' => -1, 'info' => '未找到客户资料');
	}
	
	if(!$member['phone']){
		$member['phone']='';
	}
	
	$agent = 0;
	$sync_agent = getConfigValue('SYNC_TO_MT4_AGENT', $map['mtserver']);
	if ($member['parent_id'] != 0 && $sync_agent) {//上级不是平台
		$prevaccount = $DB->getDRow("select * from `t_member` where id = '{$member['parent_id']}'");//上级帐号
		if ($prevaccount) {//上级存在
			$prevamountmt = $DB->getDRow("select * from `t_member_mtlogin` where `member_id` = '{$prevaccount['id']}' and `mt_type` = 0 and `mtserver` = '{$map['mtserver']}'");
			if ($prevamountmt) {//返佣账户
				$agent = $prevamountmt['loginid'];
			}
		}
	}
	if (!is_numeric($agent)) {
		return array('status' => -1, 'info' => 'agent_account格式不正确！');
	}
	
	$servermap = "where id = '{$map['mtserver']}' and `status` = 1";
	loginfo('api_gold_Mt4api_genericmtlogin',"result:" . $servermap);
	$server = $DB->getDRow("select * from `t_mt4_server` {$servermap}");
	loginfo('api_gold_Mt4api_genericmtlogin',"server:" . json_encode($server));
	
	//查找号码段
	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0') {
		loginfo('api_gold_Mt4api_genericmtlogin',"return:" . json_encode($result));
		return array('status' => -1, 'info' => $retarry['info']);
	}

	if (strlen($map['password']) <= 0) {
		$map['password'] = strtolower(rand_string(1,3).rand_string(1,2).rand_string(5, 6).rand_string(1,1));
	}
	$custom_open = false;

	if ($server['direct_start_number'] && $server['agent_start_number'] && $server['member_start_number']) {
		/*if ($member['userType'] == 'direct') {
			$newnumber = floatval(S('MAX_MT4_NO_SVR_D' . $server['id']));
			loginfo('api_gold_Mt4api_genericmtlogin',"cache maxnumber=" . $newnumber);
			if (empty($map['loginid'])) {
				if ($server['direct_start_number'] && $server['direct_start_number'] > 0) {
					$wherestr = " ( LOGIN >=" . $server['direct_start_number'] . " and LOGIN<=" . $server['direct_end_number'] . ")";
					$depositModel = new DepositModel($server['db_name']);
					$maxnumber = $depositModel->where($wherestr)->max('LOGIN');
					$maxnumber = floatval($maxnumber ? $maxnumber : $server['direct_start_number']);
					loginfo('api_gold_Mt4api_genericmtlogin',"MT report maxnumber = " . $maxnumber);
				}
				$newnumber = max($maxnumber, $newnumber);
				$newnumber = $newnumber + 1;
				if ($newnumber < $server['direct_start_number']) {
					$newnumber = $server['direct_start_number'];
				}
				if ($newnumber >= $server['direct_end_number']) {
					$result = array('status' => -1, 'info' => '当前开户号码：' . $newnumber . ',号码段已用尽，不能申请');
					$this->_put($result);
					return true;
				}
			} else {
				$newnumber = $map['loginid']; //自己输入账号开户
				$custom_open = true;
			}
			$nickname = iconv("UTF-8", "GBK", $map['name']);
			if ($nickname == '') {
				$result = array('status' => -1, 'info' => '开户失败，开户名称不能为空：');
				$this->_put($result);
				return true;
			}
			if ($map['group'] == "") {
				$result = array('status' => -1, 'info' => '开户失败，开户组不能为空：');
				$this->_put($result);
				return true;
			}


			//设置只读
			$open_disable = getConfigValue('OPEN_MT_DISABLE', $map['mtserver']);
			do {//循环跳出号段
				loginfo('api_gold_Mt4api_genericmtlogin',"newnumber:" . $newnumber . " nickname:" . $nickname . ",password:" . $map['password'] . "|" . $invest_password . " group:" . $map['group'] . " agent:" . $agent . ' email:' . $member['email'] . ' leverage:' . $map['leverage']);
				$info = $mt4api->NewAccount($newnumber, $map['password'], $nickname, $map['group'], $agent, $member['email'], $map['leverage'], $member['identity'], $member['phone']);
				$msg = $info['info'];
				$info = $info['ret'];
				loginfo('api_gold_Mt4api_genericmtlogin',"result:" . json_encode($info));
				$infouser = explode('|', $info);
				if (count($infouser) > 1) {
					$result = array('status' => 0, 'info' => $retarry['info'], 'data' => array('user' => $infouser[0], 'pass' => $infouser[1],'rdpass'=>$infouser[2]));
					if (!$custom_open) {
						S('MAX_MT4_NO_SVR_D' . $server['id'], $infouser[0]); //设置最大值缓存
					}
					if ($open_disable == 1) {  //设置开户只读
						$mt4api->SetReadOnly($infouser[0], 1);
					}
					$this->_put($result);
					break;
				} else {
					loginfo('api_gold_Mt4api_genericmtlogin',"error:" . $info);
					if ($info == 'Common error' && !$custom_open) { //占号问题
						$newnumber = $newnumber + 1;
						if ($newnumber > $server['direct_end_number']) {
							$result = array('status' => -1, 'info' => '开户失败，当前开户MT账号为：' . $newnumber . '，已超出号段最大值。');
							$this->_put($result);
							break;
						}
					} else {
						if ($info == 'Common error')
							$msg = "MT号码已被占用";
						$result = array('status' => -1, 'info' => '开户失败，错误信息：' . $msg);
						$this->_put($result);
						break;
					}
				}
			} while (count($info) <= 1);
			return true;
		}
		if ($member['userType'] == 'agent') {
			$newnumber = floatval(S('MAX_MT4_NO_SVR_A' . $server['id']));
			loginfo('api_gold_Mt4api_genericmtlogin',"cache maxnumber=" . $newnumber);
			if (empty($map['loginid'])) {
				if ($server['agent_start_number'] && $server['agent_start_number'] > 0) {
					$wherestr = " ( LOGIN >=" . $server['agent_start_number'] . " and LOGIN<=" . $server['agent_end_number'] . ")";
					$depositModel = new DepositModel($server['db_name']);
					$maxnumber = $depositModel->where($wherestr)->max('LOGIN');
					$maxnumber = floatval($maxnumber ? $maxnumber : $server['agent_start_number']);
					loginfo('api_gold_Mt4api_genericmtlogin',"MT report maxnumber = " . $maxnumber);
				}
				$newnumber = max($maxnumber, $newnumber2);
				$newnumber = $newnumber + 1;
				if ($newnumber < $server['agent_start_number']) {
					$newnumber = $server['agent_start_number'];
				}
				if ($newnumber >= $server['agent_end_number']) {
					$result = array('status' => -1, 'info' => '当前开户号码：' . $newnumber . ',号码段已用尽，不能申请');
					$this->_put($result);
					return true;
				}
			} else {
				$newnumber = $map['loginid']; //自己输入账号开户
				$custom_open = true;
			}
			$nickname = iconv("UTF-8", "GBK", $map['name']);
			if ($nickname == '') {
				$result = array('status' => -1, 'info' => '开户失败，开户名称不能为空：');
				$this->_put($result);
				return true;
			}
			if ($map['group'] == "") {
				$result = array('status' => -1, 'info' => '开户失败，开户组不能为空：');
				$this->_put($result);
				return true;
			}
			//设置只读
			$open_disable = getConfigValue('OPEN_MT_DISABLE', $map['mtserver']);
			do {//循环跳出号段
				loginfo('api_gold_Mt4api_genericmtlogin',"newnumber:" . $newnumber . " nickname:" . $nickname . ",password:" . $map['password'] . "|" . $invest_password . " group:" . $map['group'] . " agent:" . $agent . ' email:' . $member['email'] . ' leverage:' . $map['leverage']);
				$info = $mt4api->NewAccount($newnumber, $map['password'], $nickname, $map['group'], $agent, $member['email'], $map['leverage'], $member['identity'], $member['phone']);
				$msg = $info['info'];
				$info = $info['ret'];
				loginfo('api_gold_Mt4api_genericmtlogin',"result:" . json_encode($info));
				$infouser = explode('|', $info);
				if (count($infouser) > 1) {
					$result = array('status' => 0, 'info' => $retarry['info'], 'data' => array('user' => $infouser[0], 'pass' => $infouser[1],'rdpass'=>$infouser[2]));
					if (!$custom_open) {
						S('MAX_MT4_NO_SVR_A' . $server['id'], $infouser[0]); //设置最大值缓存
					}
					if ($open_disable == 1) {  //设置开户只读
						$mt4api->SetReadOnly($infouser[0], 1);
					}
					$this->_put($result);
					break;
				} else {
					loginfo('api_gold_Mt4api_genericmtlogin',"error:" . $info);
					if ($info == 'Common error' && !$custom_open) { //占号问题
						$newnumber = $newnumber + 1;
						if ($newnumber > $server['agent_end_number']) {
							$result = array('status' => -1, 'info' => '开户失败，当前开户MT账号为：' . $newnumber . '，已超出号段最大值。');
							$this->_put($result);
							break;
						}
					} else {
						if ($info == 'Common error')
							$msg = "MT号码已被占用";
						$result = array('status' => -1, 'info' => '开户失败，错误信息：' . $msg);
						$this->_put($result);
						break;
					}
				}
			} while (count($info) <= 1);
			return true;
		}
		if ($member['userType'] == 'member') {
			$newnumber = floatval(S('MAX_MT4_NO_SVR_M' . $server['id']));
			loginfo('api_gold_Mt4api_genericmtlogin',"cache maxnumber=" . $newnumber);
			if (empty($map['loginid'])) {
				if ($server['member_start_number'] && $server['member_start_number'] > 0) {
					$wherestr = " ( LOGIN >=" . $server['member_start_number'] . " and LOGIN<=" . $server['member_end_number'] . ")";
					$depositModel = new DepositModel($server['db_name']);
					$maxnumber = $depositModel->where($wherestr)->max('LOGIN');
					$maxnumber = floatval($maxnumber ? $maxnumber : $server['member_start_number']);
					loginfo('api_gold_Mt4api_genericmtlogin',"MT report maxnumber = " . $maxnumber);
				}
				$newnumber = max($maxnumber, $newnumber);
				$newnumber = $newnumber + 1;
				if ($newnumber < $server['member_start_number']) {
					$newnumber = $server['member_start_number'];
				}
				if ($newnumber >= $server['member_end_number']) {
					$result = array('status' => -1, 'info' => '当前开户号码：' . $newnumber . ',号码段已用尽，不能申请');
					$this->_put($result);
					return true;
				}
			} else {
				$newnumber = $map['loginid']; //自己输入账号开户
				$custom_open = true;
			}
			$nickname = iconv("UTF-8", "GBK", $map['name']);
			if ($nickname == '') {
				$result = array('status' => -1, 'info' => '开户失败，开户名称不能为空：');
				$this->_put($result);
				return true;
			}
			if ($map['group'] == "") {
				$result = array('status' => -1, 'info' => '开户失败，开户组不能为空：');
				$this->_put($result);
				return true;
			}
			//设置只读
			$open_disable = getConfigValue('OPEN_MT_DISABLE', $map['mtserver']);
			do {//循环跳出号段
				loginfo('api_gold_Mt4api_genericmtlogin',"newnumber:" . $newnumber . " nickname:" . $nickname . ",password:" . $map['password'] . "|" . $invest_password . " group:" . $map['group'] . " agent:" . $agent . ' email:' . $member['email'] . ' leverage:' . $map['leverage']);
				$info = $mt4api->NewAccount($newnumber, $map['password'], $nickname, $map['group'], $agent, $member['email'], $map['leverage'], $member['identity'], $member['phone']);
				$msg = $info['info'];
				$info = $info['ret'];
				loginfo('api_gold_Mt4api_genericmtlogin',"result:" . json_encode($info));
				$infouser = explode('|', $info);
				if (count($infouser) > 1) {
					$result = array('status' => 0, 'info' => $retarry['info'], 'data' => array('user' => $infouser[0], 'pass' => $infouser[1],'rdpass'=>$infouser[2]));
					if (!$custom_open) {
						S('MAX_MT4_NO_SVR_M' . $server['id'], $infouser[0]); //设置最大值缓存
					}
					if ($open_disable == 1) {  //设置开户只读
						$mt4api->SetReadOnly($infouser[0], 1);
					}
					$this->_put($result);
					break;
				} else {
					loginfo('api_gold_Mt4api_genericmtlogin',"error:" . $info);
					if ($info == 'Common error' && !$custom_open) { //占号问题
						$newnumber = $newnumber + 1;
						if ($newnumber > $server['member_end_number']) {
							$result = array('status' => -1, 'info' => '开户失败，当前开户MT账号为：' . $newnumber . '，已超出号段最大值。');
							$this->_put($result);
							break;
						}
					} else {
						if ($info == 'Common error')
							$msg = "MT号码已被占用";
						$result = array('status' => -1, 'info' => '开户失败，错误信息：' . $msg);
						$this->_put($result);
						break;
					}
				}
			} while (count($info) <= 1);
			return true;
		}*/
	}else {
		if (empty($map['loginid'])) {
			if($server['ver'] == 5){
				$maxnumber = $DB->getField("select max(Login) as mLogin from " . $server['db_name'] . ".mt5_users where ( Login >=" . $server['start_number'] . " and Login<=" . $server['end_number'] . ")");
			}else{
				$maxnumber = $DB->getField("select max(LOGIN) as mLogin from " . $server['db_name'] . ".mt4_users where ( LOGIN >=" . $server['start_number'] . " and LOGIN<=" . $server['end_number'] . ")");
			}
			$maxnumber = floatval($maxnumber ? $maxnumber : $server['start_number']);
			loginfo('api_gold_Mt4api_genericmtlogin',"MT report maxnumber = " . $maxnumber);
			
			$newnumber = $maxnumber + 1;
			if ($newnumber < $server['start_number']) {
				$newnumber = $server['start_number'];
			}
			if ($newnumber >= $server['end_number']) {
				loginfo('api_gold_Mt4api_genericmtlogin','当前开户号码：' . $newnumber . ',号码段已用尽，不能申请');
				return array('status' => -1, 'info' => '当前开户号码：' . $newnumber . ',号码段已用尽，不能申请');
			}
		} else {
			$newnumber = $map['loginid']; //自己输入账号开户
			$custom_open = true;
		}
		
		$nickname = iconv("UTF-8", "GBK", $map['name']);
		if ($nickname == '') {
			return array('status' => -1, 'info' => '开户失败，开户名称不能为空');
		}
		
		if ($map['group'] == "") {
			return array('status' => -1, 'info' => '开户失败，开户组不能为空');
		}
		
		//设置只读
		$open_disable = getConfigValue('OPEN_MT_DISABLE', $map['mtserver']);
		do {//循环跳出号段
			loginfo('api_gold_Mt4api_genericmtlogin',"newnumber:" . $newnumber . " nickname:" . $nickname . ",password:(pwd) group:" . $map['group'] . " agent:" . $agent . ' email:' . $member['email'] . ' leverage:' . $map['leverage']);
			
			$info = $mt4api->NewAccount($newnumber, $map['password'], $nickname, $map['group'], $agent, $member['email'], $map['leverage'], $member['identity'], $member['phone']);
			loginfo('api_gold_Mt4api_genericmtlogin',"result:" . json_encode($info));
			
			$msg = $info['info'];
			$info = $info['ret'];
			//161512|0jpgPka|lpR4vEf
			//Network problem
			//Common error
			$infouser = explode('|', $info);
			if (count($infouser) > 1) {
				$result = array('status' => 0, 'info' => $retarry['info'], 'data' => array('user' => $infouser[0], 'pass' => $infouser[1],'rdpass'=>$infouser[2]));
				if ($open_disable == 1) {  //设置开户只读
					$mt4api->SetReadOnly($infouser[0], 1);
				}
				return $result;
			} else {
				loginfo('api_gold_Mt4api_genericmtlogin',"error:" . $info);
				if ($info == 'Common error' && !$custom_open) { //占号问题，非手工指定帐号
					$newnumber = $newnumber + 1;
					if ($newnumber > $server['end_number']) {
						return array('status' => -1, 'info' => '开户失败，当前开户MT账号为：' . $newnumber . '，已超出号段最大值。');
					}
				} else {
					if ($info == 'Common error')
						$msg = "MT号码已被占用";
					return array('status' => -1, 'info' => '开户失败，错误信息：' . $msg);
				}
			}
		} while (count($info) <= 1);
	}
}

//MT4开户申请提醒
function api_gold_Mail_sendApplyMt4Notify($param_){
	global $DB;
	
	$map = array(
		'nickname'=>$param_[0],
		'info'=>$param_[1],
		'server_id'=>$param_[2],
	);

	loginfo('api_gold_Mail_sendApplyMt4Notify',"sendApplyMt4Notify arg：" . json_encode($map));
	$template = get_mail_template_lang('admin_apply_mt4_notify');
	$template_phone = get_mail_template_lang('admin_apply_mt4_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_sendApplyMt4Notify',"admin_apply_mt4_notify or admin_apply_mt4_phone template not exist ");
		return false;
	}

	$dataArr = array();
	$dataArr['webname'] = C('APP_WEB_NAME');
	$title = replace_variable($dataArr, $template['title']);

	$dataArr['username'] = $map['nickname'];
	$dataArr['op_date'] = date("Y-m-d H:i:s", time());
	$dataArr['service_email'] = getConfigValue('EMAIL', $map['server_id']);

	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
	$contentmsg = getConfigValue('MESSAGE_SINGE', $map['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));
	if (C('MT4_AUTO_CHECKED') == '1') {
		if ($map['info']) {
			if ($map['info'] == 'autochecked') {
				//已经自动开户
				$content = $content . "系统已经自动开户审核，请知悉";
				$contentmsg = $contentmsg . "系统已经自动开户审核，请知悉";
			} else {
				$content = $content . "自动开户审核出错，请手动审核，错误信息：" . $map['info'];
				$contentmsg = $contentmsg . "自动开户审核出错，请手动审核，错误信息：" . $map['info'];
			}
		}
	}
	$smtpConf = getSmtpConfig($map['server_id']);
	$smtpConf['temp_name'] = 'admin_apply_mt4_notify';
	$smtpConf['table_name'] = 'member_mtlogin';
	$smtpConf['table_id'] = $map['service_email']; //没有ID邮箱代替
	$smtpConf['server_id'] = $map['server_id'];
	$mail = getConfigValue('NOTIFY_USER_EMAIL', $map['server_id']);
	if (!$mail) {
		$mail = getConfigValue('NOTIFY_EMAIL', $map['server_id']);
	}
	//发送提醒邮件
	if ($content) {
		$mailarray = explode(',', $mail);
		foreach ($mailarray as $key => $value) {
			$res = mail_send($value, $title, $content, $html = true, null, $smtpConf);
			loginfo('api_gold_Mail_sendApplyMt4Notify',"邮件发送结果：" . json_encode($res));
		}
	}
	if ($template_phone) {
		$smtpConf['temp_name'] = 'admin_apply_mt4_phone';
		$notifyphone = getConfigValue('NOTIFY_USER_PHONE', $map['server_id']);
		$phonearray = explode(',', $notifyphone);
		foreach ($phonearray as $key => $value) {
			if ($key >= 5)
				continue;
			$res1 = SendMsg_send($value, $contentmsg, $smtpConf);
			loginfo('api_gold_Mail_sendApplyMt4Notify',"短信发送结果：" . json_encode($res));
		}
	}
	if(!$res){$res = $res1;}
	return $res;
}

//申请MT4成功后发送邮件
function api_gold_Mail_sendMtCheckedmail($param_){
	global $DB;
	
	$map = array(
		'id'=>$param_[0],
		'loginid'=>$param_[1],
		'password'=>$param_[2],
	);

	loginfo('api_gold_Mail_sendMtCheckedmail',"sendMtCheckedmail arg：" . json_encode($map));
	
	$member = $DB->getDRow("select * from `t_member` where `id` = '{$map['id']}'");

	$template = get_mail_template_lang('mt4_checked_email');
	$template_phone = get_mail_template_lang('mt4_checked_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_sendMtCheckedmail',"mt4_checked_email  or  mt4_checked_phone template not exist ");
		return false;
	}

	$dataArr = array();
	$dataArr['webname'] = C('APP_WEB_NAME');
	
	$title = replace_variable($dataArr, $template['title']);

	$dataArr['username'] = $member['nickname'];
	$dataArr['mt4login'] = $map['loginid'];
	$dataArr['mt4password'] = $map['password'];
	$dataArr['service_email'] = getConfigValue('EMAIL', $member['server_id']);
	$dataArr['login_url'] = C('LOGIN_URL');
	$template['back_color'] && $dataArr['back_color'] = $template['back_color'];
	$template['back_image'] && $dataArr['back_image'] = $template['back_image'];
	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
	$contentmsg = getConfigValue('MESSAGE_SINGE', $member['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));
	$smtpConf = getSmtpConfig($member['server_id']);
	$smtpConf['temp_name'] = 'mt4_checked_email';
	$smtpConf['table_name'] = 'member_mtlogin';
	$smtpConf['table_id'] = $map['loginid'];
	$smtpConf['server_id'] = $member['server_id'];
	//发送提醒邮件
	//发送提醒邮件

	if ($content) {
		$res = mail_send($member['email'], $title, $content, $html = true, null, $smtpConf);
		loginfo('api_gold_Mail_sendMtCheckedmail',"邮件发送结果：" . json_encode($res));
	}
	if ($template_phone) {
		$smtpConf['temp_name'] = 'mt4_checked_phone';
		$res1 = SendMsg_send($member['phone'], $contentmsg, $smtpConf);
		loginfo('api_gold_Mail_sendMtCheckedmail',"短信发送结果：" . json_encode($res));
	}
	//after_sendMtCheckedmail($member['id'], $map['loginid']);
	if(!$res){$res = $res1;}
	return $res;	
}



//开户成功给员工通知
function after_sendMtCheckedmail($from_id,$loginid) {
	global $DB;
	/*
	if(!C("NOTIFY_TO_MEMBER")){
		return false;
	}
	$fromuser = M("Member")->where(array('id' => $from_id))->find();
	$parent_member = getParentMember($fromuser['parent_id']);
	if ($parent_member) {
		$list = M("Member")->where(array('id' => array('in', $parent_member), 'status' => 1))->field('nickname,server_id,email')->limit(3)->select();
		loginfo('api_gold_Mt4api_genericmtlogin',"open_mt_tomt4 arg：" . json_encode($list));
		$template = M('mail_template')->where(array('name' => 'open_mt_tomt4', 'status' => '1'))->find();
		foreach ($list as $key => $member) {
			$dataArr['member'] = $member['nickname'];
			$dataArr['nickname'] = $fromuser['nickname'];
			$dataArr['account'] = $loginid;
			$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
			$smtpConf = getSmtpConfig($member['server_id']);
			$smtpConf['temp_name'] = 'open_mt_tomt4';
			$smtpConf['table_name'] = 'member_mtlogin';
			$smtpConf['table_id'] = $map['loginid'];
			$smtpConf['server_id'] = $member['server_id'];
			//发送提醒邮件
			//发送提醒邮件
			if ($content) {
				$res = D('Mail')->send($member['email'], $template['title'], $content, $html = true, null, $smtpConf);
				loginfo('api_gold_Mt4api_genericmtlogin',"邮件发送结果：" . json_encode($res));
			}
		}
	}*/
}



function api_gold_Mail_sendresetMt4Notify($param_){
	global $DB;
	
	$map = array(
		'id'=>$param_[0],
		'email'=>$param_[1],
		'nickname'=>$param_[2],
		'info'=>$param_[3],
		'server_id'=>$param_[4],
	);
	
	loginfo('api_gold_Mail_sendresetMt4Notify',"sendresetMt4Notify arg：" . json_encode($map));
	
	$template = get_mail_template_lang('admin_reset_mt4_notify');
	$template_phone = get_mail_template_lang('admin_reset_mt4_notify_phone');
	if (!$template && !$template_phone) {
		loginfo('api_gold_Mail_sendresetMt4Notify',"admin_reset_mt4_notify  or admin_reset_mt4_notify_phone template not exist ");
		return false;
	}

	$dataArr = array();
	$dataArr['webname'] = C('APP_WEB_NAME');
	$dataArr['username'] = $map['nickname'];
	$dataArr['resetinfo'] = $map['info'];

	$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));

	$smtpConf = getSmtpConfig($map['server_id']);
	$smtpConf['temp_name'] = 'admin_reset_mt4_notify';
	$smtpConf['table_name'] = 'mt4_apply';
	$smtpConf['table_id'] = $map['id']; //没有ID邮箱代替
	$smtpConf['server_id'] = $map['server_id'];
	if ($content) {
		$res = mail_send($map['email'], $template['title'], $content, $html = true, null, $smtpConf);
		
		loginfo('api_gold_Mail_sendresetMt4Notify',"邮件发送结果：" . json_encode($res));
	}
	if ($template_phone) {
		$smtpConf['temp_name'] = 'admin_reset_mt4_notify_phone';
		
		$phone = $DB->getField("select phone from `t_member` where `email` = '{$map['email']}' and `server_id` = '{$map['server_id']}'");
		
		$contentmsg = getConfigValue('MESSAGE_SINGE', $map['server_id']) . html_entity_decode_mail_template(replace_variable($dataArr, $template_phone['content']));
		
		$res1 = SendMsg_send($phone, $contentmsg, $smtpConf);
		
		loginfo('api_gold_Mail_sendresetMt4Notify',"短信发送结果：" . json_encode($res));
	}
	
	//$member = $DB->getDRow("select * from `t_mt4_apply` where id = '{$map['id']}'");
	//after_sendresetMt4Notify($member['member_id']);
	if(!$res){$res = $res1;}
	return $res;
}

//拒绝开户给员工通知
function after_sendresetMt4Notify($from_id,$loginid) {
	global $DB;
	/*
	if(!C("NOTIFY_TO_MEMBER")){
		return false;
	}
	
	$fromuser = $DB->getDRow("select * from `t_member` where id = '{$from_id}'");
	$parent_member = getParentMember($fromuser['parent_id']);
	if ($parent_member) {
		$list = M("Member")->where(array('id' => array('in', $parent_member), 'status' => 1))->field('nickname,server_id,email')->limit(3)->select();
		loginfo('api_gold_Mail_sendMtCheckedmail',"reject_mtapply_tomember arg：" . json_encode($list));
		$template = M('mail_template')->where(array('name' => 'reject_mtapply_tomember', 'status' => '1'))->find();
		foreach ($list as $key => $member) {
			$dataArr['member'] = $member['nickname'];
			$dataArr['nickname'] = $fromuser['nickname'];
			$content = html_entity_decode_mail_template(replace_variable($dataArr, $template['content']));
			$smtpConf = getSmtpConfig($member['server_id']);
			$smtpConf['temp_name'] = 'open_mt_tomt4';
			$smtpConf['table_name'] = 'member_mtlogin';
			$smtpConf['table_id'] = $fromuser['id'];
			$smtpConf['server_id'] = $fromuser['server_id'];
			//发送提醒邮件
			//发送提醒邮件
			if ($content) {
				$res = D('Mail')->send($member['email'], $template['title'], $content, $html = true, null, $smtpConf);
				loginfo('api_gold_Mail_sendMtCheckedmail',"邮件发送结果：" . json_encode($res));
			}
		}
	}*/
}

function replace_variable($dataArr, $content) {
	if ($dataArr['back_image'] || $dataArr['back_color']) {
		$backimg = $dataArr['back_image'] ? "background-image:({$back_image});background-repeat: no-repeat;" : '';
		$backcolor = $dataArr['back_color'] ? "background-color:{$back_color};" : '';
		$content = '<div style="' . $backimg . $backcolor . ';height:100%; overflow: hidden">' . $content;
	}

	foreach ($dataArr as $key => $value) {
		$newArr["\$" . $key] = $value;
	}
	$keys = array_keys($newArr);
	$values = array_values($newArr);
	if ($dataArr['back_image'] || $dataArr['back_color']) {
		$content .= "</div>";
	}
	return str_replace($keys, $values, $content);
}

function api_gold_Member_checkLogin($param_){
	global $DB;
	
	$account = $param_['account'];
	$password = $param_['password'];
	$server_id = $param_['server_id'];

	$logintype = '';
	/*if (checkMobile($account)) {
		$logintype = 'phone';
	}else */
	if (strlen($account) <= 10 && is_numeric($account)) {
		$logintype = 'mt4';
	}else{
		$logintype = 'email';
	}
	
	if('mt4' == $logintype){
		//loginid不判断server_id
		//查找loginid是否存在
		$rsMemberMtLogin = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$account}' and status = 1");
		if($rsMemberMtLogin) {
			//根据mtserver，通过api进行登录
			$ret = api_gold_checkMt4login($account, $password, $rsMemberMtLogin['mtserver']);
			if ($ret != 'success') {
				//登录失败，将对应的客户 登录失败次数+1
				$errorusd = check_login_used($rsMemberMtLogin['member_id']);
				if(is_array($errorusd)){
					$result  = $errorusd;
				}else{
					$result = array('status' => 0, 'info' => $ret, 'login_error' => $errorusd);
				}
				
				member_login_log($account,array('id'=>0,'server_id'=>$server_id),0);
				
				return $result;
			}
			
			//登录客户帐号
			$rsMember = $DB->getDRow("select * from t_member where id = '{$rsMemberMtLogin['member_id']}'");
			if(!$rsMember){
				$result = array('status' => 0, 'info' => '未找到相应的客户资料');
				return $result;
			}
		}else{
			member_login_log($account,array('id'=>0,'server_id'=>$server_id),0);
			
			$result = array('status' => 0, 'info' => 'MT帐号或密码错误(1)');
			return $result;
		}
	}else{
		$rsMember = $DB->getDRow("select * from t_member where `{$logintype}` = '{$account}' and server_id = '{$server_id}' order by `status` desc");
		if(!$rsMember){
			member_login_log($account,array('id'=>0,'server_id'=>$server_id),0);
			
			$result = array('status' => 0, 'info' => 'MT帐号或密码错误(2)');
			return $result;
		}else{
			//密码错误，增加错误次数
			if($rsMember['password'] != encrypt($password)){
				$errorusd = check_login_used($rsMember['member_id']);
				if(is_array($errorusd)){
					$result  = $errorusd;
				}else{
					$result = array('status' => 0, 'info' => 'MT帐号或密码错误(3)', 'login_error' => $errorusd);
				}
				
				member_login_log($account,array('id'=>0,'server_id'=>$server_id),0);
				
				return $result;
			}

			//密码正确了，判断是否还处于锁定状态
			if ((time() - $rsMember['login_error_time']) <= C('LOGIN_ERR_STOP_SECONDS') && $login_error_used >= C('LOGIN_ERR_MAX_STOP')) {
				$result = array('status' => -2, 'info' => '账号已被锁定，请10分钟后再试！');
				return $result;
			}
		}
	}
	

	if ($rsMember['status'] == -1) {
		$result = array('status' => 0, 'info' => '账号已删除！');
	} else if ($rsMember['status'] == 0) { 
		$result = array('status' => 0, 'info' => '账号未激活！');
	} else if ($rsMember['status'] == 2) {
		$result = array('status' => 0, 'info' => '您的账号还没有激活！');
	} else if ($rsMember['banned_login'] == 1){
		$result = array('status' => 0, 'info' => '你的账号已禁止登陆，请联系管理员！');
	}else {
		//清除登录错误状态
		$DB->query("update t_member set login_error_used = 0,login_error_time = 0,last_login_time = '" . time() . "' where id = '{$rsMember['id']}'");

		$cookieArr = array(
			'id'=>$rsMember["id"],
			'password'=>$rsMember["password"]
		);
		FSetCookieArr('admin',$cookieArr);
		
		member_login_log($account,$rsMember,1);
		
		$result = array('status' => 1, 'info' => '登录成功！', 'data' => $rsMember);
	}
	return $result;
}

function member_login_log($account,$rsMember,$status){
	global $DB;
	
	$log_ar = array();
	$log_ar['login'] = $account;
	$log_ar['member_id'] = $rsMember["id"];
	$log_ar['server_id'] = $rsMember["server_id"];
	$ip = get_client_ip();
	$log_ar['login_ip'] = $ip;
	//$log_ar['login_area'] = GetIpLookup($ip)['province'];
	$log_ar['login_area'] = '';
	$log_ar['create_time'] = time();
	$log_ar['status'] = 1;
	$DB->insert("t_member_login",$log_ar);
}

function check_login_used($member_id) {
	global $DB;
	
	$rsMember = $DB->getDRow("select login_error_used,login_error_time from t_member where id = '{$member_id}'");
	if ((time() - $rsMember['login_error_time']) > C('LOGIN_ERR_STOP_SECONDS')) {
		$login_error_used = 1;
	}else{
		$login_error_used = $rsMember['login_error_used'] + 1;
	}
	
	$DB->query("update t_member set login_error_used = " . $login_error_used . ",login_error_time = '" . time() . "' where id = '{$member_id}'");
	
	if ($login_error_used >= C('LOGIN_ERR_MAX_STOP')) {
		return array('status' => -2, 'info' => '账号已被锁定，请10分钟后再试！');
	}

	return $login_error_used;
}

/**
 * 检验MT4账号密码方式
 */
function api_gold_checkMt4login($login, $password, $mtserver) {
	global $DB;

	$server = $DB->getDRow("select * from t_mt4_server where status = 1 and id = '{$mtserver}'");
	
	$mt4api = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != '0') {
		return $retarry['info'];
	}

	$info = $mt4api->checkpassword($login, $password);
	$info = $info['ret'];
	if (!$info) {
		return "success";
	} else {
		return "MT帐号或密码错误(4)";
	}
}

// 输出接口数据
function api_put($data = '', $key = '') {
	if ('' == $data){
		exit();
	}

	$content = think_encrypt(serialize($data), $key ? $key : C('THINK_ENCRYPT_KEY'));
	exit($content . md5($content));
}

// 获取接口数据
function api_get($data, $key = '') {
	if ('' == $data)
		return '';
	$verify = substr($data, -32);
	$content = substr(trim($data), 0, -32);
	if ($verify == md5($content)) {			
		return unserialize(think_decrypt($content, $key ? $key : C('THINK_ENCRYPT_KEY')));
	}
	return false;
}

// 系统加密方法
function think_encrypt($data, $key, $expire = 0) {
    $key  = md5($key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }

        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }

    return str_replace('=', '', base64_encode($str));
}

// 系统解密方法
function think_decrypt($data, $key) {
    $key    = md5($key);
    $x      = 0;
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $len  = strlen($data);
    $l    = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }

        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}