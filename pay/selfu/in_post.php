<?php
//第一步是，如果有需要补充资料，则在第一步里输出表单项

if($_GET['clause'] == 'topay'){
	//第二步是发起支付
	require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
	
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
	
	//---------------------------------------
	
	$oid = FGetStr('oid');
	
	FRedirect('/pay/usdt_base/post.php?oid=' . $oid);
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
	*/
}