<?php
class Tool{
    public static function writeLog($typename,$log){
        if(!file_exists('logs/')){ mkdir('logs');}
        @file_put_contents('logs/'.$typename.'.log',date('Y-m-d H:i:s')."\t".$log.PHP_EOL,FILE_APPEND);
    }

    public static function SendEmailPost($url,$params){
        if(substr($url,0,5)=='https'){
            $response=Tool::HttpsEmailPost($url,$params);
        }else{
            $response=Tool::HttpEmailPost($url,$params);
        }
        //Tool::writeLog('SENDPOST',$url.PHP_EOL.http_build_query($params).PHP_EOL.$response);
        return $response;
    }
	
	
	public static function SendSmsPost($url,$params){
        if(substr($url,0,5)=='https'){
            $response=Tool::HttpsSmsPost($url,$params);
        }else{
            $response=Tool::HttpSmsPost($url,$params);
        }
        //Tool::writeLog('SENDPOST',$url.PHP_EOL.http_build_query($params).PHP_EOL.$response);
        return $response;
    }

    private static function HttpSmsPost($url,$post){
        $content=http_build_query($post);
        $length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "issmsmode: 1\r\n".//邮件模式必须在header中有字段isemailmode，值为1
                    "Content-length: $length \r\n",
                'content' => $content
            )
        );
        $post_tmp=file_get_contents($url,TRUE,stream_context_create($options));
        return $post_tmp;
    }
    private static function HttpsSmsPost($url,$post,$headers=array()){
        $headers  = array(
            'issmsmode:1;',//邮件模式必须在header中有字段isemailmode，值为1
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        );

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $res='Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res;
    }
	

    private static function HttpEmailPost($url,$post){
        $content=http_build_query($post);
        $length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "isemailmode: 1\r\n".//邮件模式必须在header中有字段isemailmode，值为1
                    "Content-length: $length \r\n",
                'content' => $content
            )
        );
        $post_tmp=file_get_contents($url,TRUE,stream_context_create($options));
        return $post_tmp;
    }
    private static function HttpsEmailPost($url,$post,$headers=array()){
        $headers  = array(
            'isemailmode:1;',//邮件模式必须在header中有字段isemailmode，值为1
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        );

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $res='Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res;
    }


    public static function SendPost($url,$params){
        if(substr($url,0,5)=='https'){
            $response=Tool::HttpsPost($url,$params);
        }else{
            $response=Tool::HttpPost($url,$params);
        }
        //Tool::writeLog('SENDPOST',$url.PHP_EOL.http_build_query($params).PHP_EOL.$response);
        return $response;
    }
    private static function HttpPost($url,$post){
        $content=http_build_query($post);
        $length = strlen($content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                    "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-length: $length \r\n",
                'content' => $content
            )
        );
        $post_tmp=file_get_contents($url,TRUE,stream_context_create($options));
        return $post_tmp;
    }
    private static function HttpsPost($url,$post,$headers=array()){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $res='Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $res;
    }
}

class RSAHelper
{

    const CHAR_SET = "UTF-8";
    const BASE_64_FORMAT = "UrlSafeNoPadding";
    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;
    const RSA_ALGORITHM_SIGN = OPENSSL_ALGO_SHA256;

    protected $public_key;
    protected $private_key;
    protected $key_len;

    public function __construct($pub_key, $pri_key = null)
    {
        $this->public_key = $pub_key;
        $this->private_key = $pri_key;

        $pub_id = openssl_get_publickey($this->public_key);
		$arr = openssl_pkey_get_details($pub_id);
        $this->key_len = $arr['bits'];
    }

    /*
     * 创建密钥对
     */
    public static function createKeys($key_size = 2048)
    {
        $config = array(
            "private_key_bits" => $key_size,
            "private_key_type" => self::RSA_ALGORITHM_KEY_TYPE,
        );
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $private_key);
        $public_key_detail = openssl_pkey_get_details($res);
        $public_key = $public_key_detail["key"];

        return array(
            "public_key" => $public_key,
            "private_key" => $private_key,
        );
    }

    /*
     * 公钥加密
     */
    public function publicEncrypt($data)
    {
        $encrypted = '';
        $part_len = $this->key_len / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_public_encrypt($part, $encrypted_temp, $this->public_key);
            $encrypted .= $encrypted_temp;
        }

        return base64_encode($encrypted);
    }

    /*
     * 私钥解密
     */
    public function privateDecrypt($encrypted)
    {
        $decrypted = "";
        $part_len = $this->key_len / 8;
        $base64_decoded = base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_private_decrypt($part, $decrypted_temp,$this->private_key);
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    /*
     * 私钥加密
     */
    public function privateEncrypt($data)
    {
        $encrypted = '';
        $part_len = $this->key_len / 8 - 11;
        $parts = str_split($data, $part_len);

        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_private_encrypt($part, $encrypted_temp, $this->private_key);
            $encrypted .= $encrypted_temp;
        }

        return base64_encode($encrypted);
    }

    /*
     * 公钥解密
     */
    public function publicDecrypt($encrypted)
    {
        $decrypted = "";
        $part_len = $this->key_len / 8;
        $base64_decoded = base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_public_decrypt($part, $decrypted_temp,$this->public_key);
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    /*
     * 数据加签
     */
    public function sign($data)
    {
        openssl_sign($data, $sign, $this->private_key, self::RSA_ALGORITHM_SIGN);

        return base64_encode($sign);
    }

    /*
     * 数据签名验证
     */
    public function verify($data, $sign)
    {
        $pub_id = openssl_get_publickey($this->public_key);
        $res = openssl_verify($data, base64_decode($sign), $pub_id, self::RSA_ALGORITHM_SIGN);

        return $res;
    }
}
