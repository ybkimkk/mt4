<?php
$LoadCSSArr = array();
require_once('header.php');

$oid = FGetStr('oid');

if (strlen($oid) <= 0) {
    $inlogin = FGetStr('inlogin');
    $inmoneytype = FGetInt('inmoneytype');
    $inmoney = floatval(FGetStr('inmoney'));

    $map = "where a.member_id = '{$DRAdmin['id']}' and a.mtserver = '{$DRAdmin['server_id']}' and b.status = 1 and a.status > 0 and a.loginid = '{$inlogin}'";
    $DRInlogin = $DB->getDRow("select a.*,b.nickname,b.phone,b.email from t_member_mtlogin a inner join t_member b on a.member_id=b.id {$map}");
    if (!$DRInlogin) {
        FJS_AB(L('未找到您的入金帐号'));
    }

    $DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$inmoneytype}') a left join t_pay_currency b on a.f_currencyId = b.id");

    if (!$DRPay) {
        FJS_AB(L('未找到支付方式'));
    }
    if ($DRPay['f_pa'] == 'auto') {
        $autoPa = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".mt4_prices where SYMBOL = '{$val['f_symbolsER']}'");
        if ($autoPa) {
            $DRPay['f_fixedER'] = $autoPa['BID'];
        }
    }
    if ($DRPay['f_fixedER'] <= 0) {
        FJS_AB(L('抱歉，汇率错误'));
    }

    if ($inmoney <= 0) {
        FJS_AB(L('支付金额不能为0'));
    }
    if ($inmoney < C("MIN_DEPOSIT_USD")) {
        FJS_AB(L("系统最低入金金额为") . " ($)" . C("MIN_DEPOSIT_USD"));
    }
    if ($inmoney > floatval($DRPay['maxpaynumber'])) {
        FJS_AB(L('支付金额不能超过') . ' ($)' . $DRPay['maxpaynumber']);
    }

    if ($DRPay['f_erAlgo'] == '÷') {
        $inmoney_cal = $inmoney / $DRPay['f_fixedER'];
    } else {
        $inmoney_cal = $inmoney * $DRPay['f_fixedER'];
    }
    $inmoney_fee = round($inmoney_cal * 0, 2);

    if (C('DEPOSIT_IN_INTEGER') == '0') {
        //判断入金不允许小数，0=不
        $inmoney_sum = round($inmoney_cal + $inmoney_fee, 0);
    } else {
        $inmoney_sum = round($inmoney_cal + $inmoney_fee, 2);
    }

    if ($inmoney_sum <= 0) {
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
    } while ($existpayno);

    $data['type'] = $DRPay['PayCode'];
    $data['create_time'] = time();
    $data['status'] = 0;
    $data['server_id'] = $DRAdmin['server_id'];
    $data['exchange'] = $DRPay['f_fixedER'];
    $data['f_erAlgo'] = $DRPay['f_erAlgo'];
    $data['f_currencyTitle'] = $DRPay['f_title'];
    $data['f_currencyPa'] = $DRPay['f_pa'];
    $data['price'] = $inmoney_sum; //实际支付价格（换算后，加了手续费）
    $data['pay_currency_id'] = floatval(FGetStr('currency_id'));
    $id = $DB->insert("t_inmoney", $data);

    //--------------------------

    //如果不需要其它支付参数，直接跳转
    //if($DRPay['f_fillMorePayInfo'] <= 0){
    //	include($_SERVER['DOCUMENT_ROOT'] . '/pay/' . $DRPay['PayCode'] . '/in_post.php');
    //	exit;
    //	//FRedirect('/pay/' . $DRPay['PayCode'] . '/in_post.php?oid=' . $data['payno']);
    //}

    //如果还需要其它支付参数，继续往下执行（继续执行前，为防止客户不断刷新创建新的订单号，先跳到订单号页）
    FRedirect('?clause=step2&oid=' . $data['payno']);
} else {
    $DRInfo = $DB->getDRow("select * from t_inmoney where `payno` = '{$oid}'");
    if (!$DRInfo) {
        FJS_AT(L('抱歉，未找到该订单'), '?');
    }
    if ($DRInfo['member_id'] != $DRAdmin['id']) {
        FJS_AT(L('抱歉，该订单不属于您'), '?');
    }
    if ($DRInfo['status'] != 0) {
        FJS_AT(L('抱歉，订单状态错误'), '?');
    }

    $DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$DRInfo['pay_id']}') a left join t_pay_currency b on a.f_currencyId = b.id");
    if (!$DRPay) {
        FJS_AT(L('未找到支付方式'), '?');
    }
}
?>

<style>
    @media screen and (min-width: 768px) {
        .form-horizontal .col-sm-2 {
            padding-top: 7px;
            margin-bottom: 0;
            text-align: right;
        }
    }
</style>

<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title"><?php
                    echo L('支付'), ' - ', L('确认支付');
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
                    <form class="form-horizontal"
                          action="/pay/<?php echo strlen($DRPay['f_payFolder']) ? $DRPay['f_payFolder'] : $DRPay['PayCode']; ?>/in_post.php?clause=topay&oid=<?php echo $oid; ?>"
                          target="_blank" method="post">
                        <input type="hidden" name="clause" value="step2">
                        <div class="form-group row">
                            <label class="col-sm-2"><?php echo L("入金帐号"); ?>：</label>
                            <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['mtid']; ?></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2"><?php echo L("支付方式"); ?>：</label>
                            <div class="col-sm-8 mt-sm-1"><?php echo $DRPay['PayName']; ?></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2"><?php echo L("入金金额"); ?>：</label>
                            <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['number'] * 1; ?></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2"><?php echo L("币种"); ?>：</label>
                            <div class="col-sm-8 mt-sm-1"><?php
                                $currency = $DB->getDRow("select * from t_pay_currency where `id` = '{$DRInfo['pay_currency_id']}'");
                                echo $currency['f_pa'];
                                ?></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2"><?php echo L("当前汇率"); ?>：</label>
                            <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['exchange'] * 1; ?></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2"><?php echo L("支付金额"); ?><span class="pay_pa"></span>：</label>
                            <div class="col-sm-8 mt-sm-1"><?php echo $DRInfo['beforenumber'] * 1; ?></div>
                        </div>
                        <div class="form-group row" style="display:none;">
                            <label class="col-sm-2 mt-sm-1"><?php echo L("手续费"); ?><span
                                        class="pay_pa"></span>：</label>
                            <div class="col-sm-8"><?php echo $DRInfo['fee'] * 1; ?></div>
                        </div>
                        <div class="form-group row" style="display:none;">
                            <label class="col-sm-2 mt-sm-1"><?php echo L("总共需支付"); ?><span class="pay_pa"></span>：</label>
                            <div class="col-sm-8"><?php echo $DRInfo['price'] * 1; ?></div>
                        </div>
                        <?php
                        if (strlen($DRPay['f_payFolder'])) {
                            include($_SERVER['DOCUMENT_ROOT'] . '/pay/' . $DRPay['f_payFolder'] . '/in_post.php');
                        } else {
                            include($_SERVER['DOCUMENT_ROOT'] . '/pay/' . $DRPay['PayCode'] . '/in_post.php');
                        }
                        ?>
                        <div class="form-group row">
                            <label class="col-sm-2 control-label">&nbsp;</label>
                            <div class="col-sm-8">
                                <button type="submit" class="btn btn-primary"><?php echo L("去支付"); ?></button>
                                <button onclick="window.history.back()" type="button"
                                        class="btn btn-light"><?php echo L("返回"); ?></button>
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
