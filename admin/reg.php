<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');

if($Clause == 'checkemail'){
	header('Content-type:text/json');
	
	$email = FGetStr('n_email');
	$mtserver = FGetStr('mtserver');
	$data = $DB->getDRow("select * from t_member where `email` = '{$email}' and server_id = '{$mtserver}' and status in (0,1)");
	if ($data) {
		echo json_encode(L('邮箱已注册'));
	} else {
		echo json_encode(true);
	}
	exit;
}else if($Clause == 'checkphone'){
	header('Content-type:text/json');
	
	$name = FGetStr('phone');
	$mtserver = FGetStr('mtserver');
	$data = $DB->getDRow("select * from t_member where `phone` = '{$name}' and server_id = '{$mtserver}' and status in (0,1)");
	if ($data) {
		echo json_encode(L('手机号已被使用'));
	} else {
		echo json_encode(true);
	}
	exit;
}else if($Clause == 'savereg'){
	if(stripos(strtolower(FPrevUrl()),$_SERVER['HTTP_HOST']) === false){
		this_error(L("验证码错误"));
	}
	
	$ip = get_client_ip();
	$rsChk = $DB->getDRow("select * from t_member where `register_ip` = '{$ip}' and create_time >= '" . (time() - 30 * 60) . "'");
	if($rsChk){
		this_error('30 ' . L("分钟内您只能注册1个用户"));
	}	
	
	$checkphone = FPostInt('activetype');

	$data = array(
				'fromuser'=>FPostInt('fromuser'),
				'IA'=>FPostStr('IA'),//指定分组，它的值 = md5(分组名称)
				'userType'=>FPostStr('usertype'),
				'activetype'=>FPostStr('activetype'),
				'mtserver'=>FPostStr('mtserver'),
				'n_email'=>FPostStr('n_email'),
				'phone'=>FPostStr('phone'),
				'phone_code_verify'=>FPostStr('phone_code_verify'),
				'phone_code'=>FPostStr('phone_code'),
				'email'=>FPostStr('email'),
				'nickname'=>FPostStr('nickname'),
				'password'=>FPostStr('password'),
				'confirm'=>FPostStr('confirm'),
				'chineseName'=>FPostStr('chineseName'),
				'account_type'=>FPostStr('account_type'),
				'leverage'=>FPostStr('leverage'),
				'nationality'=>FPostStr('nationality'),
				'livingState'=>FPostStr('livingState'),
				'province'=>FPostStr('province'),
				'city'=>FPostStr('city'),
				'bankName'=>FPostStr('bankName'),
				'accountName'=>FPostStr('accountName'),
				'accountNum'=>FPostStr('accountNum'),
				'bankCard'=>FPostStr('bankCard'),
				'swiftCode'=>FPostStr('swiftCode'),
				'realname'=>FPostStr('realname'),
				'identity'=>FPostStr('identity'),
				'identityOpposite'=>FPostStr('identityOpposite'),
				'identityBack'=>FPostStr('identityBack'),
				'addressProof'=>FPostStr('addressProof'),
				'register_invent_codes'=>FPostStr('register_invent_codes'),
				
				'status'=>0,
	);
	
	
	if (strlen($data['phone_code_verify']) <= 0) {
		this_error(L("输入验证码"));
	}
	session_start();
	if(strtolower($data['phone_code_verify']) != strtolower($_SESSION['VCode'])){
		this_error(L("验证码错误"));
	}
	
	if (!$data['email']) {
		$data['email'] = $data['n_email'];
	}
	if (!$data['phone']) {
		$data['phone'] = '0';
	}
	/*$pattern = '/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/';
	if (!preg_match($pattern, $data['password'])) {
		this_error(L("密码必须包含大小写字母和数字"));
	}*/
	$pattern = '/^(?=.*[0-9].*)(?=.*[A-Za-z].*).{6,12}$/';
	if (!preg_match($pattern, $data['password'])) {
		this_error(L("密码必须包含字母和数字"));
	}
	if ($data['password'] != $data['confirm']) {
		this_error(L("两次密码不相同"));
	}

	$must_field = getConfigValue('MUST_REG_FIELD', $data['mtserver']); //注册必填字段
	$must_reg_field = explode(',', $must_field);
	$must_key = unserialize(html_entity_decode($DB->getField("select extra from t_config where `name` = 'MUST_REG_FIELD'")));

	foreach ($must_reg_field as $value) {
		if (!$data[$value]) {
			this_error(L($must_key[$value]) . L("不能为空"));
		}
	}

	if (!$data['email']) {
		this_error('email ' . L("不能为空"));
	}
	if (!$data['nickname'] && !$data['chineseName']) {
		this_error(L("请填写中文名称或者昵称"));
	}
	if (!$data['nickname']) {
		$data['nickname'] = $data['chineseName'];
	}
	if (!$data['chineseName']) {
		$data['chineseName'] = $data['nickname'];
	}

	$data['password'] = md5($data['password']);
	
	//取页面上指定的 mt服务
	$server_id = $data['mtserver'];

	//优先推荐码，推荐码比推荐人更优先
	if ($data['register_invent_codes']) {
		$memberlogin = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$data['register_invent_codes']}' and status = 1");
		if(!$memberlogin){
			this_error(L("请填写有效的推荐码"));
		}

		$data['parent_id'] = $memberlogin['member_id'];
		$data['invent_code'] = $data['register_invent_codes']; //保存邀请码
		
		$server_id = $memberlogin['mtserver'];		

		/*
		$server = $DB->getDRow("select * from t_mt4_server where id = '{$server_id}'");
		if($server['ver'] == 5){
			$mtinfo = $DB->getDRow("select * from " . $server['db_name'] . ".mt5_users where `Login` = '{$data['invent_code']}'");
			$mtinfo['Group'] && $data['register_group'] = $mtinfo['Group'];
		}else{
			$mtinfo = $DB->getDRow("select * from " . $server['db_name'] . ".mt4_users where `LOGIN` = '{$data['invent_code']}'");
			$mtinfo['GROUP'] && $data['register_group'] = $mtinfo['GROUP'];
		}
		*/
	}else if ($data['fromuser'] > 0) {
		//fromuser 为临时使用，后面会置空掉
		
		$parent = $DB->getDRow("select * from t_member where id = '{$data['fromuser']}' and status = 1");
		if($parent){
			//管理员不能推荐？
			//if ($parent['scope'] == 1) {
			//	$data['fromuser'] = 0;
			//}
			
			$data['parent_id'] = $data['fromuser'];
			
			//用推荐人的 mt服务
			$server_id = $parent['server_id'];
		}
	}
	
	
	//读取服务
	$regServer = $DB->getDRow("select * from t_mt4_server where `status` = 1 and `id` = '{$server_id}'");
	if(!$regServer){
		this_error(L("无可用MT服务器开户.请联系管理员确认"));
	}
	$DRAdmin['server_id'] = $serverId;
	
	
	$reg_prov = $DB->getDTable("select * from `t_article` where f_key = 'reg_prov' and server_id = '{$regServer['id']}' and status = 1");
	if($reg_prov){
		if(FPostInt('agree') <= 0){
			this_error(L('请阅读并同意条款'));
		}
	}
	

	$res = $DB->getDRow("select id from t_member where `email` = '{$data['email']}' and status in (0,1) and server_id = '{$server_id}'");
	if ($res) {
		this_error(L("邮箱已经存在"));
	}

	$checkunque = explode(',', getConfigValue('CHECK_FIELD_UNQUE', $server_id));
	$fields = unserialize(html_entity_decode($DB->getField("select extra from t_config where `name` = 'REGEX_FILEDS'")));
	foreach ($checkunque as $key => $value) {
		if(strlen($data[$value])){
			$fieldused = $DB->getDRow("select id from t_member where `{$value}` = '" . $data[$value] . "' and status in (0,1) and server_id = '{$server_id}'");
			if ($fieldused && $value) {
				this_error($fields[$value] . L("已存在"));
			}
		}
	}
	
	/* 检测重复字段 */
	//是否开启短信验证
	$check_phone_code = getConfigValue('CHECK_PHONE_CODE', $server_id);
	//是否开启邮件验证 0否、1是
	if ($checkphone == 1) {
		if ($check_phone_code && time() - S("sendmsgtime") > 900) {
			this_error(L("短信验证码失效"));
		}
		if ($check_phone_code && !$data['phone_code']) {
			this_error(L("请填写正确的手机验证码"));
		}
		if ($check_phone_code && $data['phone_code'] != S('msgphone' . $data['phone'])) {
			this_error(L("请填写正确的手机验证码"));
		}
	}

	$auto_active = getConfigValue('REGISTER_AUTO_ACTIVE', $server_id); //系统开户字段
	//如果采用了短信验证或者配置自动激活 系统自动通过验证
	if ($auto_active == 1 || ($data['email'] && $data['phone_code'])) {
		$data['status'] = 1;
	}
	$data['userType'] = 'direct';
	$data['server_id'] = $server_id;
	$data['active_code'] = uuid();
	$data['nickname'] = $data['nickname']; //去掉空格特殊字符
	$data['register_ip'] = $ip;
	$data['headimg'] = '/assets/images/facedefault.jpeg';
	//$data['register_area'] = GetIpLookup($ip)['province'];
	
	unset($data['fromuser']);
	unset($data['IA']);
	unset($data['activetype']);
	unset($data['mtserver']);
	unset($data['n_email']);
	unset($data['phone_code_verify']);
	unset($data['phone_code']);
	unset($data['confirm']);
	unset($data['register_invent_codes']);
	
	$data['create_time'] = time();
	$data['update_time'] = time();
	
	if ($data['IA']) {
		$recomment_groups = $DB->getField2Arr("select id,`group` from t_groups");
		foreach ($recomment_groups as $key => $value) {
			if (md5($value) == $data['IA']) {
				$data['register_group'] = $value;
				break;
			}
		}
	}
	if(!$data['register_group']){
		$data['register_group'] = get_lang_otherset_val('默认开户MT组');
	}
	//$data['register_group'] = str_replace('\\','\\\\',$data['register_group']);

	$data['f_roleId'] = C('DEAFAULT_ROLE');

	$msg = $DB->insert("t_member",$data);

	if ($msg) {
		if($data['status'] == 0){
			//模拟仓直接激活
			$mail = api('gold://Mail/sendVemail', array($msg));
			if ($mail['status'] == '0') {
				if($_SERVER['HTTP_HOST'] == CC_CRM_ITEM_DOMAIN_office_etomarkets_net){
					this_success(L("提交成功 等待审核 结果将以邮件发送 请注意查收"), 'login.php');
				}else{
					this_success(L("注册成功，系统已发送验证邮件到你的邮箱，请进入邮箱验证"), 'login.php');
				}
			} else {
				if($_SERVER['HTTP_HOST'] == CC_CRM_ITEM_DOMAIN_office_etomarkets_net){
					this_success(L("提交成功 等待审核 结果将以邮件发送 请注意查收"), 'login.php');
				}else{
					this_success(L("注册成功,激活邮件发送未成功，请联系管理员重发"), 'login.php');
				}
			}
		}else{
			$info = $DB->getDRow("select * from t_member where id = '{$msg}'");
			reg_open_mt4login($info, $server_id);
			
			if($_SERVER['HTTP_HOST'] == CC_CRM_ITEM_DOMAIN_office_etomarkets_net){
				this_success(L("提交成功 等待审核 结果将以邮件发送 请注意查收"), 'login.php');
			}else{
				this_success(L("注册成功"), 'login.php');
			}
		}
	} else {
		this_error(L("注册失败，请重试"));
	}
}






