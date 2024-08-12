<?php
$LoadCSSArr = array();
require_once('header.php');

$oid = FGetStr('oid');

if(strlen($oid) <= 0){
	$inlogin = FGetStr('inlogin');
	$inmoneytype = FGetInt('inmoneytype');
	$inmoney = floatval(FGetStr('inmoney'));
	
	$map = "where a.member_id = '{$DRAdmin['id']}' and a.mtserver = '{$DRAdmin['server_id']}' and b.status = 1 and a.status > 0 and a.loginid = '{$inlogin}'";
	$DRInlogin = $DB->getDRow("select a.*,b.nickname,b.phone,b.email from t_member_mtlogin a inner join t_member b on a.member_id=b.id {$map}");
	if(!$DRInlogin){
		FJS_AB(L('未找到您的入金帐号'));
	}
	
	$DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$inmoneytype}') a left join t_pay_currency b on a.f_currencyId = b.id");
	if(!$DRPay){
		FJS_AB(L('未找到支付方式'));
	}
	if($DRPay['f_pa'] == 'auto'){
		$autoPa = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL = '{$val['f_symbolsER']}'");
		if($autoPa){
			$DRPay['f_fixedER'] = $autoPa['BID'];
		}
	}
	if($DRPay['f_fixedER'] <= 0){
		FJS_AB(L('抱歉，汇率错误'));
	}
	
	if($inmoney <= 0){
		FJS_AB(L('支付金额不能为0'));
	}
	if ($inmoney < C("MIN_DEPOSIT_USD")) {
		FJS_AB(L("系统最低入金金额为") . " ($)" . C("MIN_DEPOSIT_USD"));
	}
	if($inmoney > floatval($DRPay['maxpaynumber'])){
		FJS_AB(L('支付金额不能超过') . ' ($)' . $DRPay['maxpaynumber']);
	}
	
	if($DRPay['f_erAlgo'] == '÷'){
		$inmoney_cal = $inmoney / $DRPay['f_fixedER'];
	}else{
		$inmoney_cal = $inmoney * $DRPay['f_fixedER'];
	}
	$inmoney_fee = round($inmoney_cal * 0, 2);
	
	if(C('DEPOSIT_IN_INTEGER') == '0'){
		//判断入金不允许小数，0=不
		$inmoney_sum = round($inmoney_cal + $inmoney_fee, 0);
	}else{
		$inmoney_sum = round($inmoney_cal + $inmoney_fee, 2);
	}
	
	if($inmoney_sum <= 0){
		FJS_AB(L('抱歉，支付总额错误'));
	}
	
	//--------------------------
	
	//创建支付订单
	$data = array();
	$data['member_id'] = $DRAdmin['id'];
	$data['bankcode'] = '';
	$data['mtid'] = $inlogin;
	$data['certificate'] = '';//汇款凭证
	$data['number'] = $inmoney;//入金（美元）
	$data['beforenumber'] = $inmoney_cal;//换算后的金额
	$data['fee'] = $inmoney_fee;//手续费
	$data['pay_id'] = $DRPay['Id'];
	
	$existpayno = false;
	do {
		$data['payno'] = date('YmdHis') . mt_rand(1000, 9999);
		
		//检测订单是否重复，重复重新生成
		$existpayno = $DB->getDRow("select * from t_inmoney where `payno` = '{$data['payno']}'");
	}while ($existpayno);
	
	$data['type'] = $DRPay['PayCode'];
	$data['create_time'] = time();
	$data['status'] = 0;
	$data['server_id'] = $DRAdmin['server_id'];
	$data['exchange'] = $DRPay['f_fixedER'];
	$data['f_erAlgo'] = $DRPay['f_erAlgo'];
	$data['f_currencyTitle'] = $DRPay['f_title'];
	$data['f_currencyPa'] = $DRPay['f_pa'];
	$data['price'] = $inmoney_sum; //实际支付价格（换算后，加了手续费）
	
	$id = $DB->insert("t_inmoney",$data);
	
	//--------------------------
	
	//如果不需要其它支付参数，直接跳转
	//if($DRPay['f_fillMorePayInfo'] <= 0){
	//	include($_SERVER['DOCUMENT_ROOT'] . '/pay/' . $DRPay['PayCode'] . '/in_post.php');
	//	exit;
	//	//FRedirect('/pay/' . $DRPay['PayCode'] . '/in_post.php?oid=' . $data['payno']);
	//}
	
	//如果还需要其它支付参数，继续往下执行（继续执行前，为防止客户不断刷新创建新的订单号，先跳到订单号页）
	FRedirect('?clause=step2&oid=' . $data['payno']);
}else{
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
}

