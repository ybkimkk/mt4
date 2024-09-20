<?php
//支付成功异步通知地址=>异步
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');

$PayCode = 'moonpay';

$sPost = @file_get_contents('php://input');  //获取请求体，@的作用为屏蔽警告，可去除。
loginfo($PayCode, "notifyNotice response param is ========================>" . $sPost);
$aPost = json_decode($sPost, true);
if (!empty($aPost) && $aPost['state'] === 1) {
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
    loginfo($PayCode, "notifyNotice has error param is ========================>" . $sPost);
    echo "false";
}

