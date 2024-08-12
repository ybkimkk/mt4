<?php
/**
 * usdt类
 */
class usdt
{
    public function __construct($domain,$appid,$key)
    {
        $this->appid 	= $appid;						//商户号
        $this->key	= $key;				//秘钥
        $this->rechargeUrl = $domain . '/api/merchant/requestTraderRecharge';//用户充值接口按数量
        $this->rechargeRmbUrl = $domain . '/api/merchant/requestTraderRechargeRmb';//用户充值接口按人民币
        $this->uptradeUrl = $domain . '/api/merchant/uptrade';
        $this->notifyUrl = 'http://www.qq.com/'; //异步回调地址
        $this->returnUrl = 'http://www.baidu.com/'; //同步回调地址
    }

    /**
     * [recharge 用户充值按数量]
     * @author max
     */
    public  function recharge()
    {
        $dataArr    = array(
            'amount'           => 100,
            'address'			=> '',
            'username'			=> '',
            'orderid'			=> date('YmdHis').mt_rand(100000,999999),
            'appid'      => $this->appid ,
            'return_url'	=>$this->returnUrl,
            'notify_url'	=>$this->notifyUrl
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->rechargeUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
    }

    /**
     * [recharge 用户充值按人民币]
     * @author max
     */
    public  function rechargeRmb($dataArr)
    {


        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->rechargeRmbUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
    }

    /**
     * [uptrade 确认付款]
     * @author max
     */
    public  function uptrade()
    {
        $dataArr    = array(
            'orderid'           => '',
            'appid'      => $this->appid ,
        );

        $sign = $this->sign($dataArr);

        $dataArr['sign']    = $sign;

        $res = $this->curl($this->uptradeUrl,$dataArr);
        $data = json_decode($res,true);
        return $data;
    }

    /**
     * [recharge 用户充值回调]
     * @author max
     */
    public function notify(){
        $res = $_POST;
        if($res['sign']){
            $sign = $res['sign'];
            unset($res['sign']);
            $sign2 = $this->sign($res);
            if($sign == $sign2 && $res['status'] == 1){
                $status = $res['status'];//回调状态
                $amount = $res['amount'];//充值数量
                $rmb = $res['rmb'];//支付人民币数量
                $orderid = $res['orderid'];//订单号
                $out_trade_no = $orderid;
                if($status == 1){
                    /*成功的逻辑处理*/
					
					$systime = date("Y-m-d h:i:s");					
					pay_updateOrder($orderid, $rmb, $systime, '1', '支付成功', $out_trade_no, '', '', '', $GLOBALS['PayCode']);
                }else{
                    /*失败的逻辑处理*/
                }
            }else{
                exit('fail');
            }
        }else{
            exit('fail');
        }
        echo 'success';
        exit;
    }



    /**
     * [sign 签名验签]
     * @author max
     */
    private function sign($dataArr)
    {
        ksort($dataArr);
        $str = '';
        foreach ($dataArr as $key => $value) {
            $str.=$key.$value;
        }

        $str = $str.$this->key;

        return strtoupper(sha1($str));
    }


    private function curl($url,$data = array())
    {
        //使用crul模拟
        $ch = curl_init();
        //禁用https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //允许请求以文件流的形式返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch); //执行发送
        curl_close($ch);

        return $result;
    }
}
