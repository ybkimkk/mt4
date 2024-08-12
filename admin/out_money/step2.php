<?php
$LoadCSSArr = array();
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

$loginid = FRequestStr('login');

$DRChk = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$loginid}' and status > 0 and member_id = '{$DRAdmin['id']}' and mtserver = '{$DRAdmin['server_id']}'");
if(!$DRChk){
	FJS_AT('出金帐号错误',FPrevUrl());
}

//-------------------------------------

$number = floatval(FRequestStr('outmoney'));
if (floatval($number) <= 0){
	FJS_AT('请填写出金金额',FPrevUrl());
}

if (floatval($number) < C("MIN_WITHDRAW_USD")) {
	FJS_AT(L("系统最低出金金额为") . "：" . C("MIN_WITHDRAW_USD"),FPrevUrl());
}

if (floatval($number) > C("MAX_WITHDRAW_USD")) {
	FJS_AT(L("出金最大是") . "：$" . C("MAX_WITHDRAW_USD"),FPrevUrl());
}

$type = FRequestStr('outmoneytype');
if (strlen($type) <= 0) {
	FJS_AT('请选择出金方式',FPrevUrl());
}

if($type == 'czmt20211212'){
	$DRPay = array('id'=>0,'f_fee'=>0,'f_fixedEROut'=>1,'f_erAlgo'=>'×');
}else{
	$type = intval($type);
	
	$DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedEROut,b.f_symbolsER,b.f_erAlgo from (select * from t_out_config where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$type}') a left join t_pay_currency b on a.f_currencyId = b.id");
	if (!$DRPay) {
		FJS_AT('出金方式未找到',FPrevUrl());
	}
	if($DRPay['f_pa'] == 'auto'){
		$autoPa = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL = '{$val['f_symbolsER']}'");
		if($autoPa){
			$DRPay['f_fixedEROut'] = $autoPa['BID'];
			//ASK
		}
	}
}

//计算手续费
$fee = $DRPay['f_fee'];
$outmoney_fee = $fee * $number;

//总的出金扣除美元
$outmoney_sum = $number + $outmoney_fee;

//汇率
$currER = $DRPay['f_fixedEROut'];

//实际到账
$outmoney_account = 0;
if($DRPay['f_erAlgo'] == '÷'){
	$outmoney_account = $number / $currER;
}else{
	$outmoney_account = $number * $currER;
}
if(C('DEPOSIT_IN_INTEGER') == '0'){
	//判断入金不允许小数，0=不允许
	$outmoney_account = intval($outmoney_account,0);
}else{
	$outmoney_account = round($outmoney_account,2);
}

//出金限额
/*
{
	$limits = $DB->getDRow("select * from t_pay_limits where loginid = '{$loginid}' and server_id = '{$DRAdmin['server_id']}' and status = 1");
	$limit_all = $DB->getDRow("select * from t_pay_limits where loginid = '0' and server_id = '{$DRAdmin['server_id']}' and status = 1");	
	if ($limits && $limit_all) {
		//单账户单日出金限额
		if ($limits['money_out_day'] && $limits['money_out_day'] != 0) {
			$out_total = $limits['money_out_day'];
		} else {
			if ($limit_all['money_out_day'] && $limit_all['money_out_day'] != 0) {
				$out_total = $limit_all['money_out_day'];
			}
		}

		//单账户单笔出金限额
		if ($limits['money_out_one'] && $limits['money_out_one'] != 0) {
			$one_total = $limits['money_out_one'];
		} else {
			if ($limit_all['money_out_one'] && $limit_all['money_out_one'] != 0) {
				$one_total = $limit_all['money_out_one'];
			}
		}
	}
	if ($limits && !$limit_all) {
		if ($limits['money_out_day'] && $limits['money_out_day'] != 0) {
			$out_total = $limits['money_out_day'];
		}
		if ($limits['money_out_one'] && $limits['money_out_one'] != 0) {
			$one_total = $limits['money_out_one'];
		}
	}	
	if (!$limits && $limit_all) {
		if ($limit_all['money_out_day'] && $limit_all['money_out_day'] != 0) {
			$out_total = $limit_all['money_out_day'];
		}
		if ($limit_all['money_out_one'] && $limit_all['money_out_one'] != 0) {
			$one_total = $limit_all['money_out_one'];
		}
	}

	if ($out_total) {
		$re['mtid'] = $loginid;
		$re['status'] = array('not in', '1');
		$re['server_id'] = $DRAdmin['server_id'];
		$re['create_time'] = array(array('EGT', strtotime(date("Y-m-d 00:00:00"))), array('ELT', strtotime(date("Y-m-d 23:59:59"))));
		$number_day = $DB->getField("select round(sum(number),2) as number from t_outmoney " . cz_where_to_str($re));
		if ($number_day >= $out_total) {
			FJS_AB('此账户已超出单日出金限额');
		}
		$a = $number_day + $number - $out_total;
		if ($a > 0) {
			$b = $out_total - $number_day;
			FJS_AB(L("您的申请出金金额已超出此账户单日出金限额") . L("当日还能出金") . ': $' . $b);
		}
	}
	if ($one_total) {
		if ($number > $one_total) {
			FJS_AB(L("您的申请出金金额过高，单次出金限额为") . ': $' . $one_total . ',' . L('请修改金额后重新提交.'));
		}
	}
}
*/

