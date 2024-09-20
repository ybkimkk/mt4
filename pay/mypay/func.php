<?php

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

function buildSign($payInfo)
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
    return trim($sign, "&");
}