<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/cconfig/config_db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/mysqli.class.php');

$DB = new CMySQLi();
$DB->connnect($DBSettings['dbHost'],$DBSettings['dbUser'],$DBSettings['dbPwd'],$DBSettings['dbName'],$DBSettings['dbPort']);

//--------------------------------------------------------------

/*$WebConfig_ = $DB->getDTable("select * from `t_web_config`");
$WebConfig = array();
foreach($WebConfig_ as $key=>$val){
	$WebConfig[$val['f_key']] = $val['f_value'];
}
if($WebConfig['f_webClose']){
	if(stripos($_SERVER['REQUEST_URI'],CC_ADMIN_ROOT_FOLDER) === false){
		if(stripos($_SERVER['REQUEST_URI'],'/web-close.php') === false){
			FRedirect('/web/web-close.php');
		}
	}
}

$MemberLevelConfig = $DB->getDTable('select * from `t_member_level` order by f_level asc','f_level');*/

$GuestbookTypeArr = array(
	5=>'其他事务',
	1=>'开户咨询',
	2=>'代理咨询',
	3=>'交易咨询',
	4=>'出入金咨询',
);

$mtCConfig = array(
    'MTCODE4' => array(
        0 => 'Successful completion.',
        1 => 'Successful completion with no information returned.',
        2 => 'Common error.',
        3 => 'Invalid information.',
        60 => 'Technical errors on the server.',
        5 => 'Old terminal version.',
        6 => 'No connection.',
        7 => 'Not enough permissions to perform the operation.',
        8 => 'Too frequent requests.',
        9 => 'Operation cannot be completed.',
        10 => 'Key generation is required.',
        11 => 'Connection using extended authentication.',
        64 => 'The account is disabled.',
        65 => 'Invalid account information.',
        66 => 'No public key.',
        128 => 'Request timed out.',
        129 => 'Invalid price.',
        130 => 'Invalid stop-levels.',
        131 => 'Invalid volume.',
        132 => 'Market is closed.',
        133 => 'Trade is disabled.',
        134 => 'Not enough money.',
        135 => 'Price has changed.',
        136 => 'No price.',
        137 => 'The server is busy.',
        138 => 'Requote in response to the request.',
        139 => 'Order cannot be modified, because it is being processed by another dealer.',
        140 => 'Only Buy orders are allowed.',
        141 => 'Too many trade requests.',
        142 => 'Request accepted by the sever and added to the queue of requests.',
        143 => 'Request accepted by the dealer.',
        144 => 'Request canceled by the client.',
        145 => 'Order cannot be modified because it is too close to market.',
        146 => 'The trade context is busy',
        147 => 'Specified expiration time is over.',
        148 => 'Too many orders.',
        149 => 'Hedging is prohibited.',
        150 => 'The request cannot be fulfilled due to the FIFO rule.',
        255 => 'Network Problem.'
    ), 'MTCODE5' => array(
        0 => 'Successful completion.',
        1 => 'Successful completion with no information returned.',
        2 => 'Common error.',
        3 => 'Invalid parameters.',
        4 => 'Invalid information.',
        5 => 'Hard disk error.',
        6 => 'Memory error.',
        7 => 'Network error.',
        8 => 'Not enough permissions to perform the operation.',
        9 => 'Timeout expired.',
        10 => 'No connection.',
        11 => 'Service is not available.',
        12 => 'Too frequent requests.',
        13 => 'Not found.',
        14 => 'Partial error.',
        15 => 'Server shutdown in progress.',
        16 => 'The operation has been canceled.',
        17 => 'Duplicate information.',
        1000 => 'Invalid type of the terminal.',
        1001 => 'Invalid account.',
        1002 => 'The account is disabled.',
        1003 => 'Extended authorization required.',
        1004 => 'Certificate required.',
        1005 => 'Invalid certificate.',
        1006 => 'The certificate is not confirmed.',
        1007 => 'An attempt to connect to a server, which is not an access server.',
        1008 => 'Server is not authenticated.',
        1009 => 'Only update is available.',
        1010 => 'Old client version.',
        1011 => "An appropriate manager configuration hasn't been created for the manager account.",
        1012 => 'IP-address is not valid for the manager.',
        1013 => 'The group is not initialized (you must restart the server).',
        1014 => 'Generation of certificates is disabled.',
        1015 => 'Invalid ID or the server is disabled (the server ID should be checked).',
        1016 => 'Invalid address (the server IP-address should be checked).',
        1017 => 'Wrong type of server (server ID and type should be checked).',
        1018 => 'The server is busy.',
        1019 => 'Invalid server certificate.',
        1020 => 'Unknown account.',
        1021 => 'Outdated server version.',
        1022 => 'he server cannot be connected because of the license restrictions.',
        1023 => 'Connections of mobile devices are not allowed in the license.',
        1024 => 'This type of connection is not permitted for manager.',
        1025 => 'Creation of demo accounts is disabled.',
        1026 => 'Master password must be changed.',
        2000 => 'Deleting the last administrator configuration.',
        2001 => 'The last group of administrators cannot be deleted.',
        2003 => 'The group contains accounts or trade operations.',
        2004 => 'Invalid range of accounts or trade operations.',
        2005 => 'The manager account does not belong to the manager group.',
        2006 => 'Built-in protected configuration.',
        2007 => 'Duplicate configuration.',
        2008 => 'Reached limit on the number of configurations.',
        2009 => 'Incorrect network configuration.',
        2010 => 'A dealer with the same ID (account number) already exists.',
        2011 => 'Connection address already exists.',
        2012 => 'An attempt to delete a working trade server.',
        2013 => 'Gateway with that name already exists.',
        3001 => 'The last administrator account has been deleted.',
        3002 => 'The range of logins has been exhausted.',
        3003 => 'The login is reserved on another server.',
        3004 => 'The account already exists.',
        3005 => 'An attempt of self-deletion.',
        3006 => 'Incorrect account password.',
        3007 => 'Reached the limit on the number of users.',
        3008 => 'The accounts has open positions.',
        3009 => 'An attempt to move an account to another server.',
        3010 => 'An attempt to move an accounts to a group with a different deposit currency.',
        3011 => 'Failed to import account balance.',
        3012 => 'The account is imported with the wrong group.',
        3013 => 'The account already exists.',
        4001 => 'Reached the limit on the number of orders or deals.',
        4002 => 'The order already exists.',
        4003 => 'The range of orders has been exhausted.',
        4004 => 'The range of deals has been exhausted.',
        4005 => 'Reached the limit on the amount of money.',
        5001 => 'Database snapshot error.',
        5002 => 'The method is not supported for this report.',
        5003 => 'No information for the report.',
        5004 => 'Wrong template.',
        5005 => 'The end of the template.',
        5006 => 'Invalid row size.',
        5007 => 'Reached the limit of the number of duplicate tags.',
        5008 => 'Reached the limit of the report size.',
        6001 => 'Symbol not found, try to restart the history server.',
        10001 => 'Request is on the way.',
        10002 => 'Request accepted.',
        10003 => 'Request processed.',
        10004 => 'Requote in response to the request.',
        10005 => 'Prices in response to the request.',
        10006 => 'Request rejected.',
        10007 => 'Request canceled.',
        10008 => 'An order placed as a result of the request.',
        10009 => 'Request fulfilled.',
        10010 => 'Request partially fulfilled.',
        10011 => 'Common error of request.',
        10012 => 'Request timed out.',
        10013 => 'Invalid request.',
        10014 => 'Invalid volume.',
        10015 => 'Invalid price.',
        10016 => 'Wrong stop levels or price.',
        10017 => 'Trade is disabled.',
        10018 => 'Market is closed.',
        10019 => 'Not enough money.',
        10020 => 'Price has changed.',
        10021 => 'No price.',
        10022 => 'Invalid order expiration.',
        10023 => 'Order has been changed..',
        10024 => 'Too many trade requests.',
        10025 => 'Request does not contain changes.',
        10024 => 'Too many trade requests.',
        10025 => 'Request does not contain changes.',
        10026 => 'Autotrading disabled on the server.',
        10027 => 'Autotrading disabled on the client side.',
        10028 => 'Request blocked by the dealer.',
        10029 => 'Modification failed due to order or position being close to market.',
        10030 => 'Fill mode is not supported.',
        10031 => 'No connection.',
        10032 => 'Allowed only for real accounts.',
        10033 => 'Reached the limit on the number of orders.',
        10034 => 'Reached the volume limit.',
        10035 => 'Invalid or prohibited order type.',
        10036 => 'Position is already closed. For example, this error appears when attempting to modify the stop levels of an already closed position.',
        10037 => 'Used for internal purposes.',
        10038 => 'Volume to be closed exceeds the current volume of the position.',
        10039 => 'Order to close the position already exists.',
        10040 => 'The number of open positions simultaneously present on an account can be limited by the settings of a group',
        10041 => 'Request rejected, order canceled. This code is returned when the action IMTConRoute::ACTION_CANCEL_ORDER in a routing rule is applied.',
        10042 => 'The request is rejected, because the "Only long positions are allowed" rule is set for the symbol (IMTConSymbol::TRADE_LONGONLY).',
        10043 => 'The request is rejected, because the "Only short positions are allowed" rule is set for the symbol (IMTConSymbol::TRADE_SHORTONLY).',
        10044 => 'The request is rejected, because the "Only position closing is allowed" rule is set for the symbol (IMTConSymbol::TRADE_CLOSEONLY).',
        11001 => 'Request has been partially filled, the remainder has been canceled.',
        11002 => 'The request has been requoted and returned to the queue with new prices.',
        12000 => 'Not yet implemented.',
        12001 => 'The operation should be performed on the main trading server..',
        12002 => 'The command is not supported by this server..',
        12003 => 'The operation has been canceled due to a possible deadlock.',
        12004 => 'Working with a blocked object.'
    ),'DISABLE_AUTH'=>
    array(
        "Role"=>'saveAccess,saveUser,insert,saveGroup,delete,update',
      //  "Member"=>'update,insert,addMember,downmember,saveAlloce,can_login,saveLogininfo_disable,saveLogininfo_group,update_mark,password_again,visituser,resetAccoutPwd,editAccountPwd,saveLogininfo_leverage,unlock,delmember,addmember,refusemt4,resendMt4mail'
      //  . ',review,queryParentMemberByName,savemtlogin,saveapply,getmtlogin,saveaccount,changepass,deleteaccount,saveRole,check,resume,forbid,delete,update,insert,resendRegmail',
        'Node'=>'check,resume,forbid,delete,update,insert',
        'Mail'=>'sendAllMessage,deleteGroupMessage,sendGroupMessage,editGroupMessage,sendgroupemail,resume,forbid,delete,update,insert,sendallmail',
        'Config'=>'cs_email,send_email,saveitem,init_sync,add_sync,setting,saveConfig,saveSort,delete,resume,forbid,update,insert,listvisit',
        'Menu'=>'forbid,delete,resume,update,insert',
        'Article'=>'insert,update,delete',
        'Commission'=>'down_withdraw,down_view_no_details,down_setting,dowithaccount,down_view_details',
        'Commission'=>'saveSettingStatus,deleteSetting,saveSetting,dowithdraw',
        'Deposit'=>'refusemt4,update_bank,deleteBank,viewcheck_bank,add_bankcode,managerDepoist,managerDepoist,down_waitin,down_waitout,visitinmoney,visitoutmoney,resetoutnoney,resetinmoney,dopaid,repayout,getpaycode,delpaid',
        'Report'=>'down_system,down_history',
        'Template'=>'*',
        'Credit'=>'delete,back,forbid,pub,save,add,edit',
        'Earnest'=>'editinfo,setsymbolfee,openEarnest,uesmoeny,closeearnest,setearnest,addusermoney',
        'Datas'=>'underinfo,lookup',
        'Follow'=>'saveSignleSale,addFollowConfig,returnconfig,visitconfig,saveFollowGroup,saveLoginBase,deletegroup,editUserGroup,addUserGroup,setSpread,deleteFollowSale,saveFollowSale,setHiddenUser,setHiddenUser,setHiddenUser,closefollow,editfollow,addfollow',
        'Pay'=>'updatebankStatus,updatebank,addbank,save,edit',
        'Symbol'=>'savestusymbol,deleteSymbolType,saveSymbolType,syncSymbol',
        'Mt4Server'=>'openmt,deleteMT4Server,saveMt4Server',
        'Groups'=>'upremark,saveDefaultGroup,saveType,syncGroups', 
    )
);