/* 验证重复提交 */
$sameout = C("DEPOSIT_OUT_SAME");
if ($sameout == 1) {
	$outcount = floatval($DB->getField("select count(*) as count1 from t_outmoney where mtid = '{$loginid}' and server_id = '{$DRAdmin['server_id']}' and member_id = '{$DRAdmin['id']}' and status in (0,8)"));
	if ($outcount > 0) {
		FJS_AT(L("您还有未审核的出金申请，请等待系统处理完成后再次提交申请"),FPrevUrl());
	}
}

/* 验证重复提交 */
$isfollow = $DB->getDRow("select * from mt4svr_config where `STATUS` = 0 and LOGINID = '{$loginid}' and SERVER_ID = '{$DRAdmin['server_id']}'");
if ($isfollow) {
	FJS_AT(L("请取消该帐号的跟单配置后再进行出金操作"),FPrevUrl());
}

//需要接口调用实时的余额
$mtuser = getuser($loginid);
if (!$mtuser) {
	FJS_AT(L("当前MT用户不存在"),FPrevUrl());
}

$server = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
if (!$server){
	FJS_AT(L("mt服务器不存在"),FPrevUrl());
}

$mtapi = new MtApiModel($server['ver']);
$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
$retarry = $mtapi->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
if ($retarry['ret'] != 0) {
	FJS_AT(L($retarry['info']),FPrevUrl());
}
/* 实时查询客户余额 */
$mybalance = $mtapi->GetValidMoney($loginid, 0); //我的MT4余额
$mybalance = round($mybalance['ret'], 2);

//查询未审核出金金额
$outamount = floatval($DB->getField("select sum(number) as sum1 from t_outmoney where mtid = '{$loginid}' and member_id = '{$DRAdmin['id']}' and status = 0"));

$lesseqamount = round($mybalance - $outamount, 2);
if ($lesseqamount < 0 || $lesseqamount < round($outmoney_sum, 2)) {
	$retmsg = L("可用余额不足,无法完成出金。");
	if (floatval($outamount) > 0){
		$retmsg = $retmsg . L("原因：有待审核出金未处理。");
	}

	FJS_AT($retmsg,FPrevUrl());
}
$reportModel = new ReportModel($DRAdmin['mt4dbname']);
if (C('HAVE_ORDER_CHECK_OUT') != '1') {//等于1 不限制出金条件，否则需要检测未平仓订单
	$orders = $reportModel->queryUnClosedOrders($loginid, $server['mt4_server']);
	if (count($orders) > 0){
		FJS_AT(L("您的账号") . $loginid . L("有未平仓订单，不能完成出金，请平仓后再次申请出金"),FPrevUrl());
	}
}



