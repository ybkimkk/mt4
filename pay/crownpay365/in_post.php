<?php
//第一步是，如果有需要补充资料，则在第一步里输出表单项

if($_GET['clause'] == 'topay'){
	//第二步是发起支付
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
	
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
	
	//---------------------------------------
	
	/*$fullname = FPostStr('fullname');
	if(strlen($fullname) <= 0){
		FJS_AB(L('请填写姓名'));
	}
	
	$channel = FPostStr('channel');
	if(strlen($channel) <= 0){
		FJS_AB(L('请选择支付通道'));
	}
	
	$email = FPostStr('email');
	if(strlen($email) <= 0){
		FJS_AB(L('请填写Email'));
	}*/
	
	$oid = FGetStr('oid');
	
	$DRInfo = $DB->getDRow("select * from t_inmoney where `payno` = '{$oid}'");
	if(!$DRInfo){
		FJS_AT(L('未找到该订单'),'?');
	}
	if($DRInfo['member_id'] != $DRAdmin['id']){
		FJS_AT(L('该订单不属于您'),'?');
	}
	if($DRInfo['status'] != 0){
		FJS_AT(L('订单状态错误'),'?');
	}
	
	$DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$DRInfo['pay_id']}') a left join t_pay_currency b on a.f_currencyId = b.id");
	if(!$DRPay){
		FJS_AT(L('未找到支付方式'),'?');
	}
	
	//---------------------------------------
	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/crownpay365/config.php');
	
	//test
	//$DRInfo['price'] = 0.1;
	
	$res = new usdt(trim($DRPay['submit_gateway']),trim($DRPay['PayKey']),trim($DRPay['PaySignKey']));
	
	//充值接口按数量(暂不能用)
	//$data = $res->recharge();
	
	//充值接口按人民币
	$dataArr    = array(
		'type' => 1, //充值方式 1:平台提供支付页面 2:盘口自己提供支付页面
		'style' => 2, //支付页面风格 1：风格1，2：风格2
		'amount'           => $DRInfo['price'], //人民币金额
		'address'			=> '', //留空即可
		'username'			=> $DRAdmin['accountName'], //自己按需要填写
		'orderid'			=> $DRInfo['payno'],
		'appid'      => trim($DRPay['PayKey']) ,
		'return_url'	=>(FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_back_' . trim($DRPay['PayCode']) . '.html',
		'notify_url'	=>(FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_s2s_' . trim($DRPay['PayCode']) . '.html',
	);
	$data = $res->rechargeRmb($dataArr);
	if($data['status'] == 1){
		FRedirect($data['data']);
		exit;
	}else{
			FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>'ERROR：' . $data['err'],
			'btnStr'=>L('返回'),
			'url'=>'/',
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
		exit;
	}	
}else{
	/*
	echo '<div class="form-group row">
				<label class="col-sm-2">' , L("姓名") , '：</label>
				<div class="col-sm-8">
					<input name="fullname" type="text" class="form-control" value="' , $DRAdmin['realname'] , '" placeholder="">
				</div>
			</div>';
			
	echo '<div class="form-group row">
				<label class="col-sm-2">' , L("支付通道") , '：</label>
				<div class="col-sm-8 mt-sm-1">
					<div class="radio radio-info radio-inline">
						<input type="radio" value="UNIONPAY" name="channel" id="channel1" checked="checked">
						<label for="channel1" class="ttt">UNIONPAY</label>
					</div>
					<div class="radio radio-info radio-inline">
						<input type="radio" value="ALIPAY" name="channel" id="channel2">
						<label for="channel2" class="ttt">ALIPAY</label>
					</div>
				</div>
			</div>';
			
	echo '<div class="form-group row">
			<label class="col-sm-2">' , L("Email") , '：</label>
			<div class="col-sm-8">
				<input name="email" type="text" class="form-control" value="' , $DRAdmin['email'] , '" placeholder="">
			</div>
		</div>';
	*/
}