<?php
/*
2015-10-27 更新


*/

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

//--------权限判断 开始
require_once(CC_CZPHPHELPER_ABSPATH . 'utilcls/encrypt.class.php');

$chkPermissions = $_GET['chkPermissions'];
if(!$chkPermissions){
	$chkPermissions = $_POST['chkPermissions'];
}
if(!$chkPermissions){
	echo '权限不足！';
	exit;
}

$c_encrypt = new CEncrypt();
$decryptStr = $c_encrypt->uc_authcode_decode($chkPermissions,CC_ENCRYPT_KEY);

$arr = explode('|',$decryptStr);
if(count($arr) != 2){
	echo '权限错误！';
	exit;
}
if(strtolower($_SERVER['SERVER_NAME']) != strtolower($arr[0])){
	echo '权限之域名错误：非法域名来源！';
	exit;
}
$ssecond = FDateDiffSecond($arr[1],time());
$shour = $ssecond / 3600;
if($shour > 24){
	echo '权限之时间错误：超时了！';
	exit;
}
//--------权限判断 结束

require_once(CC_CZPHPHELPER_ABSPATH . 'utilcls/upload.class.php');

$uploadFolder = "../../../../upload/";
if(!is_dir($uploadFolder))
{
	mkdir($uploadFolder);
}
$uploadFolder .= "ckeditor/";
if(!is_dir($uploadFolder))
{
	mkdir($uploadFolder);
}

$uploadConfig = array(
	'maxSize'=>(1024 * 1024 * 10),//单位：byte；乘1024之后单位是kb；再乘1024之后单位是mb；10M
	'uploadFolder'=>$uploadFolder,
);
$upload = new CUpload($uploadConfig);
$saveFileName = $upload->uploadOne('Filedata');

echo $saveFileName;