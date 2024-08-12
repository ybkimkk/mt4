<?php
//第一步是，如果有需要补充资料，则在第一步里输出表单项

if($_GET['clause'] == 'topay'){
	//第二步是发起支付
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
	
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
	
	//---------------------------------------
	
	$oid = FGetStr('oid');
	
	$DRInfo = $DB->getDRow("select * from t_inmoney where `payno` = '{$oid}'");
	if(!$DRInfo){
		FJS_AT(L('抱歉，未找到该订单'),'?');
	}
	if($DRInfo['member_id'] != $DRAdmin['id']){
		FJS_AT(L('抱歉，该订单不属于您'),'?');
	}
	if($DRInfo['status'] != 0){
		FJS_AT(L('抱歉，订单状态错误'),'?');
	}
	
	$DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$DRInfo['pay_id']}') a left join t_pay_currency b on a.f_currencyId = b.id");
	if(!$DRPay){
		FJS_AT(L('未找到支付方式'),'?');
	}
	
	//---------------------------------------
	
	$payInfo = array(
		'version' => 'v1.0',    //固定为v1.0
		'merid' => trim($DRPay['PayKey']),    //商户号
		'paytypekey' => 'ibank',    //通道类型
		'orderid' => $DRInfo['payno'],   //订单号
		'amount' => $DRInfo['price'],    //支付金额，必须格式化为小数点后2位。例如1元，应当填写为 1.00
		'callbackurl' => (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_back_' . trim($DRPay['PayCode']) . '.html',    //支付后，浏览器跳转回来所要到达的页面
		's2surl' => (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/pay_s2s_' . trim($DRPay['PayCode']) . '.html', //支付成功后，接收 支付服务器通知支付结果情况 的页面
		'bankname'=>'ACB',
	);
	
	ksort($payInfo);
	reset($payInfo);
	//2、把值串接一起
	$payInfo2Str = implode('',$payInfo);
	//3、再串上API密钥
	$payInfo2Str .= trim($DRPay['PaySignKey']);
	//4、进行32位的md5加密，结果即是签名
	$sign = md5($payInfo2Str);
	//5、签名要转换为小写字母的字符串（md5加密结果本身就是小写字母的字符串，这里不管，执行转换为小写，因为它是一个处理逻辑，适用于其它任意语言，如.NET、JAVA）
	$sign = strtolower($sign);
	
	//把签名加进发起支付的数据里
	$payInfo['sign'] = $sign;
	
	//附加数据，该数据在支付回发通知时，原样返回（回发post到s2surl）
	//请按实际需要填写，可以为空
	//若数据内容不为空，数据内容必须用base64编码；回发回来的时候，也是base64编码，请自行解码
	$payInfo['attach'] = '';//base64_encode('测试数据');
	
	//---------------------------------------
	
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
	/*
	echo '<div class="form-group row">
				<label class="col-sm-2">' , L("下拉项") , '：</label>
				<div class="col-sm-8">
					<select name="inlogin" id="inlogin" class="form-control">
						<option value="164582">164582</option>
					</select>
				</div>
			</div>';
			
	echo '<div class="form-group row">
				<label class="col-sm-2">' , L("填写项") , '：</label>
				<div class="col-sm-8 mt-sm-1">
					<div class="radio radio-info radio-inline">
						<input type="radio" feepre="0" f_eralgo="×" f_symbolser="" f_fixeder="23020.00000" f_ers="fixed" f_pa="₫" f_title="VND" id="inlineRadio_selfpay23" numberlist="" value="500" rel="500" maxpay="100000.00" name="inmoneytype" role="" lang="yn">
						<label for="inlineRadio_selfpay23" class="ttt">Thanh toán online (C)</label>
					</div>
					<div class="radio radio-info radio-inline">
						<input type="radio" feepre="0" f_eralgo="×" f_symbolser="" f_fixeder="23020.00000" f_ers="fixed" f_pa="₫" f_title="VND" id="inlineRadio_usdt1" numberlist="" value="502" rel="502" maxpay="100000.00" name="inmoneytype" role="" lang="yn">
						<label for="inlineRadio_usdt1" class="ttt">USDT-ERC20</label>
					</div>
				</div>
			</div>';
			
	echo '<div class="form-group row">
			<label class="col-sm-2">' , L("填写项") , '：</label>
			<div class="col-sm-8">
				<input name="inmoney" id="inmoney" type="text" class="form-control" value="" placeholder="请输入金金额">
			</div>
		</div>';
	*/
}