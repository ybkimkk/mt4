<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once('conn.php');
require_once('chk_logged.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/pager.class.php');

if($Clause == 'saveeditinfo'){
	admin_action_log();
	
	$server_id = $DRAdmin['server_id'];

	$nickname = FPostStr('nickname');
	if(strlen($nickname) <= 0){
		FJS_P_AC(L('请输入您的英文名或者姓名拼音'));
	}

	$data = array(
				//'email'=>$email,
				'nickname'=>FPostStr('nickname'),
				//'phone'=>FPostStr('phone'),
				'chineseName'=>FPostStr('chineseName'),
				'nationality'=>FPostStr('nationality'),
				'birthDate'=>FPostStr('birthDate'),
				'livingState'=>FPostStr('livingState'),
				'province'=>FPostStr('province'),
				'city'=>FPostStr('city'),
				'residentialAddress'=>FPostStr('residentialAddress'),
				'residenceTime'=>FPostStr('residenceTime'),
				'residentialTelephone'=>FPostStr('residentialTelephone'),
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
				
				'headimg'=>FPostStr('headimg'),
	);
	
	/* 检测重复字段 */
	if (strlen($data['identity'])) {//这是个定制的写法，因为只允许配置这个identity不允许重复
		$checkunque = explode(',', C("CHECK_FIELD_UNQUE"));//identity,identity
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
		$fields = unserialize(html_entity_decode($DB->getField("select `extra` from `t_config` where `name` = 'REGEX_FILEDS'")));
		//遍历所有不允许重复的字段
		foreach ($checkunque as $key => $value) {
			//如果该字段有填写内容
			if(strlen($value) > 0 && strlen($data[$value]) > 0){
				//找到其它用户
				$fieldused = $DB->getDRow("select * from `t_member` where `{$value}` = '" . $data[$value] . "' and `server_id` = '{$server_id}' and `status` in (0,1) and id <> '{$DRAdmin['id']}'");
				if ($fieldused) {
					FJS_P_AC(L($fields[$value]) . ' ' . L("已存在"));
				}
			}
		}
	}
	
	$ids = $DB->update("t_member",$data,"where id = '{$DRAdmin['id']}'");
	
	FJS_P_AT(L("保存成功"),'?');
}


if(!in_array($Clause,array('main','editinfo'))){
	$Clause = 'main';
}
require_once('member_profile/' . $Clause . '.php');


