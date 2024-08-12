<?php
header('Content-type: text/html;charset=utf-8');
isset($_REQUEST['GLOBALS']) && exit('Access Error');
isset($_FILES['GLOBALS']) && exit('Access Error');
if(stripos($_SERVER['HTTP_HOST'],'127.0.0.1') !== false){
	define('CC_WEB_DEBUG', 1);
}else{
	define('CC_WEB_DEBUG', 0);
}
define('CC_MYSQL_DEBUG', 1);
define('CC_STRUNCHK',0);
define('CC_MAPIKEY','K4CCgb1eJ7kIG7j');
define('CC_MAPIVAL','UeMNYW9LDEJtg23QEiYgod9Yx9ic53fwXA3dQYVWszUOm0rq5k2Jwn9L+d+hqRJ04XmEXIMlUP4k9Pn2Q+V+cu1Mal8TqfGSAtTGta79HA==');
if(CC_WEB_DEBUG){error_reporting(E_ALL&~E_NOTICE);@ini_set('display_errors','On');}else{error_reporting(E_ERROR|E_PARSE);@ini_set('display_errors','Off');}
@set_time_limit(0);
@date_default_timezone_set('Asia/Shanghai');
@ini_set('Asia/Shanghai');
session_cache_limiter('private,must-revalidate');
@ini_set('session.auto_start',0);
ob_end_clean();function_exists('ob_gzhandler')?ob_start('ob_gzhandler'):ob_start();

define('CC_ADMIN_ROOT_FOLDER','/admin/');

define('CC_CRM_ITEM_DOMAIN_office_etomarkets_net', 'office.etomarkets.net');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/function_utility.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/function_farther.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/function_api.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/cconfig/function_item.php');

$Clause = strtolower(FGetStr('clause'));
$Id = FGetInt('id');