/*$postFile = $_SERVER["DOCUMENT_ROOT"] . '/pay/' . $DRPay['PayCode'] . '/in_post.php';
if(!file_exists($postFile)){
	FJS_AB(L('错误：支付接口POST文件不存在'));
}*/


/*
$loginid = $this->_post('inlogin');
        $type = $this->_post('inmoneytype');
        $pay_number = M('Pay')->where(array('PayCode' => $type))->getField('number_rmb');
        if ($pay_number) {
            $number = round(trim($this->_post('inmoney_m')), 2); //美元
            $inmoneyrmb = $this->_post('inmoney_rmb'); //人民币
        } else {
            $number = round(trim($this->_post('inmoney')), 2);
            $inmoneyrmb = $this->_post('inmoneyrmb');
        }
        $pay_id = $this->_post('pay_id');
        if (!$type) {
            $this->error(L("请选择付款类型"));
        }
        $allowpay = $this->check_access($loginid, $type, session('user.mt4dbname'), session('user.serverid'));
        if (!$allowpay) {
            $this->error(L("请选择正确的付款类型") . '!');
        }
        if (is_int($number) && is_float($number)) {
            $this->error(L("请选择入金金额") . '！');
        }
        if (floatval($number) <= 0)
            $this->error(L("入金金额必须为正数") . "！" . $number);
//        if (floatval($inmoneyrmb) <= 0)
//            $this->error(L("入金金额RMB必须为正数") . "！");

        if (!$loginid) {
            $this->error(L("请选择入金帐号") . '！');
        }
        if (floatval($number) < C("MIN_DEPOSIT_USD")) {
            $this->error(L("系统最低入金金额为") . "：" . C("MIN_DEPOSIT_USD"));
        }
        if (floatval($number) > C("MAX_DEPOSIT_INMONEY") && C("MAX_DEPOSIT_INMONEY") > 0) {
            $this->error(L("系统最高入金金额为") . "：" . C("MAX_DEPOSIT_INMONEY"));
        }
        //计算手续费
        $fee = $this->getinfee($number);
        if ($fee >= $number) {
            $this->error(L("入金金额不正确最少为") . '：' . ($number + $fee));
        }

        //入金限额start

        //如果设置了全部账号入金限额
        $limit_all = M('pay_limits')->where(array('loginid' => '0', 'server_id' => session('user.serverid'), 'status' => '1'))->find();
        //如果设置了单个账号入金限额
        $limits = M('pay_limits')->where(array('loginid' => $loginid, 'server_id' => session('user.serverid'), 'status' => '1'))->find();

        if ($limit_all && $limits) { // 都有设置优先账号设置的来计算
            //净入金限额判断
            if ($limits['total'] && $limits['total'] != 0) {  //单个账号有设置净入金
                $total = $limits['total'];
            } else {
                if ($limit_all['total'] && $limit_all['total'] != 0) { //整个账号
                    $total = $limit_all['total'];
                }
            }
            //单日入金限额判断
            if ($limits['total_day'] && $limits['total_day'] != 0) {
                $total_day = $limits['total_day'];
            } else {
                if ($limit_all['total_day'] && $limit_all['total_day'] != 0) {
                    $total_day = $limit_all['total_day'];
                }
            }
            //单账号单支付接口单日限额
            if (!empty($limits['pay_total_day'])) {
                $pay_total_day = unserialize($limits['pay_total_day']);
            } else {
                if (!empty($limit_all['pay_total_day'])) {
                    $pay_total_day = unserialize($limit_all['pay_total_day']);
                }
            }
        }

        if (!$limit_all && $limits) {
            if ($limits['total'] && $limits['total'] != 0) {  //单个账号有设置净入金
                $total = $limits['total'];
            }
            if ($limits['total_day'] && $limits['total_day'] != 0) {
                $total_day = $limits['total_day'];
            }
            if (!empty($limits['pay_total_day'])) {
                $pay_total_day = unserialize($limits['pay_total_day']);
            }
        }

        if ($limit_all && !$limits) { // 都有设置优先账号设置的来计算
            if ($limit_all['total'] && $limit_all['total'] != 0) { //整个账号
                $total = $limit_all['total'];
            }
            if ($limit_all['total_day'] && $limit_all['total_day'] != 0) {
                $total_day = $limit_all['total_day'];
            }
            if (!empty($limit_all['pay_total_day'])) {
                $pay_total_day = unserialize($limit_all['pay_total_day']);
            }
        }

        if ($total) {
            $reportModel = new ReportModel(session('user.mt4dbname'));
            $total_number_in = $reportModel->where(array('CMD' => 6, 'PROFIT' => array('gt', 0), 'LOGIN' => $loginid))->getField("'vtotal',round(sum(PROFIT),2) as PROFIT", true); //入金额 包括返佣
            $total_number_out = $reportModel->where(array('CMD' => 6, 'PROFIT' => array('lt', 0), 'LOGIN' => $loginid))->getField("'vtotal',round(sum(PROFIT),2) as PROFIT", true); //出金额
            $total_number['vtotal']['PROFIT'] = $total_number_in['vtotal']['PROFIT'] + $total_number_out['vtotal']['PROFIT']; // 净入金
            if ($total_number['vtotal']['PROFIT'] >= $total) {
                $this->error(L("此账户已达到入金限额，暂无法办理入金。如有疑问或申请提高限额请发邮件到" . C("EMAIL") . "咨询"));
            }
            $a = $total_number['vtotal']['PROFIT'] + $number - $total;
            if ($a > 0) {
                $b = $total - $total_number['vtotal']['PROFIT'];
                $this->error(L("此账户已超出净入金限额") . L("还能入金") . ": $" . $b);
            }
        }
        $re['mtid'] = $loginid;
        $re['status'] = array('not in', '1');
        $re['server_id'] = session('user.serverid');
        $re['create_time'] = array(array('EGT', strtotime(date("Y-m-d 00:00:00"))), array('ELT', strtotime(date("Y-m-d 23:59:59"))));
        if ($total_day) {
            $number_day = M('inmoney')->where($re)->getField("'vtotal',round(sum(number),2) as number", true);
            if ($number_day['vtotal']['number'] >= $total_day) {
                $this->error(L("此账户已超出单日入金限额"));
            }
            $a = $number_day['vtotal']['number'] + $number - $total_day;
            if ($a > 0) {
                $b = $total_day - $number_day['vtotal']['number'];
                $this->error(L("此账户已超出单日入金限额") . L("当日还能入金") . ": $" . $b);
            }
        }

        if ($pay_total_day) {
            foreach ($pay_total_day as $key => $value) {
                $code = M('pay')->where(array('Id' => $key, 'Status' => 1))->getField('PayCode');
                $re['type'] = $code;
                $pay_number_day = M('inmoney')->where($re)->getField("'vtotal',round(sum(number),2) as number", true);
                if ($type == $code && $pay_number_day['vtotal']['number'] >= $value) {
                    $this->error(L("此账户已超出该通道单日入金限额"));
                }
                if ($type == $code && $pay_number_day['vtotal']['number'] + $number > $value) {
                    $c = $value - $pay_number_day['vtotal']['number'];
                    $this->error(L("此账户已超出该通道入金限额") . L("当日该通道还能入金") . ": $" . $c);
                }
            }
        }

        //入金限额end

        $mtwhere['loginid'] = $loginid;
        $mtwhere['status'] = 1;

        $mtloginuser = D('member_mtlogin')->where($mtwhere)->find();
        if (!$mtloginuser) {
            $this->error(L("当前账户未绑定") . '！');
        } else if ($mtloginuser['member_id'] != session('user.id')) {
            $this->error($loginid . L("账户不属于当前登录账户，请刷新重新入金") . '！');
        }

        $data['member_id'] = session('user.id');
        $data['bankcode'] = $this->_post('bankid');
        $data['mtid'] = $loginid;
        $data['certificate'] = $this->_post('certificate');
        $data['number'] = $number;
        $data['afternumber'] = round($number - $fee, 2);
        $data['fee'] = round($fee, 2);
        $data['pay_id'] = $pay_id;
        $paywhere['PayCode'] = $type;
        $paywhere['server_id'] = session('user.serverid');
        $pay = D('pay')->where($paywhere)->find();
        if ($pay['maxpaynumber'] > 0 && $number > round($pay['maxpaynumber'], 2)) {
            $this->error(L("系统最高入金金额为") . "：" . $pay['maxpaynumber']);
        }
        $existpayno = false;
        do {
            if ($type == 'sxpay' || $type == 'sxwxpay' || $type == 'sxalipay' || $type == 'SxQuickPay') {
                $data['payno'] = date('Ymd') . '-' . $pay['PayKey'] . '-' . date('His') . rand(100000, 999999);
            } else if ($type == 'WanjioPay') {
                $data['payno'] = $pay['PayKey'] . date('YmdHis') . rand(100000, 999999);
            } else if ($type == 'GtcPay') {
                $data['payno'] = $pay['PartnerKey'] . date('YmdHis') . rand(100000, 999999);
            } else if ($type == 'ZhongNPay') {
                $data['payno'] = 'ZN' . date('YmdHis') . rand(100000, 999999);
            } else if ($type == 'UosPay') {
                $data['payno'] = date('YmdHi') . rand(1000, 9999);
            } else
                $data['payno'] = date('YmdHis') . rand(100000, 999999);
//检测订单是否重复，重复重新生成	
            $paynowhere['payno'] = $data['payno'];
            $existpayno = D("inmoney")->where($paynowhere)->find();
        }while ($existpayno);

        $data['type'] = $type;
        $data['create_time'] = time();
        $data['status'] = 0;
        $data['server_id'] = session('user.serverid');

        $exchangerate = M('ConfigServer')->where(array('configname' => 'EXCHANGERATE', 'server_id' => $data['server_id']))->field('configvalue as value')->find();
        if (!$exchangerate) {
            $this->error(L("支付汇率未知，不能发起支付") . '！');
        }

        $data['exchange'] = $exchangerate['value'];
        if ($pay_number) {
            $data['price'] = $this->_post('inmoney_rmb');
        } else {
            if(C('DEPOSIT_IN_INTEGER') == '0'){ //判断入金不允许小数
                $data['price'] = round(floatval($data['exchange']) * floatval($number), 0); //实际支付价格
            }else{
                $data['price'] = round(floatval($data['exchange']) * floatval($number), 2); //实际支付价格
            } 
        }
        if ($type == 'GsdPay' || $type == 'YiYKPay') { //单独Gsd支付 易亿卡支付时不能带小数点（3方控制的）
            $data['price'] = round(floatval($data['exchange']) * floatval($number), 0); //实际支付价格
        }

//计算收费金额
        if ($fee > 0) {
            $data['number'] = round($number - $fee, 2);
        }

        if ($data['exchange'] <= 0) {
            $this->error(L("网络异常") . '！');
        }

//额外参数，必须以extend开头
        $extend_param = array();
        foreach ($_POST as $key => $value) {
            $pos = strpos($key, 'extend');
            if ($pos === 0) {
                $extend_param[$key] = $value;
            }
        }
        if ($extend_param)
            $data['extend_param'] = json_encode($extend_param);

        $id = D("inmoney")->add($data);

        if ($id > 0) {
            $data['id'] = $id;
            if ($type == 'remit') {//线下汇款
                $result = api('gold://Mail/sendDepositNotify', array(session('user.nickname'), $loginid, $number, $type, session('user.serverid')));
//模拟仓不需要审核直接入金
                $mtserver = M("mt4_server")->where(array("id" => session('user.serverid')))->find();

                if ($mtserver['real'] == 0) {
                    D("Member")->autoinmoney($id); //模拟仓自动入金
                    $this->success(L("入金成功"));
                    exit;
                } else {
//存在固定支付二维码展示二维码
                    if ($pay['payimgcode']) {
                        $this->jumpUrl = U("deposit/addmoney");
                        $this->success(L("申请成功，请扫描下面二维码进行支付RMB") . $data['price'] . ',' . L("支付完成后请上传支付凭证") . '。<br/><img src="' . $pay['payimgcode'] . '" style="width:280px;hight:280px;vertical-align:middle">');
                    } else {
                        $this->success(L("申请成功，请上传凭证等待审核"));
                    }

                    exit;
                }
            }
            $this->redirect('direct2pay?id=' . $id);
        }
*/
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
									echo L('支付') , ' - ' , L('确认支付');
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
                                        <form class="form-horizontal" action="/pay/<?php echo strlen($DRPay['f_payFolder']) ? $DRPay['f_payFolder'] : $DRPay['PayCode']; ?>/in_post.php?clause=topay&oid=<?php echo $oid;?>" target="_blank" method="post">
                                        	<input type="hidden" name="clause" value="step2">
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("入金帐号");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['mtid'];?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("支付方式");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $DRPay['PayName'];?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("入金金额");?>（<?php echo L("美元");?>）：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['number'] * 1;?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("当前汇率");?>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['exchange'] * 1;?></div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2"><?php echo L("支付金额");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['beforenumber'] * 1;?></div>
                                            </div>
                                            <div class="form-group row" style="display:none;">
                                                <label class="col-sm-2 mt-sm-1"><?php echo L("手续费");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8"><?php echo $DRInfo['fee'] * 1;?></div>
                                            </div>
                                            <div class="form-group row" style="display:none;">
                                                <label class="col-sm-2 mt-sm-1"><?php echo L("总共需支付");?><span class="pay_pa"></span>：</label>
                                                <div class="col-sm-8"><?php echo $DRInfo['price'] * 1;?></div>
                                            </div>
                                            <?php
											if(strlen($DRPay['f_payFolder'])){
												include($_SERVER['DOCUMENT_ROOT'] . '/pay/' . $DRPay['f_payFolder'] . '/in_post.php');
											}else{
                                            	include($_SERVER['DOCUMENT_ROOT'] . '/pay/' . $DRPay['PayCode'] . '/in_post.php');
											}
											?>
                                            <div class="form-group row">
                                                <label class="col-sm-2 control-label">&nbsp;</label>
                                                <div class="col-sm-8">
                                                	<button type="submit" class="btn btn-primary"><?php echo L("去支付");?></button>
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
