<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'smsinfo'){
	$content = $DB->getField("select `content` from `t_msg_log` where id = '{$Id}'");
	echo $content;
	exit;
}else if($Clause == 'emailinfo'){
	$content = $DB->getField("select `contents` from `t_mail_log` where id = '{$Id}'");
	echo $content;
	exit;
}else if($Clause == 'sendemail'){
	//
	exit;
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('email_log/' . $Clause . '.php');

