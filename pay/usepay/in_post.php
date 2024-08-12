<?php
//第一步是，如果有需要补充资料，则在第一步里输出表单项
require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/usepay/_config.php');

if($_GET['clause'] == 'topay'){
	//第二步是发起支付
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
	
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
	
	//---------------------------------------
	
	$channel = FPostStr('channel');
	if(strlen($channel) <= 0){
		FJS_AB(L('请选择支付通道'));
	}
	
	$fullname = FPostStr('fullname');
	if(strlen($fullname) <= 0){
		FJS_AB(L('请填写姓名'));
	}
		
	$email = FPostStr('email');
	if(strlen($email) <= 0){
		FJS_AB(L('请填写Email'));
	}
	
	$mobile = FPostStr('mobile');
	if(strlen($mobile) <= 0){
		FJS_AB(L('请填写手机号'));
	}
	
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
	
	//test
	//$DRInfo['price'] = 1;
	
	$payInfo = array(
		'payid'=>$channel,
		'requestid'=>$DRInfo['payno'],
		'amount'=>$DRInfo['price'],
		'callfront'=>(FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_back_' . trim($DRPay['PayCode']) . '.html',
		'callback'=>(FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_s2s_' . trim($DRPay['PayCode']) . '.html',
		'customername'=>$fullname,
		'mobile'=>$mobile,
		'email'=>$email,
	);
	
	$rsahelper = new RSAHelper(trim($DRPay['PaySignKey']));
	$ciphertext = $rsahelper->publicEncrypt(json_encode($payInfo));
	unset($rsahelper);
	
	$postData = array('access_token'=>trim($DRPay['PayKey']),'ciphertext'=>$ciphertext);
	
	//---------------------------------------
	
	if($channel == '100008'){
		$response = Tool::SendEmailPost(trim($DRPay['submit_gateway']) . '/pub/pay/records/gateway',$postData);
		if($response == 'OK'){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>'支付信息发送成功，请登录您的邮箱进行查看' . '：' . $email,
				'btnStr'=>L('返回'),
				'url'=>'/',
				'isSuccess'=>1,
				'autoRedirectTime'=>0,
			));
		}else{
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>'ERROR：' . $response,
				'btnStr'=>L('返回'),
				'url'=>'/',
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}else if($channel == '100010'){
		$response = Tool::SendSmsPost(trim($DRPay['submit_gateway']) . '/pub/pay/records/gateway',$postData);
		if($response == 'OK'){
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>'支付信息发送成功，请注意查收手机短信' . '：' . $mobile,
				'btnStr'=>L('返回'),
				'url'=>'/',
				'isSuccess'=>1,
				'autoRedirectTime'=>0,
			));
		}else{
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>'ERROR：' . $response,
				'btnStr'=>L('返回'),
				'url'=>'/',
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
	}
		
	echo '<!DOCTYPE HTML>
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>' , L('支付') , '</title>
	</head>
	<body>';
	
	echo '<form id="payform" name="payform" method="post" action="' . trim($DRPay['submit_gateway']) . '/pub/pay/records/gateway">';
	
	echo '<input type="hidden" name="access_token" value="' . trim($DRPay['PayKey']) . '">';
	echo '<input type="hidden" name="ciphertext" value="' . $ciphertext . '">';

	echo '</form>';
	
	echo '<script>document.getElementById("payform").submit();</script>';
	
	echo '</body>';
	echo '</html>';
}else{
	/*
	可用通道：
	100000  / (数字货币支付/500.00- 1000000.00/CNY)  customername、email
	100008  / (银联国际(Email)/1.00- 20000.00/CNY)   email
	100010  / (银联国际(SMS)/1.00- 20000.00/CNY)    mobile
	*/

	echo '<div class="form-group row">
				<label class="col-sm-2">' , L("支付通道") , '：</label>
				<div class="col-sm-8 mt-sm-1">
					<div class="radio radio-info radio-inline">
						<input type="radio" value="100008" name="channel" id="channel1" checked="checked">
						<label for="channel1" class="ttt">UNIONPAY-Email</label>
					</div>
					<div class="radio radio-info radio-inline">
						<input type="radio" value="100010" name="channel" id="channel2">
						<label for="channel2" class="ttt">UNIONPAY-SMS</label>
					</div>
					<div class="radio radio-info radio-inline">
						<input type="radio" value="100000" name="channel" id="channel3">
						<label for="channel3" class="ttt">UNIONPAY</label>
					</div>
				</div>
			</div>';
	
	echo '<div class="form-group row">
				<label class="col-sm-2">' , L("姓名") , '：</label>
				<div class="col-sm-8">
					<input name="fullname" type="text" class="form-control" value="' , $DRAdmin['realname'] , '" placeholder="">
				</div>
			</div>';

	echo '<div class="form-group row">
			<label class="col-sm-2">' , L("Email") , '：</label>
			<div class="col-sm-8">
				<input name="email" type="text" class="form-control" value="' , $DRAdmin['email'] , '" placeholder="">
			</div>
		</div>';
		
	echo '<div class="form-group row">
			<label class="col-sm-2">' , L("手机号") , '：</label>
			<div class="col-sm-8">
				<input name="mobile" type="text" class="form-control" value="' , $DRAdmin['phone'] , '" placeholder="">
			</div>
		</div>';

}