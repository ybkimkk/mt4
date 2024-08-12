<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

/*
$str = 'a:16:{s:11:&quot;chineseName&quot;;s:9:&quot;中文名&quot;;s:5:&quot;phone&quot;;s:6:&quot;手机&quot;;s:4:&quot;city&quot;;s:6:&quot;城市&quot;;s:8:&quot;bankName&quot;;s:12:&quot;开户支行&quot;;s:11:&quot;accountName&quot;;s:18:&quot;银行开户姓名&quot;;s:10:&quot;accountNum&quot;;s:12:&quot;银行账号&quot;;s:8:&quot;bankCard&quot;;s:15:&quot;银行卡照片&quot;;s:8:&quot;realname&quot;;s:12:&quot;真实姓名&quot;;s:8:&quot;identity&quot;;s:12:&quot;证件号码&quot;;s:16:&quot;identityOpposite&quot;;s:18:&quot;身份证正面照&quot;;s:12:&quot;identityBack&quot;;s:18:&quot;身份证反面照&quot;;s:12:&quot;addressProof&quot;;s:12:&quot;地址证明&quot;;s:21:&quot;register_invent_codes&quot;;s:15:&quot;注册邀请码&quot;;s:8:&quot;nickname&quot;;s:9:&quot;英文名&quot;;s:9:&quot;swiftCode&quot;;s:18:&quot;银行国际代码&quot;;s:12:&quot;account_type&quot;;s:12:&quot;账户类型&quot;;}';
$str = html_entity_decode($str);
$str = unserialize($str);
print_r($str);
exit;
*/
/*
Array
(
    [chineseName] => 中文名
    [phone] => 手机
    [city] => 城市
    [bankName] => 开户支行
    [accountName] => 银行开户姓名
    [accountNum] => 银行账号
    [bankCard] => 银行卡照片
    [realname] => 真实姓名
    [identity] => 证件号码
    [identityOpposite] => 身份证正面照
    [identityBack] => 身份证反面照
    [addressProof] => 地址证明
    [register_invent_codes] => 注册邀请码
    [nickname] => 英文名
    [swiftCode] => 银行国际代码
    [account_type] => 账户类型
)
*/
/*
$arr = array('ud'=>'按上下级的员工级别比较','max'=>'单个订单计算过程中，与下层中相关的最大员工级别比较');
$str = serialize($arr);
$str = htmlentities($str);
echo $str;exit;
*/

if($Clause == 'saveitem'){
	admin_action_log();
	
		//处理数组
		foreach($_POST as $key=>$val){
			if(is_array($val)){
				$_POST[$key] = implode(',',array_filter($val));
			}
		}
		
		//限制
		if(intval($_POST['MAX_LEVEL']) < 50){
			$_POST['MAX_LEVEL'] = 50;
		}
		
        $serverid = $DRAdmin['server_id'];
        $mid = $DRAdmin['id'];
		
        //查询自己有哪些mt4配置的管理权限
        //$mt4group = explode(',', $DB->getField("select query_server from `t_member` where id = '{$mid}'"));
		
        //公共配置
        $config = $DB->getDTable("select * from `t_config` where `status` = 1","name");

        //私有配置
        $configvalue = $DB->getField2Arr("select configname,configvalue from `t_config_server` where `server_id` = '{$serverid}'");

		//开启自动获取汇率
        if (NULL !== $_POST['OPEN_WWW_FOREX']) {
            //S("WWW_FOREX" . $serverid, null);
        }
		
		//跟单返佣模式
        if (NULL !== $_POST['FOLLOW_COMM_TYPE']) {
            $comm_type = $DB->getField("select configvalue from `t_config_server` where `configname` = 'FOLLOW_COMM_TYPE' and `server_id` = '{$serverid}'");
            if ($comm_type != $_POST['FOLLOW_COMM_TYPE']) {
                //对所有的跟单配置进行更新
				$DB->query("update `mt4svr_config` set UPDATE_TIME = '" . time() . "' where `STATUS` <> -1");
            }
        }
		
        //设置为周期模式的时候所有的订单全部标记为已经处理
        if (NULL !== $_POST['FOLLOW_COMM_TYPE'] && $_POST['FOLLOW_COMM_TYPE'] > 0) {
            $commtype = $DB->getField("select configvalue from `t_config_server` where `configname` = 'FOLLOW_COMM_TYPE' and `server_id` = '{$serverid}'");
			
            //从及时模式切换到周期模式 将以前的订单处理
            if (!$commtype) {
				$DB->query("update `mt4svr_trades` set QUEE_ORDER = 1 where QUEE_ORDER = '' or QUEE_ORDER is null");
            }
        }
		
		//跟单价格模式
        if (NULL !== $_POST['Follow_PriceMode']) {
            $_POST['Follow.PriceMode'] = $_POST['Follow_PriceMode'];
        }
		
		//系统模版
        if (NULL !== $_POST['APP_TEMP_SRC'] && $_POST['APP_TEMP_SRC'] != $configvalue['APP_TEMP_SRC']) {
            //deldir("./Runtime/");
        }

        foreach ($_POST as $key => $value) {
            //如果是私有配置则去查询
            if ($config[$key]['sid'] == 1 && $serverid > 0) {
                if (NULL === $configvalue[$key]) {
					$DB->query("insert into `t_config_server` set bgColor='" . $config[$key]['bgColor'] . "',configname = '{$key}',configvalue = '{$value}',server_id = '{$serverid}',create_time = '" . time() . "',update_time = '" . time() . "'");
                } else {
					$DB->query("update `t_config_server` set bgColor='" . $config[$key]['bgColor'] . "',configvalue = '{$value}',update_time = '" . time() . "' where configname = '{$key}' and server_id = '{$serverid}'");
                }
            } else {
				$DB->query("update `t_config` set `value` = '{$value}' where `name` = '{$key}'");
            }
        }
        //D("Config")->getCacheList(false);
		
	FCreateErrorPage(array(
		'title'=>L("提示"),
		'content'=>L("保存成功"),
		'btnStr'=>L('返回'),
		'url'=>FPrevUrl(),
		'isSuccess'=>1,
		'autoRedirectTime'=>2000,
	));
}else if($Clause == 'cs_email'){
	$email = FPostStr('email');

	$smtpConf['EMAIL_HOST'] = C('EMAIL_HOST');
	$smtpConf['EMAIL_PORT'] = C('EMAIL_PORT');
	$smtpConf['EMAIL'] = C('EMAIL');
	$smtpConf['EMAIL_PASS'] = C('EMAIL_PASS');
	$smtpConf['SUPPORT_SSL'] = C('SUPPORT_SSL');
	$smtpConf['EMAIL_HONER'] = C('EMAIL_HONER');
	$smtpConf['server_id'] = $DRAdmin['server_id'];

	$title = L('测试');
	$content = L('这只是一封测试邮件');

	$res = Mail_send($email, $title, $content, $html = true, null, $smtpConf);
	if (true === $res) {
		$output = array('code' => 1);
	} else {
		$output = array('code' => 0,'msg'=>$res);
	}
	echo json_encode($output);
	exit;
}

if(!in_array($Clause,array('main'))){
	$Clause = 'main';
}
require_once('config_set/' . $Clause . '.php');