$serverId = FGetInt('mt');
//推荐人
$fromuser = FGetInt('fromuser');
//由推荐人，获得推荐人所在的mt服务器
if ($fromuser > 0) {
	$rs = $DB->getDRow("select server_id from t_member where id = '{$fromuser}' and status = 1");
	if($rs){
		$serverId = intval($rs['server_id']);
	}else{
		$fromuser = 0;
	}
}

$where = "where `status` = 1";
if($serverId){
	$where .= " and `id` = '{$serverId}'";
} else {
	//$where .= " and `real` = '1'";
}
$rsDt = $DB->getDTable("select * from t_mt4_server {$where} order by `real` desc,default_open_svr desc");
if(count($rsDt) <= 0) {
	FCreateErrorPage(array(
		'title'=>L("提示"),
		'content'=>L("无可用MT服务器开户.请联系管理员确认") . '!',
		'btnStr'=>L('返回'),
		'url'=>FPrevUrl(),
		'isSuccess'=>0,
		'autoRedirectTime'=>0,
	));
}else{
	$mt4server = $rsDt[0];
	
	//如果不是推荐人来的，那么必须是默认的 mt服务器，否则注册不了
	if($serverId <= 0){
		if($mt4server['default_open_svr'] <= 0){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("请指定默认开户MT Manage服务器") . '!',
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}
}

$typelist = explode(',',getConfigValue('ACCOUNT_TYPE', $mt4server['id']));//系统账户类型
$reg_fields = explode(',',getConfigValue('REGEX_FILEDS', $mt4server['id']));//用户注册字段
$must_reg_field = explode(',',getConfigValue('MUST_REG_FIELD', $mt4server['id'])); //注册必填字段

$inventCode = '';
if (in_array("register_invent_codes", $must_reg_field)) {  //若邀请码必填则自动填写
	if ($fromuser > 0) {
		$inventCode = $DB->getField("select loginid from t_member_mtlogin where member_id = '{$fromuser}' and status = 1");
	}
}

$check_phone_code = getConfigValue('CHECK_PHONE_CODE', $mt4server['id']);//开启手机短信验证

$leveages_arr = array();
$leveages_arr[] = array(
						'SVR_ID' => $mt4server['id'], 
						'DEFAULT_LEVERAGE' => getConfigValue('DEFAULT_LEVER', $mt4server['id']), //默认开户杠杆
						'LEVERAGES' => getConfigValue('LEVERAGES', $mt4server['id']), //平台允许的杠杆
						'MODIFY_LEVERAGE' => getConfigValue('MODIFY_LEVERAGE', $mt4server['id']), //注册是否允许修改杠杆比例
						);

$auto_active= $DB->getField("select configvalue from t_config_server where configname = 'OPEN_MT4_FILEDS' and server_id = '{$mt4server['id']}'");//系统开户字段
if($auto_active=='chineseName'){
	$must_reg_field[]='chineseName';
	$reg_fields[]='chineseName';
}else{
	$must_reg_field[]='nickname';
	$reg_fields[]='nickname';
}

//debug
$check_phone_code = 0;
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo L('注册') , ' - ' , $mt4server['mt4_name'] , ' - ' , $webConfig['f_title'];?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="<?php echo $webConfig['f_description'];?>" name="description" />
        <meta content="Coderthemes" name="author" />
        <link rel="shortcut icon" href="/favicon.ico">
        <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/cz.css" rel="stylesheet" type="text/css" />
		<?php
		$czWebSkinName = C('APP_TEMP_SRC');
		if(strlen($czWebSkinName) > 0 && $czWebSkinName != 'default'){
			echo '<link href="/assets/css/skin/' , $czWebSkinName , '.css" rel="stylesheet" type="text/css">';
		}
		?>
    </head>
    <body class="authentication-bg">

        <?php
        $reg_prov = $DB->getDTable("select * from `t_article` where f_key = 'reg_prov' and server_id = '{$mt4server['id']}' and status = 1");
        ?>

        <div class="account-pages mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-5" id="regWapBox">
                        <div class="card">
                            <!-- Logo-->
                            <div class="card-header pt-3 pb-3 text-center bg-primary bg-primary-rlf-cz">
                                <a href="#nolink">
                                    <span><img src="<?php echo C('WEB_LOGO_FILE');?>" alt="" height="50"></span>
                                </a>
                            </div>

                            <div class="card-body p-4">
                                
                                <div class="text-center w-75 m-auto">
                                    <h4 class="text-dark-50 text-center mt-0 font-weight-bold"><?php echo L('注册') , '(' , $mt4server['mt4_name'] , ')';?></h4>
                                    <p class="text-muted mb-4"><?php
									$ci = 0;
                                    foreach($LangNameList['list'] as $key=>$val){
										if($ci > 0){
											echo '&nbsp; | &nbsp;';
										}
										echo '<a href="set_lang.php?lang=' , $key , '">' , $val['title'] , '</a>';
										$ci++;
									}
									?></p>
                                </div>
                                
                            <form class="form-horizontal m-t" action="?clause=savereg" method="post" id="signupForm" autocomplete="off">
								<input type='hidden' name="fromuser" value="<?php echo $fromuser;?>"/>
								<input type='hidden' name="IA" value="<?php echo FGetStr('IA');?>"/>
                                <input type='hidden' name="usertype" value="<?php echo FGetStr('usertype');?>"/>
                                <input type='hidden' name="activetype" id="activetype" value="0"/>
								<input type='hidden' name="mtserver" id="mtserver" value="<?php echo $mt4server['id']; ?>"/>
								<?php
								if($check_phone_code==1){
								?>
					     		<div class="form-group">
				                    <ul class="nav nav-tabs" style="width:90%;height:50px; margin: auto">
					                    <li class="active" id="agentli"><a data-toggle="tab" href="#tab-1" aria-expanded="false" style='border-radius:none; margin-right:0px;' onClick="javascript: phoneHide()" ><?php echo L('邮箱注册'); ?></a></li>
					                    <li id="directli" ><a data-toggle="tab" href="#tab-2" aria-expanded="true" style='border-radius:none; margin-right:0px;' onClick="javascript: phoneShow()"><?php echo L('短信注册'); ?></a></li>
				                    </ul>
				                 </div>
								<?php
                                }
								//邮箱
								?>
				                 <div id="mail_show">
		                 			<div class="form-group">
										<label class="control-label"><?php echo L('邮箱');?><font color=red>*</font>：</label>
	                                    <input value="" id="n_email" name="n_email" class="form-control<?php if(in_array("email",$must_reg_field)){echo ' required';}?>" type="email" placeholder="<?php echo L('请输入您的邮箱');?>">
									</div>
				                 </div>
									<?php 
									//if(in_array('phone',$reg_fields)){
									//手机号
									//if(in_array("phone",$must_reg_field)){echo '<font color=red>*</font>';}
									?>
									<div class="form-group">
										<label class="control-label">
										<?php echo L('手机');?><font color=red>*</font>：
										</label>
										<input value="" id="phone" name="phone" class="form-control valid" type="text" placeholder="<?php echo L('请输入您的手机号');?>">
									</div>
									<?php 
									//}
									if(1==2 && $check_phone_code==1){
									?>
									<div id='phone_show'  style="display:none">                    	
                                        <div class="form-group">
                                            <label class="control-label"><?php echo L('手机验证码');?><font color=red>*</font>：</label>
                                            <div class="col-sm-2">
                                            <input id="phone_code_verify" name="phone_code_verify"   class="form-control valid" type="text" placeholder="<?php echo L('请输入图片验证码'); ?>"  aria-required="true" aria-invalid="false"  >  
                                            </div>
                                            <img src="{:U('Public/verify')}" id="reg_phone_codeimg"/>
                                            <input class="btn btn-primary" type="button" id="getcode" value="<?php echo L('获取验证码'); ?>"/>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label"></label>
                                            <div class="col-sm-2">
                                                <input id="phone_code" name="phone_code"  class="form-control valid" type="text" placeholder="<?php echo L('请输入手机验证码'); ?>"  aria-required="true" aria-invalid="false"  >
                                            </div>
                                        </div>									
                                        <div class="form-group">
                                            <label class="control-label"><?php echo L('邮箱');?><font color=red>*</font>：</label>
                                            <input id="email" name="email" remote="{:U('Public/checkreg',array('mtserver'=>$mt4server['id']))}"  class="form-control" type="email" placeholder="<?php echo L('请输入您的邮箱');?>">
                                        </div>
                                    </div>
	                                <?php
									}
	                             if(in_array('nickname',$reg_fields)){
									 //英文名
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('英文名');?><?php if(in_array("nickname",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input value="" id="nickname" name="nickname" class="form-control valid" type="text" placeholder="<?php echo L('请输入您的姓名拼音');?>" <?php if(in_array("nickname",$must_reg_field)){echo ' aria-required="true"';} ?> aria-invalid="false">
								</div>
                         		<?php
                                }
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('密码');?><font color=red>*</font>：</label>
									<input value="" id="password" name="password" rangelength="[6,12]" class="form-control" placeholder="<?php echo L('请输入您的登录密码');?>" type="password">
								</div>
								<div class="form-group">
									<label class="control-label"><?php echo L('确认密码');?><font color=red>*</font>：</label>
									<input type="hidden">
									<input style="display:none">  
									<input value="" id="confirm" name="confirm" rangelength="[6,12]" class="form-control" type="password" placeholder="<?php echo L('请再次输入您的密码');?>">
								</div>
								<?php
								if(in_array('chineseName',$reg_fields)){
									//中文名
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('中文名');?><?php if(in_array("chineseName",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="chineseName" name="chineseName" class="form-control" placeholder="<?php echo L('请输入您的中文名称');?>" type="text">
								</div>
								<?php
                                }
								if(in_array('account_type',$reg_fields) && $typelist){
									//自定义 用户类型
                                ?>                                    	
								<div class="form-group">
                                   <label class="control-label"><?php echo L('账户类型');?><?php if(in_array("account_type",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
                                        <select name='account_type' id="account_type" class='form-control'>
                                            <option value=""><?php echo L("请选择");?></option>
                                            <?php
                                            foreach($typelist as $key=>$val){
												echo '<option value="' , $val , '">' , $val , '</option>';
											}
											?>
                                        </select>
                               </div>
                               <?php
                               }
							   //开户杠杆
							   ?>
                                <div class="form-group" id="leverage_div">
                                   <label class="control-label"><?php echo L('开户杠杆');?></label>
                                        <select name='leverage' id="leverage" class='form-control'>
                                        </select>
                               </div>            
								<div class="form-group">
									<label class="control-label"><?php echo L('国籍');?><?php if(in_array("nationality",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
										<select class="form-control m-b" id="nationality" name="nationality"><?php write_country_option();?></select>
										<script>document.getElementById('nationality').value = '<?php echo $ConfigItem['Reg_DefaultCity'];?>';</script>
								</div>
								<!--								
								 <div class="form-group" id="data_5">
									<label class="control-label">出生日期：</label>
									<div class="col-sm-4 input-daterange">
										<input id="birthDate" name="birthDate" class="form-control" placeholder="请选择您的生日"  type="text">
									</div>
								</div>-->								
								 <div class="form-group">
									<label class="control-label"><?php echo L('居住地');?><?php if(in_array("birthCountry",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
										 <select class="form-control m-b" id="birthCountry" name="livingState"><?php write_country_option();?></select>
										 <script>document.getElementById('birthCountry').value = '<?php echo $ConfigItem['Reg_DefaultCity'];?>';</script>
								</div>			
								<div class="form-group">
									<label class="control-label"><?php echo L('省份');?><?php if(in_array("province",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="province" name="province" placeholder="<?php echo L('请输入您所在的省份');?>"  class="form-control<?php if(in_array("province",$must_reg_field)){echo ' required';} ?>" type="text">
								</div>
								<?php
								if(in_array('city',$reg_fields)){
								?>
							 	<div class="form-group">
                                    <label class="control-label"><?php echo L('城市');?><?php if(in_array("city",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="city" name="city" placeholder="<?php echo L('请输入您所在的城市');?>"  class="form-control" type="text">
								</div>
								<?php
                                }
								?>
							 	<!--<div class="form-group">
									<label class="control-label">地址：</label>
										<input id="residentialAddress" name="residentialAddress" placeholder="请输入您所居住的地址"   class="form-control" type="text">
								</div>								
								<div class="form-group">
									<label class="control-label">居住年限：</label>
									<input id="residenceTime" name="residenceTime"  class="form-control" placeholder="请输入您的居住年限"  type="text">
								</div>								
								<div class="form-group">
									<label class="control-label">住宅电话：</label>
									<input id="residentialTelephone" name="residentialTelephone"  class="form-control" type="text">
								</div>-->
								<?php
								if(in_array('bankName',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('开户支行名称');?><?php if(in_array("bankName",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="bankName" name="bankName"  class="form-control" placeholder="<?php echo L('请输入您的收款银行及支行名称：例如：XX银行XX省XX市XX支行');?>"  type="text">
								</div>
								<?php
                                }
								if(in_array('accountName',$reg_fields)){
								?>
								<div class="form-group">
                                    <label class="control-label"><?php echo L('银行开户姓名');?><?php if(in_array("accountName",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="accountName" name="accountName"  placeholder="<?php echo L('请输入您的收款人姓名');?>"  class="form-control" type="text">
								</div>
								<?php
                                }
								if(in_array('accountNum',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('银行账号');?><?php if(in_array("accountNum",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="accountNum" name="accountNum" placeholder="<?php echo L('请输入您的银行账号');?>"  class="form-control" type="text">
								</div>
								<?php
                                }
								if(in_array('bankCard',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('银行卡照片');?><?php if(in_array("bankCard",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
										<a id="btnUp1" style="padding:4px 10px;" class="btnsubmit btn btn-info btn-black-cz" href="#nolink"><?php echo L('选择图片');?></a>
			                        	<div id="imglist1"></div>
	                                    <input id="bankCard" name="bankCard"  type="text" size="0" style="height: 0;width: 0;border: 0;">
								</div>
								<?php
                                }
								if(in_array('swiftCode',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('银行国际代码');?><?php if(in_array("swiftCode",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="swiftCode" name="swiftCode" placeholder="<?php echo L('请输入您的银行国际代码');?>"  class="form-control" type="text">
								</div>
								<?php
                                }
								if(in_array('realname',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('真实姓名');?><?php if(in_array("realname",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="realname" name="realname" placeholder="<?php echo L('请输入您证件的真实姓名');?>" class="form-control" type="text">
								</div>
								<?php
                                }
								if(in_array('identity',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('证件号码');?><?php if(in_array("identity",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="identity" name="identity" placeholder="<?php echo L('请输入您的证件号码');?>" class="form-control" type="text">
								</div>
								<?php
                                }
								if(in_array('identityOpposite',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('身份证正面照');?><?php if(in_array("identityOpposite",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
                                    <a id="btnUp2" style="padding:4px 10px;" class="btnsubmit btn btn-info btn-black-cz" href="#nolink"><?php echo L('选择图片');?></a>
                                    <input id="identityOpposite" name="identityOpposite" type="text" size="0" style="height: 0;width: 0;border: 0;">
                                    <div id="imglist2"></div>
								</div>
								<?php
                                }
								if(in_array('identityBack',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('身份证反面照');?><?php if(in_array("identityBack",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
										<a id="btnUp3" style="padding:4px 10px;" class="btnsubmit btn btn-info btn-black-cz" href="#nolink"><?php echo L('选择图片');?></a>
	                                    <input id="identityBack" name="identityBack" type="text" size="0" style="height: 0;width: 0;border: 0;" >
			                        	<div id="imglist3"></div>
								</div>
								<?php
                                }
								if(in_array('addressProof',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('地址证明');?><?php if(in_array("addressProof",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
										<a id="btnUp4" style="padding:4px 10px;" class="btnsubmit btn btn-info btn-black-cz" href="#nolink"><?php echo L('选择图片');?></a>
	                                    <input id="addressProof" name="addressProof" type="text" size="0" style="height: 0;width: 0;border: 0;" >
			                        	<div id="imglist4"></div>
								</div>
								<?php
                                }
								if(in_array('register_invent_codes',$reg_fields)){
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('推荐码');?><?php if(in_array("register_invent_codes",$must_reg_field)){echo '<font color=red>*</font>';}?>：</label>
									<input id="register_invent_codes" name="register_invent_codes" value="<?php echo $inventCode;?>" placeholder="<?php echo L('请填写推荐码');?>" class="form-control <?php if(in_array("register_invent_codes",$must_reg_field)){echo ' required';} ?>" type="text">
								</div>
								<?php
                                }
								?>
								<div class="form-group">
									<label class="control-label"><?php echo L('验证码');?><font color=red>*</font>：</label>
									<input id="phone_code_verify" name="phone_code_verify" value="" placeholder="<?php echo L('请输入图片验证码');?>" class="form-control required" type="text">
									<img src="/include/vCode/003.php" id="reg_phone_codeimg"/>
                                </div>
								<?php
								if($reg_prov){
								?>
							    <div class="form-group">
	                                <div class="col-sm-offset-1">
	                                    <div class="checkbox">	                                        
											<?php
                                            foreach($reg_prov as $key=>$val){
                                                $reg_prov1 = get_lang_otherset_drow('-_news_-'.$val['id'],$CurrLangName,$mt4server['id'],1);
                                                echo '<a href="reg_agreement.php?id=' , $val['id'] , '&sid=' , $mt4server['id'] , '" target="_blank">《' , $reg_prov1['f_title'] , '》</a> &nbsp; ';
                                            }
                                            ?>
                                            <div style="margin-top:10px;">
                                            	<input type="checkbox" class="checkbox" id="agree" name="agree" value="1" style="display:inline-block;"> <label for="agree"><?php echo L('我本人知晓和同意上述所有条款');?></label>
                                            </div>
	                                    </div>
	                                </div>
	                            </div>
								<?php
                                }
								?>
                                <div class="form-group mb-0 text-center">
                                    <button class="btn btn-primary" type="submit" id="regiestbtn"><?php echo L('提交注册');?></button>
                                </div>
							</form>

                                
                                
                            </div> <!-- end card-body -->
                        </div>
                        <!-- end card -->
						<!--
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <p class="text-muted">Already have account? <a href="login.php" class="text-muted ml-1"><b>Log In</b></a></p>
                            </div>
                        </div>
                        -->
                        <!-- end row -->

                    </div> <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end page -->

		<!--
        <footer class="footer footer-alt">&nbsp;</footer>
        -->

        <!-- App js -->
        <script src="/assets/js/app.min.js"></script>
        
        <script src="/assets/js/ajaxupload.3.5.js"></script>
        <script src="/assets/js/layer/layer.js"></script>        
        
        <script src="/assets/js/validate/jquery.validate.min.js"></script>
        
        
 		<script>

			var phone_show = document.getElementById("phone_show");
			var mail_show = document.getElementById("mail_show");
          //  phone_show.style.display = "none";
            $("#phone_show").hide();
            function phoneShow() {
                $("#activetype").val(1);
				phone_show.style.display = "block";
				mail_show.style.display = "none";
            }

            function phoneHide() {
                  $("#activetype").val(0);
                phone_show.style.display = "none";
        		mail_show.style.display = "block";
				document.getElementById('phone').value = '';
				document.getElementById('phone_code_verify').value = '';
				document.getElementById('phone_code').value = '';
				document.getElementById('email').value = '';
            }


		    $("#data_5 .input-daterange").datepicker({keyboardNavigation:!1,forceParse:1,autoclose:!0})
			$.validator.setDefaults({
				highlight: function(e) {
					$(e).closest(".form-group").removeClass("has-success").addClass("has-error")
				},
				success: function(e) {
					e.closest(".form-group").removeClass("has-error").addClass("has-success")
				},
				errorElement: "span",
				errorPlacement: function(e, r) {
					e.appendTo(r.is(":radio") || r.is(":checkbox") ? r.parent().parent().parent() : r.parent())
				},
				submitHandler: function(form) {
		           $("#regiestbtn").attr('disabled','disabled'); 
                   var url=$("#regiestbtn").closest("form").attr('action');
                   $.ajax({
                         url:url,
                         type:'POST',
                         data:$("#regiestbtn").closest("form").serialize(),
                         success:function(data){
                             if(data.status){
                                alert(data.info);
                                setTimeout(function(){
                                   document.location.href='login.php';
                               },500);
                             }else{
                                 $("#regiestbtn").removeAttr('disabled'); 
                                alert(data.info);
                             }
                         },error:function(data){
                            alert('<?php echo L('注册失败'); ?>');
                         },dataType:'json'
                   
                       })
                   return false;
		       },
				errorClass: "help-block m-b-none",
				validClass: "help-block m-b-none"
			}), $().ready(function() {


				var e = "<i class='fa fa-times-circle'></i> ";
				$("#signupForm").validate({
					rules: {
						email: {
							required: !0,
							email: !0
						},
						<?php
						if(in_array('nickname',$must_reg_field)){
						?>
						nickname: {
							required: !0,
							english: "<?php echo L('只能输入字母'); ?>",
							minlength : 3,
							maxlength : 20
						},
						<?php
						}
						//if(in_array('phone',$must_reg_field)){
						?>
						phone: {
							required: !0,
							isNum: "<?php echo L('只能输入数字'); ?>！"
						},
						<?php
						//	}
						?>						
						password: {
							required: !0,
							minlength: 6,
							ckps: "<?php echo L('密码必须包含大小写字母和数字'); ?>！"
						},
						confirm: {
							required: !0,
							minlength: 6,
							equalTo: "#password"
						},
						<?php
						// if(in_array('chineseName',$must_reg_field)){
						?>
						// chineseName: {
						// 	required: !0,
						// 	minlength: 2,
						// 	maxlength: 50,
						// 	isChar: "<?php echo L('请输入规范的中文名称'); ?>！"
						// },
						<?php
						 // }
						?>
						<?php
						if(in_array('bankName',$must_reg_field)){
						?>
						bankName: {
							required: !0,
							minlength: 2
						},
						<?php
						 }
						if(in_array('accountName',$must_reg_field)){
						?>
						accountName: {
							required: !0,
							minlength: 1
						},
						<?php
						 }
						if(in_array('accountNum',$must_reg_field)){
						?>
						/*accountNum: {
							required: !0,
							minlength: 10
						},*/
						<?php
						 }
						if(in_array('realname',$must_reg_field)){
						?>
						realname: "required",
						<?php
						 }
						if(in_array('identity',$must_reg_field)){
						?>
						identity: {
							required: !0,
							minlength: 5
						},
						<?php
						 }
						if(in_array('bankCard',$must_reg_field)){
						?>
						bankCard:  "required",
						<?php
						 }
						if(in_array('swiftCode',$must_reg_field)){
						?>
						swiftCode:  "required",
						<?php
						 }
						if(in_array('identityOpposite',$must_reg_field)){
						?>
						identityOpposite:  "required",
						<?php
						 }
						if(in_array('identityBack',$must_reg_field)){
						?>
						identityBack: "required",
						<?php
						 }
						if(in_array('city',$must_reg_field)){
						?>
						city: "required",
						<?php
						 }
						?>
						agree: "required",
						<?php
						if(in_array('addressProof',$must_reg_field)){
						?>
						addressProof: "required",
						<?php
						 }
						?>
					},
					messages: {
						email: e + "<?php echo L('请输入您的E-mail');?>",
						<?php
						if(in_array('nickname',$must_reg_field)){
						?>
						nickname: {
							required: e + "<?php echo L('请输入您的英文名');?>",
							minlength : e + "<?php echo L('最少3个字符'); ?>",
							maxlength : e + "<?php echo L('最多20个字符'); ?>",
							english : e + "<?php echo L('只能输入字母'); ?>"
						},
						<?php
						 }
						//if(in_array('phone',$must_reg_field)){
						?>
						phone: {
							required: e + "<?php echo L('请输入您的手机号');?>",
							isNum: e + "<?php echo L('只能输入数字'); ?>！"
						},
						<?php
						// }
						?>
						agree: e + "<?php echo L('请阅读并同意条款');?>",
						phone_code_verify: e + "<?php echo L('请输入验证码');?>",
						password: {
							required: e + "<?php echo L('请输入您的密码');?>",
							minlength: e + "<?php echo L('密码必须6个字符以上');?>",
							ckps: e + "<?php echo L('密码必须包含大小写字母和数字'); ?>！"
						},
						confirm: {
							required: e + "<?php echo L('请再次输入密码');?>",
							minlength: e + "<?php echo L('密码必须6个字符以上');?>",
							equalTo: e + "<?php echo L('两次输入的密码不一致');?>"
						},
						<?php
						if(in_array('chineseName',$must_reg_field)){
						?>
						chineseName: {
							required: e + "<?php echo L('请输入您的中文名称');?>",
							minlength : e + "<?php echo L('最少2个字符'); ?>",
							maxlength : e + "<?php echo L('最多50个字符'); ?>",
							//isChar : e + "<?php echo L('请输入规范的中文名称'); ?>！"
						},
						<?php
						 }
						if(in_array('bankName',$must_reg_field)){
						?>
						bankName: e + "<?php echo L('请输入您的收款银行及支行名称');?>",
						<?php
						 }
						if(in_array('accountName',$must_reg_field)){
						?>
						accountName: e + "<?php echo L('请输入您的收款人姓名');?>",
						<?php
						 }
						if(in_array('accountNum',$must_reg_field)){
						?>
						/*accountNum: {
							required: e + "<?php echo L('请输入您的银行账号');?>",
							minlength: e + "<?php echo L('银行账号必须10个字符以上');?>"
						},*/
						<?php
						 }
						if(in_array('realname',$must_reg_field)){
						?>
						realname: e + "<?php echo L('请输入您的真实姓名');?>",
						<?php
						 }
						if(in_array('identity',$must_reg_field)){
						?>
						identity: {
							required: e + "<?php echo L('请输入您的证件号码');?>",
							minlength: e + "<?php echo L('证件号必须5个字符以上');?>"
						},
						<?php
						 }
						if(in_array('bankCard',$must_reg_field)){
						?>
						bankCard: e + "<?php echo L('请上传您的银行卡照片');?>",
						<?php
						 }
						if(in_array('swiftCode',$must_reg_field)){
						?>
						swiftCode: e + "<?php echo L('请输入您的银行国际代码');?>",
						<?php
						 }
						if(in_array('identityOpposite',$must_reg_field)){
						?>
						identityOpposite: e + "<?php echo L('请上传您的身份证正面照片');?>",
						<?php
						 }
						if(in_array('identityBack',$must_reg_field)){
						?>
						identityBack: e + "<?php echo L('请上传您的身份证反面照片');?>",
						<?php
						 }
						if(in_array('city',$must_reg_field)){
						?>
						city: e + "<?php echo L('请输入您所在的城市');?>",
						<?php
						 }
						if(in_array('addressProof',$must_reg_field)){
						?>
						addressProof: e + "<?php echo L('请上传地址证明'); ?>",
						<?php
						 }
						?>
					}
				}) 
			});
			
			// jQuery.validator.addMethod("isChar", function(value, element) {
			// 	var regName = /[^\u4e00-\u9fa5\" "]/g;
			// 	return this.optional(element) || !regName.test( value );  
			// }, "<?php echo L('请输入规范的中文名称'); ?>！");

			jQuery.validator.addMethod("english", function(value, element) {
			    var regEname = /^([a-zA-Z\" "]+)$/;
			    return this.optional(element) || (regEname.test(value));
			}, "<?php echo L('只能输入字母'); ?>");

			jQuery.validator.addMethod("isNum", function(value, element) {
			    var regNum = /[^\d^\+]/g;
			    return this.optional(element) || (!regNum.test(value));
			}, "<?php echo L('只能输入数字'); ?>！");

			jQuery.validator.addMethod("ckps", function(value, element) {
			    var regPass = /^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*).{6,12}$/;
			    return this.optional(element) || (regPass.test(value));
			}, "<?php echo L('密码必须包含大小写字母和数字'); ?>！");

			//验证帐号
			var loginurl = "<?php echo C('LOGIN_URL');?>";
			var form = $('#form');
			<?php
			if(in_array('identityOpposite',$reg_fields)){
			?>
			$(function() {
				var button = $('#btnUp2'),
					interval;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php?from=reg",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|JPG|JPEG|PNG|png)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择 jpg,PNG 格式的文件');?>', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							var text = button.text();
							if (text.length < 10) {
								button.text(text + '.');
							} else {
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
					  	window.clearInterval(interval);
					    this.enable();
   
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L('上传完成');?>');
							$('#imglist2').empty();
							$('<img style="width:100px;hight:100px;border:1px solid #cccccc;margin:5px;">').appendTo($('#imglist2')).attr("src", savepath+savename);
							$("#identityOpposite").val(info.data[0].id);
							
							layer.msg(info.info);
						}
					}
				});

			});
			<?php
			 }
			if(in_array('identityBack',$reg_fields)){
			?>
			$(function() {
				var button = $('#btnUp3'),
					interval;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php?from=reg",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择 jpg,PNG 格式的文件');?>', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							var text = button.text();
							if (text.length < 10) {
								button.text(text + '.');
							} else {
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
                                        window.clearInterval(interval);
                                        this.enable();
										
										
										
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L('上传完成');?>');
							$('#imglist3').empty();
							$('<img style="width:100px;hight:100px;border:1px solid #cccccc;margin:5px;">').appendTo($('#imglist3')).attr("src", savepath+savename);
							$("#identityBack").val(info.data[0].id);
							
							layer.msg(info.info);
						}

					}
				});

			});
			<?php
			 }
			if(in_array('bankCard',$reg_fields)){
			?>
			$(function() {
				var button = $('#btnUp1'),
					interval;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php?from=reg",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择 jpg,PNG 格式的文件');?>', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							var text = button.text();
							if (text.length < 10) {
								button.text(text + '.');
							} else {
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
                                          window.clearInterval(interval);
                                                 this.enable();
												 
												 
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L('上传完成');?>');
							$('#imglist1').empty();
							$('<img style="width:100px;hight:100px;border:1px solid #cccccc;margin:5px;">').appendTo($('#imglist1')).attr("src", savepath+savename);
							$("#bankCard").val(info.data[0].id);
							
							layer.msg(info.info);
						}

					}
				});

			});
			<?php
			 }
			if(in_array('addressProof',$reg_fields)){
			?>
			$(function() {
				var button = $('#btnUp4'),
					interval;
				new AjaxUpload(button, {
					action: "uploader/ajax_upload_save.php?from=reg",
					name: 'myfile',
					onSubmit: function(file, ext) {
						if (!(ext && /^(jpg|jpeg|png|JPG|JPEG|PNG)$/.test(ext))) {
							alert('<?php echo L('图片格式不正确,请选择 jpg,PNG 格式的文件');?>', '<?php echo L('系统提示');?>');
							return false;
						}
						button.text('<?php echo L('上传中');?>');
						this.disable();
						interval = window.setInterval(function() {
							var text = button.text();
							if (text.length < 10) {
								button.text(text + '.');
							} else {
								button.text('<?php echo L('上传中');?>');
							}
						}, 200);
					},
					onComplete: function(file, response) {
                                           window.clearInterval(interval);
                                             this.enable();
                                             
											 
						var k=response;k=k.substring(k.indexOf("{"));k=k.substring(0,k.lastIndexOf("}")+1);
						var info=$.parseJSON(k);
						if (info['status'] == -1) {
							alert(info['info']);
						}else {
							var savepath = info.data[0].savepath;
							var savename = info.data[0].savename;

							button.text('<?php echo L('上传完成');?>');
							$('#imglist4').empty();
							$('<img style="width:100px;hight:100px;border:1px solid #cccccc;margin:5px;">').appendTo($('#imglist4')).attr("src", savepath+savename);
							$("#addressProof").val(info.data[0].id);
							
							layer.msg(info.info);
						}

					}
				});

			});
			<?php
			 }
			?>
			
			/*function alert(msg) {
				layer.alert(msg, {
					skin: 'layui-layer-molv' //样式类名
				});
			}*/

			var json = '<?php echo json_encode($leveages_arr);?>';
			$('#mtserver').change(function(){
				var svr =  $(this).val();
				changeMtLeveage(svr);
			})
			
			function changeMtLeveage(svr){
				var json_obj = eval('(' + json + ')');
				$('#mtserver').val(svr);
				$('#leverage').empty();
				for(var o in json_obj){ 
					if(json_obj[o].SVR_ID ==svr){
						if(json_obj[o].MODIFY_LEVERAGE =='1'){
							$('#leverage_div').show();
							var arr = json_obj[o].LEVERAGES.split(',');
							for(i=0;i<arr.length;i++){
								$("#leverage").append("<option value='"+arr[i]+"'>"+arr[i]+"</option>"); 
							}
							console.log(json_obj[o]);
						}else{
							$('#leverage_div').hide();
						}
					}
					$("#leverage").val(json_obj[o].DEFAULT_LEVERAGE);
				}
				
			}
			
			changeMtLeveage($("#mtserver").val());
	
				$(document).on("click","#reg_phone_codeimg",function(){
				 //$("#reg_phone_codeimg").click(function(){
				  $("#reg_phone_codeimg").attr("src","/include/vCode/003.php?t="+new Date());
				 });
				 //邮箱验证码
				 $(document).on("click","#reg_mail_codeimg",function(){
				//$("#reg_mail_codeimg").click(function(){
					$("#reg_mail_codeimg").attr("src","{:U('Public/verify')}?t="+new Date());
				 });
				//短信验证码
				$(document).on("click","#getcode",function(){
			//$("#getcode").click(function(){
					if (wait != 60) {
					  return false;
					}
				  var phone=$("#phone").val();
				  var mtserver=$("#mtserver").val();
				  var imgcode=$("#phone_code_verify").val();
				  var _this=$(this);
				 
			  
				  time(this);
				  $.post('{:U("Public/sendactivemsg")}',{phone:phone,mtserver:mtserver,imgcode:imgcode},function(data){
					   $("#reg_phone_codeimg").attr("src","{:U('Public/verify')}?t="+new Date());
						if(data.error==0){
							alert('<?php echo L('发送成功'); ?>');
							}else{
							   alert(data.info);
							   wait=0;
							}
					},'json');
				});

				        //邮箱验证码
						$(document).on("click","#getmailcode",function(){
			//$("#getmailcode").click(function(){
                            if (mailwait != 60) {
                              return false;
                            }
                          var email=$("#n_email").val();
                          var mtserver=$("#mtserver").val();
                          var imgcode=$("#mail_code_verify").val();
                          var _this=$(this);
                         
                      
                          mailtime(this);
                          $.post('{:U("Public/sendmailactivemsg")}',{email:email,mtserver:mtserver,imgcode:imgcode},function(data){
                               $("#reg_mail_codeimg").attr("src","{:U('Public/verify')}?t="+new Date());
                                if(data.error==0){
                                    alert('<?php echo L('发送成功'); ?>');
                                    }else{
                                       alert(data.info);
                                       mailwait=0;
                                    }
                            },'json');
                        });



		var wait=60;
        function time(o) {
	   		if (wait == 0) {
	   			 $("#getcode").val("<?php echo L('重发'); ?>");
	   				wait = 60;
	   		} else {
	   			$("#getcode").val('<?php echo L('重发'); ?>('+wait+')');
		   		wait--;
		   		setTimeout(function() {
	   				time(o)
	   			},1000)
	   		}
   		}

		var mailwait=60;
        function mailtime(o) {
	   		if (mailwait == 0) {
	   			 $("#getmailcode").val('<?php echo L('重发'); ?>');
	   				mailwait = 60;
	   		} else {
	   			$("#getmailcode").val('<?php echo L('重发'); ?>('+mailwait+')');
		   		mailwait--;
		   		setTimeout(function() {
	   				mailtime(o)
	   			},1000)
	   		}
   		}
               
		</script>  

        
        
    </body>
</html>
