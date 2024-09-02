<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'conn.php');
require_once($_SERVER['DOCUMENT_ROOT'] . CC_ADMIN_ROOT_FOLDER . 'chk_logged.php');
$PayCode = 'moonpay';
//支付
if ($_GET['clause'] == 'topay') {
    $oid = FGetStr('oid');
    $DRInfo = $DB->getDRow("select * from t_inmoney where `payno` = '{$oid}'");
    $DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedER,b.f_symbolsER,b.f_erAlgo from (select * from t_pay where `Status` = 1 and server_id = '{$DRAdmin['server_id']}' and Id = '{$DRInfo['pay_id']}') a left join t_pay_currency b on a.f_currencyId = b.id");
    $memberInfo = $DB->getDRow("select * from t_member where `id` = '{$DRInfo["member_id"]}'");
    echo json_encode($memberInfo);
    if ($DRInfo === null) {
        FJS_AT(L('支付异常,请联系客服'), '?');
    }
    $host = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"];
    $payInfo = [
        "merchantNo" => $DRPay["PayKey"],            //商户号
        "orderNo" => $oid,                           //order no
        "channelType" => 1,                         //币种
        "amount" => $DRInfo['number'],              //金额
        "timestamp" => $DRInfo['create_time'],      //时间搓
        "extra" => [
            "id" => $memberInfo['id'],
            "nickname" => $memberInfo['nickname'],
            "email" => $memberInfo['email']
        ], //额外信息
        "returnUrl" => $host . '/pay/moonpay/returnNotice.php',   //同步回调
        "notifyUrl" => $host . '/pay/moonpay/notifyNotice.php'    //异步回调
    ];
    $payInfo['sign'] = md5(buildSign($payInfo, $DRPay['PaySignKey']));

    loginfo($PayCode, $PayCode . " request param is ===============>" . json_encode($payInfo));
    $result = sendHttpPostRequest("https://www.moon-pay.in/api/order/pay", $payInfo);
    loginfo($PayCode, $PayCode . " pay response param is ===============>" . json_encode($result));

    if ($result['errorCode'] === 0 && $result['errorMsg'] === 'SUCCESS') {
        $DB->getDRow("update t_inmoney set `serialno` = '{$result['pOrderNo']}' where `payno` = '{$oid}'");
        FRedirect($result['data']['payUrl']);
    } else {
        FJS_AT(L('获取支付失败'), '?');
    }

}

function buildSign($payInfo, $secretKey)
{
    // 提取键名
    $keys = array_keys($payInfo);

    // 对键名进行排序
    sort($keys);
    $sign = '';
    foreach ($keys as $key) {
        if (is_array($payInfo[$key])) {
            $sign .= "&" . $key . '=' . json_encode($payInfo[$key], JSON_UNESCAPED_UNICODE);
        } else {
            $sign .= "&" . $key . "=" . $payInfo[$key];
        }
    }
    $sign .= "&key=" . $secretKey;
    return trim($sign, "&");
}

function sendHttpPostRequest($url, $params, $contentType = 'application/json')
{
    $params = json_encode($params);
    // 初始化 cURL 会话
    $ch = curl_init();

    // 设置 cURL 选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回而不是直接输出
    curl_setopt($ch, CURLOPT_POST, true); // 设置为 POST 请求
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // 设置 POST 数据
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: ' . $contentType,
        'Content-Length: ' . strlen($params)
    ]);

    // 忽略 SSL 证书验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 执行 cURL 会话
    $response = curl_exec($ch);

    // 检查是否有错误
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    // 关闭 cURL 会话
    curl_close($ch);

    // 解析响应数据为 JSON 格式
    $responseData = json_decode($response, true);

    return $responseData;
}