//这个是确认后的保存。如果没有传递参数，则会到达确认页
//czmt20211212 这个出金方式，不需要确认，直达了
if(FRequestInt('confirmsave') >= 1 || $type == 'czmt20211212'){
	//线下
	if(FRequestInt('confirmsave') == 1){
		$forwordname1 = FPostStr('forwordname1');
		if (!$forwordname1) {
			FJS_AT(L("请填写收款人姓名"),FPrevUrl());
		}
		
		$bankname1 = FPostStr('bankname1');
		if (!$bankname1) {
			FJS_AT(L("请填写收款银行名称"),FPrevUrl());
		}
		
		$bankaccount1 = FPostStr('bankaccount1');
		if (!$bankaccount1) {
			FJS_AT(L("请填写收款银行帐号"),FPrevUrl());
		}
		
		$data['forwordname'] = $forwordname1;
		$data['bankname'] = $bankname1;
		$data['bankaccount'] = $bankaccount1;
	}

	//USDT
	if(FRequestInt('confirmsave') == 2){
		$forwordname1 = FPostStr('forwordname1');
		if (!$forwordname1) {
			//FJS_AT(L("请填写收款人姓名"));
		}
		
		$bankname1 = FPostStr('bankname1');
		if (!$bankname1) {
			//FJS_AT(L("请填写收款银行名称"));
		}
		
		$bankaccount1 = FPostStr('bankaccount1');
		if (!$bankaccount1) {
			FJS_AT(L("请填写收款地址"),FPrevUrl());
		}
		
		$data['forwordname'] = $forwordname1;
		$data['bankname'] = $bankname1;
		$data['bankaccount'] = $bankaccount1;
	}
	
	//转给其它mt帐号
	if($type == 'czmt20211212'){
		$mtlogin = FRequestInt('mtlogin');
		$mtlogin2 = FRequestInt('mtlogin2');
		if ($mtlogin <= 0) {
			FJS_AT(L("请输入收款MT帐号"),FPrevUrl());
		}
		//if ($mtlogin == $loginid) {
		//	FJS_AT(L("不能转账给自己"));
		//}
		if ($mtlogin == $loginid) {
			FJS_AT(L("不能自己转账给自己同一个MT账号"),FPrevUrl());
		}
		if ($mtlogin != $mtlogin2) {
			FJS_AT(L("确认收款账号不正确，请重新填写"),FPrevUrl());
		}
		
		$DR1 = $DB->getDRow("select * from t_member_mtlogin where loginid = '{$mtlogin}' and status = 1");
		if(!$DR1){
			FJS_AT(L('转入账户不存在') . '(001)',FPrevUrl());
		}
		
		$DR2 = $DB->getDRow("select * from t_member where id = '{$DR1['member_id']}'");
		if(!$DR2){
			FJS_AT(L('转入账户不存在') . '(002)',FPrevUrl());
		}
		
		if(!chk_mt_is_root_agent($DRAdmin['id'],$DR2['id'])){
			FJS_AT(L('转入账户与您不属于同一根代理'),FPrevUrl());
		}
		
		$data['forwordmtlogin'] = $mtlogin;
	}	
	 
	/*if ($type == 2) {
		//2=电汇
		if (!$forwordname2) {
			$this->error(L("请填写收款人姓名"));
		}
		if (!$bankname2) {
			$this->error(L("请填写收款银行名称"));
		}
		if (!$bankaccount2) {
			$this->error(L("请填写收款银行帐号"));
		}
		if ($this->_post('swiftCode2')) {
			$data['swiftCode'] = $this->_post('swiftCode2');
		}
		$data['forwordname'] = $forwordname2;
		$data['bankname'] = $bankname2;
		$data['bankaccount'] = $bankaccount2;
	} else if ($type == 3) {
		//3=MT转账
		if (!$mtlogin) {
			$this->error(L("请输入收款MT帐号"));
		}
		if ($mtlogin == $loginid) {
			$this->error(L("不能转账给自己"));
		}
		if ($mtlogin != $mtlogin2) {
			$this->error(L("确认收款账号不正确，请重新填写"));
		}
		if ($loginid == $mtlogin) {
			$this->error(L("不能自己转账给自己同一个MT账号"));
		}
		$data['forwordmtlogin'] = $mtlogin;
	
		$depositModel = new DepositModel($DRAdmin['mt4dbname']);
		$mtuser = getuser($mtlogin);
		$mtuserout = getuser($loginid);
		if (!$mtuser)
			$this->error(L("转入账户不存在"));
	}*/
				
	$data['exchange'] = $currER;
	$data['f_pa'] = $DRPay['f_pa'];
	$data['amount'] = $outmoney_account; //实际支付价格
	
	$data['member_id'] = $DRAdmin['id'];
	$data['mtid'] = $loginid;
	$data['number'] = $number;
	$data['type'] = $type;
	$data['create_time'] = time();
	
	$data['server_id'] = $DRAdmin['server_id'];
	$data['fee'] = $outmoney_fee;
	$data['beforenumber'] = $number;
		
	$retid = $DB->insert("t_outmoney",$data);
	if (!$retid) {
		FJS_AT(L("保存出金失败！"),FPrevUrl());
	}
	
	$msg = "出金申请成功，等待平台处理！";
	$outmoneytime = C('OUT_MONEY_TIME');
	if ($outmoneytime == '0' || $type == 'czmt20211212') {
		//出金扣账时机 = 申请时
		$updata = array();
		$updata['status'] = 8;
		try {
			$outid = $mtapi->balance($loginid, -$data['number'], "Withdraw maxib#" . $retid, 0); //MT4出金
			$outinfo = $outid['info'];
			$outid = $outid['ret'];
		} catch (Exception $e) {
			$updata['status'] = 0;
			//print_r($e);
			$msg = "出金审核中";
		}
		if ($outid <= 0 && $outid != '-88') {//失败
			$usermsg = L("出金申请失败") . "！" . $msg;
			$msg = L("出金申请失败！错误信息") . "：" . $outinfo;
	
			$updata['status'] = 0;
		} else {
			$updata['outid'] = $outid;
		}
		$map['id'] = $retid;
		$updata['adminidd'] = $DRAdmin['id'];
		$updata['content'] = $msg;
		$id = $DB->update("t_outmoney",$updata,cz_where_to_str($map));
		if ($outid == '-88') {//入金失败
			FJS_AT(L("重复出金"),'out_money.php');
		}
	}
	
	//这个支付方式，直接成功：扣钱、加钱
	if($type == 'czmt20211212'){
		$updata = array();
		$fromcomment = "Deposit ib#" . $retid . " from#" . $loginid;
		$fromticket = $reportModel->getTicketByComment('6', $fromcomment, $server['ver'], $mtlogin);
		if ($fromticket) {
			$inids = $fromticket['TICKET'];
		} else {
			$inids = $mtapi->balance($mtlogin, $data['number'], $fromcomment, 0); //MT4入金
			$inids = $inids['ret'];
		}
		if ($inids > 0) {
			$updata['status'] = 9;
			$updata['visit_time'] = time();
			$msg = "出金成功，并转入账户" . $mtlogin;
		} else {
			$msg = "出金成功，转入" . $mtlogin . '失败，错误代码:' . $inids . '，请在MT4后台手动入金';
		}
		$updata['content'] = $msg;
		$id = $DB->update("t_outmoney",$updata,cz_where_to_str($map));

		if ($inids == '-88') {//入金失败
			FJS_AT(L("重复入金"),'out_money.php');
		}
	}

	
	
	//查询MT转账是不是本人是本人直接成功
	//$outuser = M("MemberMtlogin")->where(array("loginid" => $this->_post('mtlogin'), 'status' => 1, 'mtserver' => $server['id']))->find();
	/*if ($id && $type == 3 && $outuser['member_id'] == $mtlogin['member_id']) {
		$fromcomment = "Deposit ib#" . $retid . " from#" . $loginid;
		$fromticket = $reportModel->getTicketByComment('6', $fromcomment, $server['ver'], $this->_post('mtlogin'));
		if ($fromticket) {
			$inids = $fromticket['TICKET'];
		} else {
			$inids = $mtapi->balance($this->_post('mtlogin'), $data['number'], $fromcomment, 0); //MT4出金
			$inids = $inids['ret'];
		}
		if ($inids > 0) {
			$updata['status'] = 9;
			$updata['visit_time'] = time();
			$msg = "出金成功，并转入账户" . $info['forwordmtlogin'];
		} else {
			$msg = "出金成功，转入" . $info['forwordmtlogin'] . '失败，错误代码:' . $inids . '，请在MT4后台手动入金';
		}
		$updata['content'] = $msg;
		$id = D("Outmoney")->where($map)->save($updata);
	
		if ($outid == '-88') {//入金失败
			$this->error(L("重复出金"));
		}
	}*/
	if ($id) {
		//$result = api('gold://Mail/sendWithdrawNotify', array($DRAdmin['nickname'], $loginid, $number, $DRAdmin['server_id'], $retid));
	}
	
	FJS_AT($msg,'?');
}

