<?php
$CZMenu = array();

CZMenu_add('a',array('title'=>'首页','ico'=>'dripicons-home','url'=>'index.php','murl'=>array('main.php','member_changepass.php','member_profile.php','member_mybank.php')));

CZMenu_add('b',array('title'=>'出入金','ico'=>'dripicons-view-apps'));
	CZMenu_add('b1',array('title'=>'入金','url'=>'in_money.php'));
	CZMenu_add('b2',array('title'=>'出金','url'=>'out_money.php','macc'=>array('MT转账')));
	CZMenu_add('b8',array('title'=>'返佣提现','url'=>'out_amount.php'));
	CZMenu_add('b3',array('title'=>'我的赠金','url'=>'my_credits.php'));
	CZMenu_add('b4',array('title'=>'待审核入金','url'=>'deposit_waitin.php','macc'=>array('管理员入金','入金审核','入金驳回审核')));//下载入金信息 
	CZMenu_add('b5',array('title'=>'待审核出金','url'=>'deposit_waitout.php','macc'=>array('出金审核','出金驳回')));//下载出金信息 
	CZMenu_add('b6',array('title'=>'赠金审核','url'=>'credits_check.php'));//,'macc'=>array('审核赠金申请','驳回赠金申请')
	CZMenu_add('b7',array('title'=>'待审核银行卡','url'=>'deposit_bank.php'));

CZMenu_add('c',array('title'=>'客户管理','ico'=>'dripicons-user-group'));
	CZMenu_add('c1',array('title'=>'客户列表','url'=>'member.php','macc'=>array('添加客户','下载客户信息','登录日志','查看客户详情','修改客户','重置CRM密码','重发激活邮件','激活客户','分配客户','授权客户','设置CRM登录与禁用','删除客户','解除登录锁定','标记','修改佣金余额')));//下载客户信息 保存客户分配 查看客户等级 查看下级账户联系信息 MT分组 跟单首页
	CZMenu_add('c2',array('title'=>'账户管理','url'=>'mtlogin.php','macc'=>array('绑定MT帐号','MT开户','修改','重置MT密码','调整杠杆','调整分组','调整只读禁用','MT账号移仓','删除MT账号')));//查看客户杠杆调整申请
	CZMenu_add('c3',array('title'=>'账户审核','url'=>'authmt4.php'));//,'macc'=>array('修改备注')
	CZMenu_add('c4',array('title'=>'客户变动详情','url'=>'member_change_log.php'));

CZMenu_add('d',array('title'=>'报表中心','ico'=>'dripicons-graph-pie'));
	CZMenu_add('d1',array('title'=>'资金报表'));
		CZMenu_add('d101',array('title'=>'资金报表','url'=>'rc_capita.php','macc'=>array('报表导出')));
		CZMenu_add('d102',array('title'=>'入金报表','url'=>'rc_deposit.php','macc'=>array('报表导出')));
		CZMenu_add('d103',array('title'=>'出金报表','url'=>'rc_withdrawal.php','macc'=>array('报表导出')));
		CZMenu_add('d104',array('title'=>'赠金报表','url'=>'rc_credit.php','macc'=>array('报表导出')));
		CZMenu_add('d105',array('title'=>'MT转账报表','url'=>'rc_mt.php','macc'=>array('报表导出')));
	CZMenu_add('d2',array('title'=>'交易报表'));
		CZMenu_add('d201',array('title'=>'订单报表','url'=>'report_history_trade.php','macc'=>array('报表导出')));
		CZMenu_add('d202',array('title'=>'持仓报表','url'=>'rc_position.php','macc'=>array('报表导出')));
		CZMenu_add('d203',array('title'=>'平仓报表','url'=>'rc_closer.php','macc'=>array('报表导出')));
		CZMenu_add('d204',array('title'=>'挂单报表','url'=>'rc_limit.php','macc'=>array('报表导出')));
	CZMenu_add('d3',array('title'=>'佣金报表'));
		CZMenu_add('d301',array('title'=>'点差返佣报表','url'=>'commission_view_details.php','macc'=>array('报表导出')));
		CZMenu_add('d303',array('title'=>'点差返佣报表(0)','url'=>'commission_view_details0.php','macc'=>array('报表导出')));
		CZMenu_add('d302',array('title'=>'未返佣报表','url'=>'commission_view_no_details.php','macc'=>array('报表导出')));
		CZMenu_add('d304',array('title'=>'返佣(补)报表','url'=>'cvd_other.php'));//,'macc'=>array('报表导出')
		CZMenu_add('d305',array('title'=>'返佣(补)报表(0)','url'=>'cvd_other0.php'));//,'macc'=>array('报表导出')
	CZMenu_add('d4',array('title'=>'详情报表'));
		CZMenu_add('d401',array('title'=>'客户报表','url'=>'customer_indexstatic.php'));
		CZMenu_add('d402',array('title'=>'统计报表','url'=>'report_index.php','macc'=>array('报表导出')));
		CZMenu_add('d403',array('title'=>'返佣/提现报表','url'=>'commission_index.php'));

