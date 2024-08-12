<?php
define('CC_PLUGIN_TC_DEBUG', 1);
define('CC_PLUGIN_TC_SIGN_KEY', 'H5w4NwH=x!fuHshBb8Rr5R=?');

Class TripleDES {
    public static function encrypt($s_input, $s_key, $b_padded = true, $b_hexed = true, $b_cbc_mode = false, $s_iv=false) {
		if ($m = strlen($s_input)%8){
			$s_input .= str_repeat("\x00",  8 - $m);
		}
		$encOpenSSLZeroPadding = @openssl_encrypt($s_input, "DES-EDE3", $s_key, OPENSSL_ZERO_PADDING);
		return $encOpenSSLZeroPadding;


		//php7.2
		$s_iv = openssl_random_pseudo_bytes(8);
		$encOpenSSLZeroPadding = @openssl_encrypt(TripleDES::zeroPadding($s_input,8), 'DES-EDE3', $s_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $s_iv);
		return $encOpenSSLZeroPadding;		
		
        if($b_cbc_mode){
            $s_mode = MCRYPT_MODE_CBC;
        } else{
            $s_mode = MCRYPT_MODE_ECB;    
        }
        
        if($b_padded){
            $s_input=TripleDES::padBuffer($s_input);
        }
        
        if($s_iv === false){
			//if (function_exists('random_bytes')) {
			//	$s_iv=openssl_random_pseudo_bytes(8);
			//}else{
				$s_iv=mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_3DES,$s_mode),MCRYPT_RAND);
			//}
        }

		/*if (function_exists('random_bytes')) {
			$message_padded = $s_input;
			$message_padded = TripleDES::padBuffer($message_padded);
			if (strlen($message_padded) % 8) {
				$message_padded = str_pad($message_padded,
					strlen($message_padded) + 8 - strlen($message_padded) % 8, "\0");
			}

			$s_encrypted = openssl_encrypt($message_padded,'DES-EDE3',$s_key,OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $s_iv);
		}else{*/
			$s_encrypted = mcrypt_encrypt (MCRYPT_3DES, $s_key, $s_input, $s_mode, $s_iv); 
		/*}*/

        if ($b_hexed) $s_encrypted = bin2hex($s_encrypted); 
        
        return $s_encrypted; 
    }
	
	private static function zeroPadding($data, $size) {
		$oversize = strlen($data) % $size;
		return $oversize == 0 ? $data : ($data . str_repeat("\0", $size - $oversize)); 
	}
    
    private static function padBuffer($s_input) {
        $s_padVal = 8 - (strlen($s_input) % 8);
        return str_pad($s_input, strlen($s_input) + $s_padVal, chr($s_padVal));
    }
    
    public static function hex2bin($s_hexdata) {
        return pack('H'.strlen($s_hexdata), $s_hexdata);
    }
}

function FCurlGet1($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

	$content = curl_exec($ch);
	
	return $content;
}

function getPluginTcTaFile($type = 0,$time = 0){
	if($time <= 0){
		$time = time();
	}
	
	$iName = (floor(floatval(date('i',$time)) / 5.0)) * 5;
	$filePath = '/plugin/tc/ta/' . date('Y/m/d/',$time);
	$fileName = date('H-',$time) . substr('0' . $iName,-2) . '.xml';
	switch($type){
		case 1:
			return $filePath;
			break;
		case 2:
			return $fileName;
			break;
		default:
			return $filePath . $fileName;
			break;
	}
}

function plugin_tc_api_update_webtv($debug = 0){
	global $DB;
	global $logStr;
	
	$webTvUrl = 'https://video.tradingcentral.com/playlists/33882.xml';
	
	$xml = FCurlGet1($webTvUrl);
	
	$obj = simplexml_load_string($xml,"SimpleXMLElement", LIBXML_NOCDATA);
	$webTvArr = json_decode(json_encode($obj),true);
	
	foreach($webTvArr['video'] as $key=>$val){
		$id = $val['id'];
		if(strlen($id) > 0){
			$chk = $DB->getDRow("select * from t_plugin_tc_webtv where f_pid = " . $id);
			if(!$chk){
				$sqlFVArr = array(
					'f_pid'=>$id,
					'f_title'=>$val['title'],
					'f_description'=>$val['description'],
					'f_createTime'=>$val['created_at'],
					'f_mp4'=>$val['url'],
					'f_picSmall'=>$val['thumbnail_url'],
					'f_picBig'=>$val['image_url'],
					'f_width'=>$val['width'],
					'f_height'=>$val['height'],
					'f_duration'=>$val['duration'],
					'f_playCount'=>$val['play_count'],
				);
				$affectId = $DB->insert('t_plugin_tc_webtv',$sqlFVArr);
				
				if($debug){
					echo '增加WebTV：' , $id , "<br>\r\n";
				}
				$logStr .= '增加WebTV：' . $id . "\r\n";
			}else{
				if($debug){
					echo '跳过WebTV：' , $id , "<br>\r\n";
				}
				$logStr .= '跳过WebTV：' . $id . "\r\n";
			}
		}else{
			if($debug){
				echo 'WebTV有误：' , $id , "<br>\r\n";
			}
			$logStr .= 'WebTV有误：' . $id . "\r\n";
		}
	}
}

