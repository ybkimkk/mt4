<?php
//支付成功异步通知地址=>异步
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/pay/mypay/func.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

$PayCode = 'mypay';

$sPost = @file_get_contents('php://input');  //获取请求体，@的作用为屏蔽警告，可去除。
loginfo($PayCode, "notifyNotice response param is ========================>" . $sPost);
$aPost = json_decode($sPost, true);

try {
    if (!isset($aPost['tradeStatus']) && $aPost['tradeStatus'] !== 3) {
        echo 'fail';
        return false;
    }
} catch (exception $e) {
    echo 'fail';
    return false;
}

$param = [
    "appId" => "627583",
    "out_trade_no" => $aPost['tradeId'],
    "amount" => $aPost['amount'],
];
$buildSign = buildSign($param);
$buildSign .= "&key=1F4lDLlEy6k8Yo8MlP3bERkud62WGhGG"; // 回调秘钥
$param['_sign'] = md5($buildSign);

$result = sendHttpPostRequest("https://api.mybases.app/wallet/chat/check", $param);

try {
    if (!empty($result) && $result['code'] === 200) {
        $DRPay = $DB->getDRow("select * from t_pay where PayCode = '{$PayCode}'");
        if (!$DRPay) {
            echo 'Payment method does not exist';
            exit;
        }
        pay_updateOrder(
            $aPost['orderNo'],  //系统订单号
            $aPost['amount'],   //金额
            date("Y-m-d h:i:s"),           // 当前时间
            '1',                          //1 成功，-1：失败
            '支付成功',
            $aPost['pOrderNo'],               //支付平台订单号
            '',
            '',
            '',
            $PayCode);
        echo "success";
    } else {
        echo "false";
    }
} catch (exception $e) {
    loginfo($PayCode, "notifyNotice has error exception msg is ========================>" . $e);
    echo "false";
}