$webCConfig = array(
	'THINK_ENCRYPT_KEY'=>'topthink',
	
	'LOGIN_ERR_MAX_STOP'=>5,
	'LOGIN_ERR_STOP_SECONDS'=>600,
	
	'APP_NAME' => 'gold', 
	'RIGHT_SOFT_URL_SITE' => 'https://download.mql5.com/cdn/web/atom.markets.ltd/mt4/fxatommarkets4setup.exe', 
	'RIGHT_SOFT_NAME' => 'Download MT4', 
	'OPEN_ACCOUNT_AGREEMENT'=>'http://fxd', 
	'APP_DEBUG' => 0, 
	'APP_GROUP_LIST' => 'Admin,Home,Api', 
	'DEFAULT_GROUP' => 'Admin', 
	'UPLOAD_PATH' => './Uploads/gold/', 
	'URL_HTML_SUFFIX' => '.html', 
	'SHOW_PAGE_TRACE'=>0, 
	'DB_FIELDS_CACHE' => false, 
	'DB_PREFIX' => 't_', 
	'openeamil'=>1, 
	'APP_AUTOLOAD_PATH' => '@.TagLib', 
	'PASSWORD_KEY'=>'111',
);

$adminCConfig = array(
    //布局配置
    'LAYOUT_ON' => true,
    'LAYOUT_NAME' => 'layout',
    //系统配置
    'TOKEN_ON' => false, //表单令牌验证
    'SHOW_PAGE_TRACE' => false, //调试配置
    'URL_HTML_SUFFIX' => '', //url后缀
    'APP_AUTOLOAD_PATH' => '@.TagLib', //自动加载
	'APP_DEBUG' => '1', //调试模式
   // 'CONFIG_GROUP'=>'用户设置,',
   
    'LIST_ROWS' => 15, //列表显示条少
    'VERIFY_CODE_LENGTH' => 4, //验证码长度
    //用户权限信息
    'ADMIN_AUTH_KEY' => '1',
    'OPEN_TRUE_NUMBER'=>'2',
	'OPEN_SIMU_NUMBER'=>'2',
    'FOLLOE_POINT_LOGIN'=>'',//信号源账号 针对不使用我们跟单系统得设置
    'SYSTEM_POINT_LOGIN'=>'',// 系统收费账号 针对不使用我们跟单系统得设置
    'SUBADMIN_AUTH_KEY' => '2,372',
    'USER_AUTH_TYPE' => '1', //认证类型 0:关闭认证,1:缓存认证,2:实时认证
    'OPEN_NUMBER' => '1',
    'USER_AUTH_KEY' => 'user', //后台统一Key
    'USER_AUTH_GATEWAY' => 'Public/login', //后台登录网关
    'SAVE_ACCESS_NAME' => 'gold_admin', //权限保存唯一名称
    'NOT_AUTH_MODULE' => array('Public' => '*'), //不需要验证的操作
    //模板配置
    'TMPL_ACTION_ERROR' => 'Public:success', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'Public:success', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE' => 'Tpl/think_exception.tpl', // 异常页面的模板文件
  	'LANG_SWITCH_ON' => true,
	'DEFAULT_LANG' => 'zh-vn', // 默认语言
	'LANG_AUTO_DETECT' => true, // 自动侦测语言
	'LANG_LIST'=>'en-us,zh-cn,zh-vn',//必须写可允许的语言列表
	'VAR_LANGUAGE'     => 'l', // 默认语言切换变量  
);

//读取数据库中的配置
//查询默认的所有配置
$configArr = $DB->getDTable("select * from `t_config` where `status` = 1 order by sort desc");

$czUConfig = array();
foreach ($configArr as $k => $v) {
	$czUConfig[$v['name']] = $v['value'];
}

$czCConfig = array(
	'cz_REGISTER_URL'=>(FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/admin/reg.php',
	'f_editor'=>'ckeditor',

	'APP_WEB_TITLE'=>$czUConfig['APP_WEB_NAME'],
	'f_title'=>$czUConfig['APP_WEB_NAME'],
	'f_description'=>$czUConfig['APP_WEB_NAME'],
);
//直客是否允许推荐下家，这里只涉及推广码在页面上的显示
if($_SERVER['HTTP_HOST'] == CC_CRM_ITEM_DOMAIN_office_etomarkets_net){
	$czCConfig['f_directCanRecom'] = 0;
}else{
	$czCConfig['f_directCanRecom'] = 1;
}

//合并页面上的配置
$webConfig = array_merge($mtCConfig,$webCConfig,$adminCConfig,$czCConfig,$czUConfig);


//--------------------------------------------------------------

require_once($_SERVER['DOCUMENT_ROOT'] . '/cconfig/config_item.php');