function plugin_tc_api_update_ta($debug = 0){
	global $DB;
	global $logStr;
	
	$filePath = getPluginTcTaFile(1);
	$fileName = getPluginTcTaFile(2);
	
	if(!is_file($_SERVER['DOCUMENT_ROOT'] . $filePath . $fileName)){
		@mkdir($_SERVER['DOCUMENT_ROOT'] . $filePath,0777,true);
	
		$webTvUrl = 'https://feed.tradingcentral.com/ws_ta.asmx/GetFeed?culture=zh-CN&type_product=null&product=null&term=null&days=1&last_ta=true&partner=1683&token=EnAJ72%2f2EAbz%2bB5eTiYWVg%3d%3d';
		//《Trading Central Web Services(API)-Analyst Views(ZHCN).docx》
	
		$xml = FCurlGet1($webTvUrl);
	
		FStrToFile($xml,$_SERVER['DOCUMENT_ROOT'] . $filePath . $fileName);
	
		if($debug){
			echo '分析师观点：生成文件 ' , $fileName , "<br>\r\n";
		}
		$logStr .= '分析师观点：生成文件' . $fileName . "\r\n";
	}else{
		if($debug){
			echo '分析师观点：跳过' , "<br>\r\n";
		}
		$logStr .= '分析师观点：跳过' . $fileName . "\r\n";
	}
}

function plugin_tc_api_get_ta(&$filePath,&$fileName,$isArr = 0){
	global $DB;
	
	//当前时间的xml文件
	$filePath = getPluginTcTaFile(1);
	$fileName = getPluginTcTaFile(2);
	
	//没找到，可能还没生成，往前推，拿旧的5分钟前的
	if(!is_file($_SERVER['DOCUMENT_ROOT'] . $filePath . $fileName)){
		$filePath = getPluginTcTaFile(1,time() - 60 * 5);
		$fileName = getPluginTcTaFile(2,time() - 60 * 5);
		
		//要是还没有，就生成
		if(!is_file($_SERVER['DOCUMENT_ROOT'] . $filePath . $fileName)){
			plugin_tc_api_update_ta($debug = 0);
			
			//重取当前时间的
			$filePath = getPluginTcTaFile(1);
			$fileName = getPluginTcTaFile(2);
		}
	}

	$xml = FReadFileUtf8($_SERVER['DOCUMENT_ROOT'] . $filePath . $fileName);
	$obj = simplexml_load_string($xml,"SimpleXMLElement", LIBXML_NOCDATA);
	
	if($isArr){
		$arr = json_decode(json_encode($obj),true);
		return $arr;
	}else{
		return $obj;
	}
}

function dz_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {  
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙  
    $ckey_length = 4;  
      
    // 密匙  
    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);  
      
    // 密匙a会参r加解密  
    $keya = md5(substr($key, 0, 16));  
    // 密匙b会用来做数据完整性验证  
    $keyb = md5(substr($key, 16, 16));  
    // 密匙c用于变化生成的密文  
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';  
    // 参与运算的密匙  
    $cryptkey = $keya.md5($keya.$keyc);  
    $key_length = strlen($cryptkey);  
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性  
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确  
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;  
    $string_length = strlen($string);  
    $result = '';  
    $box = range(0, 255);  
    $rndkey = array();  
    // 产生密匙簿  
    for($i = 0; $i <= 255; $i++) {  
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);  
    }  
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度  
    for($j = $i = 0; $i < 256; $i++) {  
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;  
        $tmp = $box[$i];  
        $box[$i] = $box[$j];  
        $box[$j] = $tmp;  
    }  
    // 核心加解密部分  
    for($a = $j = $i = 0; $i < $string_length; $i++) {  
        $a = ($a + 1) % 256;  
        $j = ($j + $box[$a]) % 256;  
        $tmp = $box[$a];  
        $box[$a] = $box[$j];  
        $box[$j] = $tmp;  
        // 从密匙簿得出密匙进行异或，再转成字符  
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));  
    }  
    if($operation == 'DECODE') {  
        // substr($result, 0, 10) == 0 验证数据有效性  
        // substr($result, 0, 10) - time() > 0 验证数据有效性  
        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性  
        // 验证数据有效性，请看未加密明文的格式  
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {  
            return substr($result, 26);  
        } else {  
            return '';  
        }  
    } else {  
        // 把动态密0保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因  
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码  
        return $keyc.str_replace('=', '', base64_encode($result));  
    }  
}