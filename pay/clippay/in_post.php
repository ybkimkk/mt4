<?php
//第一步是，如果有需要补充资料，则在第一步里输出表单项

if($_GET['clause'] == 'topay'){
	//第二步是发起支付
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
	
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
	
	//---------------------------------------
	
	$fullname = FPostStr('fullname');
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
	//$DRInfo['price'] = 0.1;
	
	$payInfo = array(
		'merchantId' => trim($DRPay['PayKey']),
		'outTradeNo' => $DRInfo['payno'],
		'name' => $fullname,
		'email' => $email,
		'channel' => $channel,
		'currency' => 'CNY',
		'amount' => $DRInfo['price'],
		'notifyUrl'=> (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_s2s_' . trim($DRPay['PayCode']) . '.html',
		'returnUrl'=> (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_back_' . trim($DRPay['PayCode']) . '.html',
		'timestamp'=>time(),
	);

	ksort($payInfo);
	reset($payInfo);
	
	$signStr = '';
	foreach($payInfo as $key=>$val){
		if(strlen($val) > 0){
			$signStr .= $key . '=' . $val . '&';
		}
	}
	$signStr .= 'key=' . trim($DRPay['PaySignKey']);
	
	$payInfo['signature'] = strtoupper(md5($signStr));

	//---------------------------------------
	
function http_post_json($url, $jsonStr){
	$headers = array(
		'Content-Type:application/json',
	);
	
    $ch = curl_init();//初始化curl
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
	
	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}
	
	$html = http_post_json(trim($DRPay['submit_gateway']) . '/gateway/email-payment-request', json_encode($payInfo));
	
	$arr = json_decode($html,true);
	
	//{ "data" : { "paymentId" : "202110290000102863" }, "errorCode" : "0", "message" : "success" }
	/* 
	{
	  "errorCode" : "1001",
	  "message" : "outTradeNo_duplicated"
	}
	*/
	
	if($arr['errorCode'] == '0' && $arr['message'] == 'success'){
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
			'content'=>'ERROR：（' . $arr['errorCode'] . '）' . $arr['message'],
			'btnStr'=>L('返回'),
			'url'=>'/',
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}
	
	//echo $html;
	
	exit;
	
	echo '<!DOCTYPE HTML>
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>' , L('支付') , '</title>
	</head>
	<body>';
	
	echo '<form id="payform" name="payform" method="post" action="' . $DRPay['submit_gateway'] . '">';
	
	foreach ($payInfo as $key => $obj) {
		echo '<input type="hidden" name="' . $key . '" value="' . $obj . '">';
	}

	//echo '<div style="text-align:center;margin:50px auto;"><input type="submit" class="btn btn-primary " value="Confirm"></div>';
	
	echo '</form>';
	
	echo '<script>document.getElementById("payform").submit();</script>';
	
	echo '</body>';
	echo '</html>';
}else{

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
					<!--
					<div class="radio radio-info radio-inline">
						<input type="radio" value="ALIPAY" name="channel" id="channel2">
						<label for="channel2" class="ttt">ALIPAY</label>
					</div>
					-->
				</div>
			</div>';
			
	echo '<div class="form-group row">
			<label class="col-sm-2">' , L("Email") , '：</label>
			<div class="col-sm-8">
				<input name="email" type="text" class="form-control" value="' , $DRAdmin['email'] , '" placeholder="">
			</div>
		</div>';

}