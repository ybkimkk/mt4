<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');

$agent = $_SERVER["HTTP_USER_AGENT"];
C("LAYOUT_NAME", 'layout');
foreach ($_GET as $key => $value) {
	$_GET[str_replace(';', '', $key, $key)] = $value;
}
$email = FGetStr('email');
$user = FGetStr('user');
$mtserver = FGetInt('mtserver');
$code = FGetStr('code');
$id = FGetInt('id');
$data = $DB->getDRow("select * from t_member where id = '{$id}'");
//M('member')->field('id,nickname,email,phone,city,address,parent_id,update_time,leverage,chineseName,server_id,invent_code,register_group')->
//where(array('id' => $id))->find();

if (!$data) {
	FJS_AT(L('账号不存在'),'login.php');
}
if ($data['status'] == 1) {
	FJS_AT(L('账号已激活，请使用账号登陆'),'login.php');
}
if ($data['status'] == -1) {
	FJS_AT(L('账号不存在'),'login.php');
}

//新的标准按UUID来验证
if ($code) {
	if($data['active_code'] != $code){
		FJS_AT(L('该链接已激活或链接错误，请使用账号登陆'),'login.php');
	}
} else {
	FJS_AT(L('获取验证参数失败！由于部分邮箱服务商会将验证数据截断导致出现此错误，如果出现此提示请将邮件内容中的链接手动复制到浏览器中访问激活，或者联系管理员手动激活'),'login.php');
}

$res = $DB->update("t_member",array('status' => 1, 'update_time' => time()),"where id = '{$id}'");
if ($res) {
	$apiinfo = reg_open_mt4login($data, $data['server_id']);
	if ($apiinfo['error'] == 0) {
		FJS_AT($apiinfo['info'] ? $apiinfo['info'] : L('验证成功'),'login.php');
	} else {
		FJS_AT($apiinfo['info'] ? $apiinfo['info'] : L('验证失败'),'login.php');
	}
} else {
	FJS_AT(L('验证失败，请刷新页面重试'),'login.php');
}