CZMenu_add('e',array('title'=>'系统管理','ico'=>'dripicons-gear'));
	CZMenu_add('e1',array('title'=>'系统设置'));
		CZMenu_add('e101',array('title'=>'角色管理','url'=>'role.php'));
	CZMenu_add('e2',array('title'=>'MT设置'));
		//CZMenu_add('e201',array('title'=>'返佣设置','url'=>'commission_setting.php'));//下载设置 ,'macc'=>array('添加设置','修改设置','修改状态','删除设置')
		CZMenu_add('e207',array('title'=>'返佣设置(新)','url'=>'commission_setting_new.php'));//下载设置 ,'macc'=>array('添加设置','修改设置','修改状态','删除设置')
		CZMenu_add('e208',array('title'=>'返佣设置(补)','url'=>'commission_setting_other.php'));
		CZMenu_add('e202',array('title'=>'MT管理','url'=>'mt_server.php'));//开户号段设置 ,'macc'=>array('添加MT服务器','修改MT服务器','删除MT服务器')
		CZMenu_add('e203',array('title'=>'分组同步','url'=>'sync_groups.php'));
		CZMenu_add('e204',array('title'=>'货币同步','url'=>'sync_symbol.php'));
		CZMenu_add('e205',array('title'=>'等级设置','url'=>'rank.php'));
		CZMenu_add('e206',array('title'=>'赠金规则','url'=>'credit.php'));
	CZMenu_add('e3',array('title'=>'消息管理'));
		CZMenu_add('e301',array('title'=>'信息群发','url'=>'group_mail.php'));
		CZMenu_add('e302',array('title'=>'留言列表','url'=>'myguestbook.php'));
		CZMenu_add('e303',array('title'=>'留言管理','url'=>'guestbook.php'));
		CZMenu_add('e304',array('title'=>'通知信息','url'=>'myarticle.php'));
		CZMenu_add('e305',array('title'=>'通知管理','url'=>'article.php'));
		CZMenu_add('e306',array('title'=>'信息模板','url'=>'mail_tpl.php'));
		CZMenu_add('e307',array('title'=>'活动信息','url'=>'myactivity.php'));
		CZMenu_add('e308',array('title'=>'活动管理','url'=>'activity.php'));
	CZMenu_add('e4',array('title'=>'日志管理'));
		CZMenu_add('e401',array('title'=>'系统操作日志','url'=>'logger.php'));
		CZMenu_add('e402',array('title'=>'信息发送日志','url'=>'email_log.php'));
if($_SERVER['HTTP_HOST'] == CC_CRM_ITEM_DOMAIN_office_etomarkets_net){
	CZMenu_add('e10',array('title'=>'交易工具'));
		CZMenu_add('e1001',array('title'=>'分析师观点','url'=>'https://' . CC_CRM_ITEM_DOMAIN_office_etomarkets_net . '/plugin/tc/ta.php','target'=>'_blank'));
		CZMenu_add('e1002',array('title'=>'Web TV','url'=>'https://' . CC_CRM_ITEM_DOMAIN_office_etomarkets_net . '/plugin/tc/webtv.php','target'=>'_blank'));
		CZMenu_add('e1003',array('title'=>'财经日历','url'=>'https://' . CC_CRM_ITEM_DOMAIN_office_etomarkets_net . '/plugin/tc/calendar.php','target'=>'_blank'));
		CZMenu_add('e1004',array('title'=>'MT4插件','url'=>'https://' . CC_CRM_ITEM_DOMAIN_office_etomarkets_net . '/plugin/tc/mtp.php','target'=>'_blank'));
}
	CZMenu_add('e5',array('title'=>'同步客户','url'=>'sync_users.php'));
	CZMenu_add('e6',array('title'=>'配置参数','url'=>'config_set.php'));
	CZMenu_add('e7',array('title'=>'支付币种','url'=>'pay_currency.php'));
	CZMenu_add('e8',array('title'=>'支付管理','url'=>'pay_config.php'));
	CZMenu_add('e9',array('title'=>'提现设置','url'=>'out_config.php'));

//-------------------------------------------------------------------------------------------

function CZMenu_add($key,$val){
	if(strlen($key) <= 0){
		return;
	}else if(strlen($key) == 1){
		//a、b、c……z
		$GLOBALS['CZMenu'][$key] = $val;
	}else if(strlen($key) <= 3){
		//a1、a2、a3……a10、a11、a12……a99
		$key1 = substr($key,0,1);
		$GLOBALS['CZMenu'][$key1]['sub'][$key] = $val;
	}else{
		//a101、a102、a103……a199
		//a1001、a1002、a1003……a1099
		if(strlen($key) == 4){
			$key1 = substr($key,0,1);
			$key2 = substr($key,0,2);
		}else if(strlen($key) == 5){
			$key1 = substr($key,0,1);
			$key2 = substr($key,0,3);
		}else{
			return;
		}
		$GLOBALS['CZMenu'][$key1]['sub'][$key2]['sub'][$key] = $val;
	}
}


