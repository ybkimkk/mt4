<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/mypay/func.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
$PayCode = 'mypay';
//支付
$LangList = array(
    'en-us' => "en-US",
    'zh-vn' => "vietnamese",
    'zh-cn' => "zh-CN",
    'id' => "indian",
    'tc' => "zh-CN",
    'korean' => "korean",
    'japanese' => "en-US",
    'arabic' => "en-US",
);
$crrLang = $LangList[$_COOKIE['lang']];
if (!$LangList[$_COOKIE['lang']]) {
    $crrLang = "en-US";
}
if ($_GET['clause'] == 'topay') {
    $oid = FGetStr('oid');
    $DRInfo = $DB->getDRow("select * from t_inmoney where `payno` = '{$oid}'");
    $DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$DRInfo['pay_id']}') a left join t_pay_currency b on a.f_currencyId = b.id");
    $memberInfo = $DB->getDRow("select * from t_member where `id` = '{$DRInfo["member_id"]}'");
    $payCurrency = $DB->getDRow("select * from t_pay_currency where `id` = '{$DRInfo["pay_currency_id"]}'");

    if ($DRInfo === null) {
        FJS_AT(L('支付异常,请联系客服'), '?');
    }
    $host = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"];
    $payInfo = [
        "appId" => "627583",      //商户的 appId
        "apiUserId" => $memberInfo['id'],     //商户下不同用户唯一识别 Id
        "amount" => $DRInfo['number'],              //金额
        "lang" => $crrLang,
        "apiAmountType" => 1,     //充值金额币种
        "legaltenderid" => 3,     //充值金额币种??
        "apiOrderId" => $oid,     //商户订单唯一识别 Id
        "paymentName" => $memberInfo['nickname'],  //付款人名称
        "phoneNumber" => $memberInfo['phone'],  //用户手机号码
        "notifyUrl" => $host . '/pay/mypay/notifyNotice.php',  //异步回调
    ];
    $buildSign = buildSign($payInfo);
    $buildSign .= "&key=Lewow4aa11aRfSxK4qqFBsoMVPvx5c2l"; // 需要接入私钥
    $payInfo['_sign'] = md5($buildSign);

    loginfo($PayCode, $PayCode . " request param is ===============>" . json_encode($payInfo));
    $result = sendHttpPostRequest("https://api.mybases.app/wallet/chat/generateOrders", $payInfo);


    loginfo($PayCode, $PayCode . " pay response param is ===============>" . json_encode($result));

    if ($result['code'] === 200) {
        $DB->getDRow("update t_inmoney set `serialno` = '{$result['pOrderNo']}' where `payno` = '{$oid}'");
        FRedirect($result['result']['url']);
    } else {
        loginfo($PayCode, $PayCode . " result has error ===============>" . json_encode($result));
        FJS_AT(L('server has error'), "?clause=step2&oid={$oid}");
    }

}


