<?php
/*
2015-10-28 更新


*/

$logged_admin_id = $_COOKIE['admin']['id'] . '';
$logged_admin_username = $_COOKIE['admin']['username'] . '';
$logged_admin_password = $_COOKIE['admin']['password'] . '';
//验证是否登录
if($logged_admin_id == '' || $logged_admin_password == ''){
	echo '<script type="text/javascript">';
	echo 'UploadedCall(' , $CKEditorFuncNum , ',"","抱歉，请登录后再使用上传功能！");';
	echo '</script>';
	exit();
}