<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

FCreateErrorPage(array(
	'title'=>L("提示"),
	'content'=>L("没有操作权限"),
	'btnStr'=>L('返回'),
	'url'=>CC_ADMIN_ROOT_FOLDER,
	'isSuccess'=>0,
	'autoRedirectTime'=>0,
));