//会员银行卡信息
$bank_a = array();
$bank = $DB->getDTable("select * from t_member where id = '{$DRAdmin['id']}' and server_id = '{$DRAdmin['server_id']}' and status = 1");
if ($bank && $bank[0]['accountNum']) {	
	$bank_a[0]['id'] = $bank[0]['id'];
	$bank_a[0]['bankCard'] = $bank[0]['bankCard'];
	$bank_a[0]['bankName'] = $bank[0]['bankName'];
	$bank_a[0]['swiftCode'] = $bank[0]['swiftCode'];
	$bank_a[0]['accountName'] = $bank[0]['accountName'];
	$bank_a[0]['accountNum'] = $bank[0]['accountNum'];
}
$bank_b = $DB->getDTable("select * from t_bankcode where server_id = '{$DRAdmin['server_id']}' and member_id = '{$DRAdmin['id']}' and status = 1");
$bank_name = array_merge($bank_a ? $bank_a : array(), $bank_b ? $bank_b : array());
?>

<style>
@media screen and (min-width:768px) {
	.form-horizontal .col-sm-2 {padding-top: 7px;margin-bottom: 0;text-align: right;}
}
</style>

					<div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php 
									echo L('支付 - 确认支付');
									echo getCurrMt4ServerName();
									?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal" action="?clause=step2&login=<?php echo $loginid;?>&outmoney=<?php echo $number;?>&outmoneytype=<?php echo $type;?>" method="post">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出金帐号");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $loginid;?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出金金额");?>（<?php echo L("美元");?>）：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $number;?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("出金方式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo '（' , $DRPay['f_title'] , ' ' , $DRPay['f_pa'] , '），' , L('渠道') , '：' , L($DRPay['PayName']);?></div>
                                            </div>
                                            <?php
                                            if($outmoney_fee > 0){
												?>
												<div class="form-group row">
													<label class="col-sm-2"><?php echo L("手续费");?>（<?php echo L("美元");?>）：</label>
													<div class="col-sm-8 mt-sm-1"><?php echo $outmoney_fee * 1;?></div>
												</div>
												<div class="form-group row">
													<label class="col-sm-2"><?php echo L("一共扣除");?>（<?php echo L("美元");?>）：</label>
													<div class="col-sm-8 mt-sm-1"><?php echo $outmoney_sum * 1;?></div>
												</div>
												<?php
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("当前汇率");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $currER * 1;?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("到账金额");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $outmoney_account * 1;?></div>
                                            </div>
                                            <?php
                                            include($_SERVER['DOCUMENT_ROOT'] . '/out/' . $DRPay['PayCode'] . '/out_post.php');
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("确认");?></button>
                                                    <button onclick="window.history.back()" type="button" class="btn btn-light"><?php echo L("返回");?></button>
                                                </div>
                                            </div>
                                        </form>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card-->
                            </div> <!-- end col -->
                        </div>

					</div>






		<?php
        require_once('footer.php');
        ?>

    </body>
</html>