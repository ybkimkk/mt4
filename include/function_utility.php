<?php
//js提示并跳转
function FJS_AT($tips,$url,$isExit = true){
	echo '<script type="text/javascript">alert("' , $tips , '");location.href="' , $url , '";</script>';
	if($isExit){
		exit();
	}
}

//js提示并后退
function FJS_AB($tips,$isExit = true){
	echo '<script type="text/javascript">alert("' , $tips , '");history.back();</script>';
	if($isExit){
		exit();
	}
}

//客户端js跳转
function FJS_T($url,$isExit = true){
	echo '<script type="text/javascript">location.href="' , $url , '";</script>';
	if($isExit){
		exit();
	}
}

//js提示并执行js代码
function FJS_AJ($tips,$jsCode,$isExit = true){
	echo '<script type="text/javascript">alert("' , $tips , '");' , $jsCode , '</script>';
	if($isExit){
		exit();
	}
}

//js提示
function FJS_A($tips,$isExit = true){
	echo '<script type="text/javascript">alert("' , $tips , '");</script>';
	if($isExit){
		exit();
	}
}

//执行js代码
function FJS_J($jsCode,$isExit = true){
	echo '<script type="text/javascript">' , $jsCode , '</script>';
	if($isExit){
		exit();
	}
}

//window.onload后执行js代码，需要与页面上的js相配合
function FJS_OnloadPush($jsCode){
	echo '<script type="text/javascript">$(function(){' , $jsCode , '});</script>';
}

//-------------------

//js提示并top页跳转（内框架使整个网页得到跳转）
function FJS_T_AT($tips,$url,$isExit = true){
	echo '<script type="text/javascript">window.top.WJX_Alert("' , $tips , '",function(){window.top.location.href="' , $url , '";});</script>';
	if($isExit){
		exit();
	}
}

function FJS_T_T($url,$isExit = true){
	echo '<script type="text/javascript">window.top.location.href="' , $url , '";</script>';
	if($isExit){
		exit();
	}
}

//-------------------

//js提示并parent页跳转（内框架使整个网页得到跳转）
function FJS_P_AT($tips,$url,$isExit = true){
	echo '<script type="text/javascript">window.parent.WJX_Alert("' , $tips , '",function(){window.parent.location.href="' , $url , '";});</script>';
	if($isExit){
		exit();
	}
}

//js提示，并且执行父页面的art关闭
function FJS_P_AC($tips,$isExit = true){
	echo '<script type="text/javascript">window.parent.WJX_Alert("' , $tips , '",function(){window.parent.WJX_ArtClose();});</script>';
	if($isExit){
		exit();
	}
}

function FJS_P_AJ($tips,$jsCode,$isExit = true){
	echo '<script type="text/javascript">window.parent.WJX_Alert("' , $tips , '",function(){' , $jsCode , '});</script>';
	if($isExit){
		exit();
	}
}

function FJS_P_T($url,$isExit = true){
	echo '<script type="text/javascript">window.parent.location.href="' , $url , '";</script>';
	if($isExit){
		exit();
	}
}

//-------------------

//过滤所有html标签
//php自带的strip_tags过滤有问题
function FRemoveHTML($html){
	//return strip_tags($html);
	//php自带的strip_tags有bug
	//例：话说2<3是对的
	//会被过滤成：话说2
	$r = preg_replace('(<[^<]*>)','',$html);
	return $r;
}

function FRemoveHTMLBlank($html){
	//return strip_tags($html);
	//php自带的strip_tags有bug
	//例：话说2<3是对的
	//会被过滤成：话说2
	$temp = preg_replace('(<[^<]*>)','',$html);
	$temp = str_ireplace('&nbsp;',' ',$temp);
	return $temp;
}

function FRemoveJS($html){
	$r = preg_replace("/<script[\s\S]*?<\/script>/i",'',$html);
	return $r;
}

//获取文件扩展名
function FGetFileExt($filePath,$withPoint = false){
	$r = ($withPoint ? '.' : '') . pathinfo($filePath,PATHINFO_EXTENSION);
	return $r;
}

//判断是否是时间格式
/*
如果是int类型，那么时间的区间是：
min 1970-01-01 00:00:00（不含）
max 2038-01-19 03:14:07（含）
北京时区要+8小时，这里以标准为准
*/
function FIsDate($str){
	if(is_numeric($str)){
		if($str <= 0 || $str > strtotime('2038-01-19 03:14:07')){
			return 0;
		}else{
			return 1;
		}
	}else{
		if(strtotime($str)){
			return 1;
		}else{
			return 0;
		}
	}
}

//是否是身份证号码
function FIsIdCard($num){
    $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    for ($i = 0;$i < 17;$i++){
        $b = (int) $num{$i};
        $w = $wi[$i];
        $sigma += $b * $w;
    }
    $snum = $sigma % 11;
    $check_num = $ai[$snum];
	if($num{17} == $check_num){
		return 1;
	}else{
		return 0;
	}
}

//是否是手机号码
function FIsMobile($str){
	$r = (strlen($str) == 11 && substr($str,0,1) == '1') ? 1 : 0;
	return $r;
}

//随机字符
function FRndStr($len,$withUpperCase = true){
	$str = 'abcdefghijklmnopqrstuvwxyz0123456789';  
	if($withUpperCase){
		$str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}
	$temp = ''; 
	for ($ci = 0; $ci < $len; $ci++){   
		$temp .= substr($str, mt_rand(0,strlen($str)-1),1); 
	}   
	return $temp;
}

//随机字符，不含O0等这类容易误辩认的字符
function FRndCardPwd($len){
	$str = '3457ACDEFHJKLMNPQRSTUVWXY';  
	$temp = ''; 
	for ($ci = 0; $ci < $len; $ci++){   
		$temp .= substr($str, mt_rand(0,strlen($str)-1),1); 
	}   
	return $temp;
}

//随机数字
function FRndNum($len){
	$str = '0123456789';  
	$temp = ''; 
	for ($ci = 0; $ci < $len; $ci++){   
		$n = substr($str, mt_rand(0,strlen($str)-1),1); 
		if($n == 0 && $ci == 0){$n = 5;}
		$temp .= $n;
	}
	return $temp;
}

//内容保存为文件
function FStrToFile($str,$fileName,$mode = 'a+'){
	$handle = fopen($fileName,$mode);
	fwrite($handle,$str);
	fclose($handle);
}

function FIsHttps(){
	$r = (strtolower($_SERVER['HTTPS']) === 'on' || strtolower($_SERVER['HTTPS']) === '1' || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' || strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) === 'on' || strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) === '1' || $_SERVER['SERVER_PORT'] == 443) ? 1 : 0;
	return $r;
}

function FGetHttpOrHttps(){
	if(FIsHttps()){
		return 'https://';
	}else{
		return 'http://';
	}
}

//取地址栏值
//1 = http://域名/目录/文件名.php
//2 = http://域名/目录/
//3 = http://域名（http://域名:非80端口）
//4 = /目录/文件名.php?参数
//5 = /目录/文件名.php
//6 = /目录/
//7 = 文件名.php?参数
//8 = 文件名.php
//9 = 地址栏参数，不含?号
//0 = 赋整个地址栏值
function FGetCurrUrl($type){
	if($type == 0){
		$type = 999;
	}
	$Q = FIsHttps() ? 'https' : 'http';
	$Q .= '://' . $_SERVER['HTTP_HOST'];
	if($_SERVER['SERVER_PORT'] == 80){
		$L = $Q;
		$M = $Q . ':' . $_SERVER['REQUEST_URI'];
	} else {
		$L = $Q . ':' . $_SERVER['SERVER_PORT'];
		$M = $Q . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
	}
	$N = parse_url($M);
	$O = pathinfo(basename($M));
	if(dirname($_SERVER['SCRIPT_NAME']) == "\\"){
		$P = '/';
	} else {
		$P = dirname($_SERVER['SCRIPT_NAME']) . '/';
	}
	switch ($type){
		case '1':
			$r = $L . $N['path'];
			break;	
		case '2':
			$r = $L . $P;
			break;	
		case '3':
			$r = $L;
			break;	
		case '4':
			$r = $N['path'] . '?' . $N['query'];
			break;	
		case '5':
			$r = $N['path'];
			break;	
		case '6':
			$r = $P;
			break;	
		case '7':
			$r = $O['basename'];
			break;	
		case '8':
			$r = str_replace('?' . $N['query'], '', $O['basename']);
			break;	
		case '9':
			$r = $N['query'];
			break;	
		default:
			$r = $M;
	}
	return $r;
}

//过滤html标签后，转义可能涉及数据库安全的字符为 &quot; 这类的格式
//专供get和post数据时用
//s = short、sql、self，只过滤涉及sql不安全操作的字符
//2019-03-30 更新为：不再使用该函数，即不使用其手工转换的字符实体，而使用mysqli里的转换
function FSHtmlEncode($html){
	$temp = $html . '';
	if($temp != ''){
		//$temp = strip_tags($temp);
		$temp = FRemoveHTML($temp);

		//addslashes 系统的转义是："转为\"，'转为\'
		//这里需要自行手工转义，转为htmlencode
		//$temp = htmlspecialchars($temp);
		$temp = str_replace(array('\"', '"', "\\'", "'", '<', '>'), array('&quot;', '&quot;', '&#039;', '&#039;', '&lt;', '&gt;'), $temp);
	}
	return $temp;
}

//获取地址栏的参数值（过滤html标签+sql不安全字符转义），默认过滤首尾空格
function FGetStr($key){
	$r = trim(FRemoveHTML(trim($_GET[$key])));
	return $r;
}

function FGetStrUnTrim($key){
	$r = FRemoveHTML($_GET[$key]);
	return $r;
}

//获取地址栏的参数值为int类型（有正负）
function FGetInt($key){
	$r = intval(trim($_GET[$key]));
	return $r;
}

function FGetUInt($key){
	$r = intval(trim($_GET[$key]));
	if($r < 0){
		$r = 0;
	}
	return $r;
}

function FGetIds($key){
	$r = trim(FRemoveHTML(trim($_GET[$key])));
	$arr = array();
	if(strpos($r,',') !== false){
		$rArr = explode(',',$r);
		foreach($rArr as $key=>$val){
			$arr[] = intval($val);
		}
	}else{
		$arr[] = intval($r);
	}
	return implode(',',$arr);
}

//获取POST过来的参数值（过滤html标签+sql不安全字符转义），默认过滤首尾空格
function FPostStr($key){
	$r = trim(FRemoveHTML(trim($_POST[$key])));
	return $r;
}

function FPostStrUnTrim($key){
	$r = FRemoveHTML($_POST[$key]);
	return $r;
}

//获取POST过来的参数值为int类型（有正负）
function FPostInt($key){
	$r = intval(trim($_POST[$key]));
	return $r;
}

function FPostUInt($key){
	$r = intval(trim($_POST[$key]));
	if($r < 0){
		$r = 0;
	}
	return $r;
}

function FPostIds($key){
	$r = trim(FRemoveHTML(trim($_POST[$key])));
	$arr = array();
	if(strpos($r,',') !== false){
		$rArr = explode(',',$r);
		foreach($rArr as $key=>$val){
			$arr[] = intval($val);
		}
	}else{
		$arr[] = intval($r);
	}
	return implode(',',$arr);
}

//获取request
//获取POST过来的参数值（过滤html标签+sql不安全字符转义），默认过滤首尾空格
function FRequestStr($key){
	$r = trim(FRemoveHTML(trim($_REQUEST[$key])));
	return $r;
}

function FRequestStrUnTrim($key){
	$r = FRemoveHTML($_REQUEST[$key]);
	return $r;
}

//获取POST过来的参数值为int类型（有正负）
function FRequestInt($key){
	$r = intval(trim($_REQUEST[$key]));
	return $r;
}

function FRequestUInt($key){
	$r = intval(trim($_REQUEST[$key]));
	if($r < 0){
		$r = 0;
	}
	return $r;
}

function FRequestIds($key){
	$r = trim(FRemoveHTML(trim($_REQUEST[$key])));
	$arr = array();
	if(strpos($r,',') !== false){
		$rArr = explode(',',$r);
		foreach($rArr as $key=>$val){
			$arr[] = intval($val);
		}
	}else{
		$arr[] = intval($r);
	}
	return implode(',',$arr);
}

//转义：
//未开启系统自转义，则手工转义；已开启就不管了，因为已经转义
//单引号（'）转为（\'）
//双引号（"）转为（\"）
//反斜杠（\）转为（\\）
//NULL 转为 空字符串(转义后===''为true)
//默认地，PHP 对所有的 GET、POST 和 COOKIE 数据自动运行 addslashes()。所以不应对已转义过的字符串使用 addslashes()，因为这样会导致双层转义。遇到这种情况时可以使用函数 get_magic_quotes_gpc() 进行检测。
//本函数已经有检测判断，只有当系统未开启时，才进行转义
function FAddslashes($html){
	if(!get_magic_quotes_gpc()){
		if(is_array($html)){
			foreach ($html as $k => $v){
				$html[$k] = FAddslashes($v);
			}
		} else {
			$html = addslashes($html);
		}
	}
	return $html;
}

function FStripslashes($html){
	if(get_magic_quotes_gpc()){
		if(is_array($html)){
			foreach ($html as $k => $v){
				$html[$k] = FStripslashes($v);
			}
		} else {
			$html = stripslashes($html);
		}
	}
	return $html;
}

//FCreateCkeditor('f_content',$rs['f_content'],array('lang'=>'zh-cn','toolbar'=>'Default','height'=>'450'))
function FCreateCkeditor($id,$val,$ckconfig){
	echo '<textarea name="', $id, '" id="', $id, '" class="textarea4">';
	echo htmlspecialchars($val);
	echo '</textarea>';
	echo '<script type="text/javascript">CKEDITOR.replace("', $id, '",{language:"', $ckconfig['lang'], '",toolbar:"', $ckconfig['toolbar'], '",height:"', $ckconfig['height'], '"});</script>';
}

//创建订单号
//如：20151125084553477
//type 1=短年份12位（15开头）+随机位数
//type 2=长年份14位（2015开头）+随机位数
function FCreateOrderId($type = 2,$rndLen = 3){
	switch ($type){
		case 1:
			$r = date('ymdHis') . FRndNum($rndLen);
			break;	
		default:
			$r = date('YmdHis') . FRndNum($rndLen);
	}
	return $r;
}

//长度不够时补0到前面
function FFill0($str,$strFullLen){
	if($strFullLen > strlen($str)){
		$r = sprintf('%0' . $strFullLen . 's', $str);
	} else {
		$r = $str;
	}
	return $r;
}

//36进制的订单号，如：00000JUAPWMVD1I
//type 1=短年份12位（15开头）+随机位数
//type 2=长年份14位（2015开头）+随机位数
//orderLen 在不够长度时前面补0
function FCreateOrderId36($type = 2,$rndLen = 3,$orderLen = 15){
	$r = FFill0(strtoupper(base_convert(FCreateOrderId($type, $rndLen) , 10, 36)) , $orderLen);
	return $r;
}

function FP3P(){
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
}

function FNow(){
	$r = date('Y-m-d H:i:s');
	return $r;
}

function FSetCookie($key,$val,$time = NULL,$path = '/'){
	setcookie($key,$val,$time,$path);
}

//例1：
//$cookieArr=array(
//	'uid'=>$affectId,
//	'username'=>$username,
//	'nickname'=>'',
//	'password'=>md5(CC_ENCRYPT_KEY.$password),
//	'loginTime'=>time(),
//);
//$remember=FPostInt('remember');
//if($remember > 0){
//	$cookieArr['expires']=time() + 3600 * 24 * $remember;
//}else{
//	$cookieArr['expires']=NULL;
//}
//FSetCookieArr('member', $cookieArr, $cookieArr['expires']);
//
//例2：
//$logged_member_uid=$_COOKIE['member']['uid'];
//$logged_member_username=$_COOKIE['member']['username'];
//$logged_member_nickname=$_COOKIE['member']['nickname'];
//$logged_member_password=$_COOKIE['member']['password'];
//$logged_member_loginTime=intval($_COOKIE['member']['loginTime']);
//$logged_member_expires=intval($_COOKIE['member']['expires']);
//$logged_member_state=0;
//if(strlen($logged_member_uid) > 0 && strlen($logged_member_username) > 0 && strlen($logged_member_password) > 0){
//	$rsMember = $DB->getDRow("select * from `t_member` where f_username = '$logged_member_username' and f_password = '{$logged_member_password}'");
//    if($rsMember){
//        $logged_member_state=1;
//    }
//}
//FSetCookieArr($groupKey, $arr, $time = NULL, $path = '/')
function FSetCookieArr($groupKey, $arr, $time = NULL, $path = '/'){
	foreach ($arr as $k => $v){
		setcookie($groupKey . '[' . $k . ']', $v, $time, $path);
	}
}

function FClearCookieArr($groupKey,$path = '/'){
	$L = $_COOKIE[$groupKey];
	if(is_array($L) && count($L) > 0){
		foreach ($L as $k => $v){
			setcookie($groupKey . '[' . $k . ']', '', NULL, $path);
		}
	}
}

function FPrevUrl(){
	$r = $_GET['prevUrl'];
	if(!$r){
		$r = $_POST['prevUrl'];
	}
	if(!$r){
		$r = $_SERVER['HTTP_REFERER'];
	}
	if(!$r){
		$r = '/';
	}
	return $r;
}

function FRedirect($a){
	header('Location:' . $a);
	exit;
}

function FReadFileUtf8($a){
	$r = file_get_contents($a);
	return $r;
}

function FReadFileGbk($a){
	$r = iconv('gbk','utf-8//IGNORE',file_get_contents($a));
	return $r;
}

//转义html标签专门用于截取字符串长度（先FHtmlDecode，再截取，最后FHtmlEncode）
//c=cut，截取字符串时转义专用，使得像 &gt; 转为 >，而使在截取字符时算1个字符单位
//注意，其中的空格不是直接敲的，而是&#12288; 只有这样在取反的时候才能正常处理
function FHtmlDecode($html){
	$r = str_replace(array('&#039;','&quot;','&lt;','&gt;','&nbsp;','&amp;'),array("'",'"','<','>','　','&'),$html);
	return $r;
}

//FHtmlDecode取反
//其中&amp;不换回，因为它换不换在html中都能正常显示、正常处理；而如果换，肯定会把其它的&quot;等处理坏
function FHtmlEncode($html){
	$r =  str_replace(array("'",'"','<','>','　'),array('&#039;','&quot;','&lt;','&gt;','&nbsp;'),$html);
	return $r;
}

function FArrToStr($a){
	if(is_array($a)){
		$r = var_export($a, true);
	} else {
		$r = $a;
	}
	return $r;
}

//格式化数值，如：FFormatNum(3.1,3)=3.100
function FFormatNum($num,$dp){
	$r = sprintf('%.'.$dp.'f',is_numeric($num) ? floatval($num) : 0);
	return $r;
}

//根据开始字符和结束字符取str的内容
//sStr=从哪里开始往后取数据
//FGetSubString($oStr,$sStr,$startStr,$endStr,$withSEStr)
function FGetSubString($oStr,$sStr,$startStr,$endStr,$withSEStr){
	$r = '';
	$L = - 1;
	$M = 0;
	if(strlen($sStr) > 0){
		$M = strpos($oStr, $sStr);
	}
	if($M !== false){
		$M = $M + strlen($sStr);
		$M = strpos($oStr, $startStr, $M);
		if($M !== false){
			$L = strpos($oStr, $endStr, $M + strlen($startStr));
			if($L !== false && $L > $M){
				if($withSEStr == false){
					$r = substr($oStr, $M + strlen($startStr) , $L - $M - strlen($startStr));
				} else {
					$r = substr($oStr, $M, $L - $M + strlen($endStr));
				}
			}
		}
	}
	return $r;
}

//字符转为16进制的数字，可用于地址栏传递数据时的加密
function FHexEncode_CB($r){
	return str_pad(dechex(ord($r[1])) , 2, '0', STR_PAD_LEFT);
}
function FHexEncode($html){
	$r = preg_replace_callback('/(.)/s', 'FHexEncode_CB', $html);
	return $r;
}

//FHexEncode 取反
function FHexDecode_CB($r){
	return chr(hexdec($r[1]));
}
function FHexDecode($html){
	$r = preg_replace_callback('/(\w{2})/', 'FHexDecode_CB', $html);
	return $r;
}

//一个中文算2个字符单位，可混合中英文进行截取
//即：len=4时，若截取中文，实际只返回2个中文字（并视情况）+..
//FShortStr2($str,$len,$isHtml=false,$append='..',$encoding='utf-8')
function FShortStr2($str,$len,$isHtml = false,$append = '..',$encoding = 'utf-8'){
	$r = '';
	if(!$isHtml){
		$str = FHtmlDecode($str);
	}
	if($len >= FStrLen2($str, $encoding)){
		if(!$isHtml){
			$str = FHtmlEncode($str);
		}
		$r = $str;
	} else {
		$M = 0;
		for ($i = 0; $i < $len; $i++){
			$N = substr($str, $M, 1);
			if(ord($N) > 127){
				$i++;
				if($i < $len){
					if(strtolower($encoding) == 'utf-8' || strtolower($encoding) == 'utf8'){
						$r.= substr($str, $M, 3);
						$M+= 3;
					} else {
						$r.= substr($str, $M, 2);
						$M+= 2;
					}
				}
			} else {
				$r.= substr($str, $M, 1);
				$M+= 1;
			}
		}
		if(!$isHtml){
			$r = FHtmlEncode($r);
		}
		if($str != $r){
			$r.= $append;
		}
	}
	return $r;
}

//以中文长度为准来指定截取，如len=4时，若截取中文，将返回4个中文字（并视情况）+..；若截取英文，将返回8个英文字（并视情况）+..
//FShortStr($str,$len,$isHtml=false,$append='..',$encoding='utf-8')
function FShortStr($str,$len,$isHtml=false,$append='..',$encoding='utf-8'){
	$r = FShortStr2($str,$len*2,$isHtml,$append,$encoding);
	return $r;
}

//取字符串左边多少个字符，中文、英文每个字都算1个字符单位
function FStrLeft($str,$len,$encoding = 'utf-8'){
	$L = mb_strlen($str, $encoding);
	if($len >= $L){
		$r = $str;
	} else {
		if(function_exists('mb_substr')){
			$r = mb_substr($str, 0, $len, $encoding);
		} else if(function_exists('iconv_substr')){
			$r = iconv_substr($str, 0, $len, $encoding);
		} else {
			$r = substr($str, 0, $len);
		}
	}
	return $r;
}

//取字符串右边多少个字符，中文、英文每个字都算1个字符单位
function FStrRight($str,$len,$encoding = 'utf-8'){
	$L = mb_strlen($str, $encoding);
	if($len >= $L){
		$r = $str;
	} else {
		if(function_exists('mb_substr')){
			$r = mb_substr($str, $L - $len, $len, $encoding);
		} else if(function_exists('iconv_substr')){
			$r = iconv_substr($str, $L - $len, $len, $encoding);
		} else {
			$r = substr($str, $L - $len, $len);
		}
	}
	return $r;
}

//不管中文还是英文，1个字都算1个字符单位
//本函数必须在与$encoding相同的环境下使用（即：encoding=utf-8时，要求：php charset=utf-8、页面保存为utf-8编码）
//如果encoding=utf-8，而环境却是gb2312，则结果与实际不符（好像多出一个单位？）
function FStrLen1($str,$encoding = 'utf-8'){
	$r = mb_strlen($str,$encoding);
	return $r;
}

//1个中文算2个字符单位，1个英文算1个字符单位
//FStrLen2($str,$encoding='utf-8')
function FStrLen2($str,$encoding = 'utf-8'){
	if(strtolower($encoding) == 'utf-8' || strtolower($encoding) == 'utf8'){
		$r = (strlen($str) + mb_strlen($str, $encoding)) / 2;
	} else {
		$r = strlen($str) + mb_strlen($str, $encoding);
	}
	return $r;
}

//获取当前页面地址、并排除page=xxx
//如：http://www.a.com/b/c.php?d=1&e=f&page=2
//FGetCurrUrlNotWithPageParam()=?d=1&e=f&
//如：http://www.a.com/b/c.php
//FGetCurrUrlNotWithPageParam()=?
function FGetCurrUrlNotWithPageParam($pageKey = 'page'){
	$N = FIsHttps() ? 'https' : 'http';
	$L = parse_url($N . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	$r = '?';
	if(strlen($L['query']) > 0){
		$r.= $L['query'];
		$M = "/&" . $pageKey . "=[-0-9]*/";
		if(preg_match($M, $r)){
			$r = preg_replace($M, '', $r);
		}
		$M = "/\?" . $pageKey . "=[-0-9]*/";
		if(preg_match($M, $r)){
			$r = preg_replace($M, '?', $r);
		}
	}
	if($r != '?'){
		$r.= '&';
	}
	return $r;
}

//获取网址的html代码
function FCurlGet($url,$isAutoRedirect = 1){
	$L = curl_init();
	curl_setopt($L, CURLOPT_URL, $url);
	curl_setopt($L, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($L, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($L, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)');
	if($isAutoRedirect){
		curl_setopt($L, CURLOPT_FOLLOWLOCATION, 1);
	}
	curl_setopt($L, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	$r = curl_exec($L);
	return $r;
}

//post并获取数据
//postData可以是数组，也可以是串接后的字符串，如：a=b&c=d（理论上值b和d等需要urlencode）
function FCurlPost($url,$postData){
	if(is_array($postData)){
		$temp = '';
		foreach ($postData as $key=>$val){
			if($temp){
				$temp .= '&';
			}
			$temp .= $key;
			$temp .= '=';
			$temp .= urlencode($val);
		}
		$postData = $temp;
	}
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)');
	curl_setopt($ch , CURLOPT_POST , 1);
	curl_setopt($ch , CURLOPT_POSTFIELDS , $postData);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	$content = curl_exec($ch);
	
	return $content;
}

//以HRPD的方式发送数据
function FCurlPostAjax($url,$postData){
	if(is_array($postData)){
		$temp = '';
		foreach ($postData as $key=>$val){
			if($temp){
				$temp .= '&';
			}
			$temp .= $key;
			$temp .= '=';
			$temp .= urlencode($val);
		}
		$postData = $temp;
	}
	
	$header = array();
	$header[] = 'Content-Type: text/xml; charset=utf-8';  	
	$header[] = 'User-Agent: nginx/1.0.0';
	$header[] = 'Accept: text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2';  
	$header[] = 'Connection: keep-alive';  
	$header[] = 'Content-Length: ' . strlen($postData);  

	$ch = curl_init();  
	curl_setopt($ch, CURLOPT_URL, $url);  
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  
	curl_setopt($ch, CURLOPT_POST, 1);   
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	curl_setopt($ch, CURLOPT_HEADER, 0);  
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	$res = curl_exec($ch);  
	curl_close($ch);  

	return $res;  	
}

//格式化json格式的字符串，以换行、缩进
function FFormatJson($jsonStr){
	$result = '';
	$pos = 0;
	$strLen = strlen($jsonStr);
	$indentStr = '    ';
	$newLine = "\r\n";
	$prevChar = '';
	$outOfQuotes = true;
	for($i = 0;$i <= $strLen;$i++){
		$char = substr($jsonStr,$i,1) ;
		if($char == '"' && $prevChar != '\\'){
			$outOfQuotes = !$outOfQuotes;
		}else if(($char == '}' || $char == ']') && $outOfQuotes){
			$result .= $newLine;
			$pos--;
			for($j = 0;$j < $pos; $j++){
				$result .= $indentStr;
			}
		}
		$result .= $char;
		if(($char == ',' || $char == '{' || $char == '[') && $outOfQuotes){
			$result .= $newLine;
			if($char == '{' || $char == '[' ){
				$pos ++ ;
			}
			for ($j = 0;$j < $pos;$j++){
				$result .= $indentStr;
			}
		}
		$prevChar = $char;
	}
	return $result;
}

//时间差，返回数组：array('day'=>$days,'hour'=>$hours,'min'=>$mins,'sec'=>$secs)
//FDateDiffRArr($date1,$date2)
function FDateDiffRArr($date1,$date2){
	if(!is_numeric($date1)) $date1 = strtotime($date1);
	if(!is_numeric($date2)) $date2 = strtotime($date2);
	$L = $date2 - $date1;
	$d = intval($L / (60 * 60 * 24));
	$r = $L % (60 * 60 * 24);
	$H = intval($r / (60 * 60));
	$r = $r % (60 * 60);
	$M = intval($r / 60);
	$S = $r % 60;
	$r = array(
		'day' => $d,
		'hour' => $H,
		'min' => $M,
		'sec' => $S
	);
	return $r;
}

//get和post保存为debug文件
function FDebugLog($filePath = ''){
	if(strlen($filePath) <= 0){
		$filePath = 'logs/' . date('Y-m-d') . '_' . md5(date('Y-m-d').'GH52#^g5ds') . '.txt';
	}
	$fp = fopen($filePath, 'a+b');
	fwrite($fp, date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']) . "\r\n");
	fwrite($fp, FGetCurrUrl(0) . "\r\n\r\n");
	fwrite($fp, 'GET:' . "\r\n");
	fwrite($fp, print_r($_GET,true));
	fwrite($fp, "\r\n" . 'POST:' . "\r\n");
	fwrite($fp, print_r($_POST,true));
	fwrite($fp, "\r\n" . 'php://input:' . "\r\n");
	fwrite($fp, print_r(file_get_contents('php://input', 'r'),true));
	fwrite($fp, "\r\n----------------\r\n\r\n");
	fclose($fp);
}

//随机float 介于0（不含）至1（不含），如：0.954582944491220
function FRndFloat($min = 0, $max = 1){  
    $r = $min + mt_rand() / mt_getrandmax() * ($max - $min);
	return $r;
}

//过滤不能成为文件名的字符，如：*?
function FFilenameRemoveBadChar($filename){
	$r = str_replace(array("\\","/",":","*","?","\"","<",">","|"),"",$filename);
	return $r;
}

//本月的最后一天
function FGetCurrMonthLastDay(){
	return FGetMonthLastDay(date('Y'),date('m'));
}
//1970-2038
function FGetMonthLastDay($year,$month){
	$r = date('Y-m-d 23:59:59',strtotime('+1 month -1 day',strtotime(date($year . '-' . $month . '-1'))));
	return $r;
}
//100-9999
function FGetMonthLastDayL($year = 0,$month = 0){
	//取当前时间
	$date = new DateTime();
	if($year <= 0){
		//格式化出年份
		$year = intval($date->format('Y'));
	}
	if($year < 100 || $year > 9999){
		return false;
	}
	
	if($month <= 0){
		//格式化出月份
		$month = intval($date->format('m'));
	}
	if($month <= 0 || $month > 12){
		return false;
	}
	
	//按照日期，构造：年-月-1
	$date = new DateTime($year . '-' . $month . '-1');
	//加一个月
	//格式以字母P开头，为"期间"。 每个持续时间由一个整数值表示，后跟一个周期指示符。如果持续时间包含时间元素，则说明书的该部分前面加上字母T
	$date->add(new DateInterval('P1M'));
	//减一天
	$date->sub(new DateInterval('P1D'));
	
	return $date->format('Y-m-d 23:59:59');	
}

//获取网址的html代码
function FGetUrlHtml($url){
	$r = file_get_contents($url);
	return $r;
}

//获取用户ip
//FGetClientIP($gt=0)
//0 = 第1个ip，1=多ip字串(ip,ip)，2=ip数组
function FGetClientIP($gt = 0){
	$r = 'unknown';
	$M = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR'
	);
	foreach ($M as $k){
		if(array_key_exists($k, $_SERVER)){
			$l = array();
			foreach (explode(',', $_SERVER[$k]) as $t){
				$t = trim($t);
				if((bool)filter_var($t, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)){
					$l[] = $t;
				}
			}
			if(count($l) > 0){
				break;
			}
		}
	}
	if(count($l) > 0){
		if($gt == 1){
			$r = implode(',', $l);
		} else if($gt == 2){
			$r = $l;
		} else {
			$r = $l[0];
		}
	}
	return $r;
}

function get_client_ip(){
	return FGetClientIP();
}

//构建html5 的 header html（适应手机）
function FCreateHtml5Header($title,$jsArr = array(),$cssArr = array()){
	echo '<!DOCTYPE HTML>';
	echo '<html>';
	echo '<head>';
	echo '<meta content="text/html; charset=utf-8" http-equiv="Content-Type">';
	echo '<meta charset="utf-8">';
	echo '<title>', $title, '</title>';
	echo '<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width">';
	echo '<meta name="viewport" media="(device-height: 568px)" content="initial-scale=1.0,user-scalable=no,maximum-scale=1">';
	echo '<meta name="apple-mobile-web-app-capable" content="yes">';
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black">';
	echo '<meta name="format-detection" content="telephone=no">';
	if(is_array($cssArr)){
		if(count($cssArr) > 0){
			for ($i = 0; $i < count($cssArr); $i++){
				echo '<link href="', $cssArr[$i], '" rel="stylesheet" type="text/css">';
			}
		}
	}
	$H = 0;
	if(is_array($jsArr)){
		if(count($jsArr) > 0){
			for ($i = 0; $i < count($jsArr); $i++){
				if(stripos($jsArr[$i], 'jquery') !== false){
					$H = 1;
				}
				echo '<script type="text/javascript" src="', $jsArr[$i], '"></script>';
			}
		}
	}
	if(!$H){
		echo '<script type="text/javascript" src="/static/js/jquery-1.12.4.min.js"></script>';
	}
	echo '</head><body>';
}

//SHA256Hex加密
function FSha256Hex($str){
	$re = hash('sha256',$str,true);
	return bin2hex($re);
}

//取地址栏，并替换地址栏里的参数
//FReplaceUrlParm(array('page'=>'','clause'=>'','id'=>''));
//FReplaceUrlParm(array('page'=>'','clause'=>'','id'=>''),'path');
//FReplaceUrlParm(array('page'=>'','clause'=>'','id'=>''),'page');
function FReplaceUrlParm($arr = array(),$pageType = '',$url = ''){
	//自动取地址栏
	if(strlen($url) <= 0){
		switch($pageType){
			case 'path':
				$url = $_SERVER['REQUEST_URI'];
				break;
			case 'page':
				$url = $_SERVER['REQUEST_URI'];
				$url = substr($url,strripos($url,'/') + 1);
				if(substr($url,0,1) == '?'){
					$url = 'index.php' . $url;
				}
				break;
			default:
				$url = '?' . $_SERVER['QUERY_STRING'];
				break;
		}
	}
	
	//拆分，去除?号及之前的东西
	$urlArr = str_ireplace('&amp;','&',$url);
	if(stripos($urlArr,'?') !== false){
		$urlArr = explode('?',$urlArr);
		$urlArr = $urlArr[1];
	}
	
	//按&拆分
	$temp = '';
	$urlArr = explode('&',$urlArr);
	foreach($urlArr as $key=>$val){
		if(strlen($val)){
			$keyFound = 0;
			foreach($arr as $key1=>$val1){
				if(stripos($val,$key1 . '=') === 0){
					$keyFound = 1;
					break;
				}
			}
			
			if($keyFound <= 0){
				if(strlen($temp)){
					$temp .= '&';
				}
				$temp .= $val;
			}
		}
	}
	
	//补?号和&号
	if(stripos($temp,'?') !== false){
		//已经有?号，最后一个字符补&号
		if(substr($temp,-1) != '&'){
			$temp .= '&';
		}
	}else{
		if(substr($url,0,1) == '?'){
			$temp = '?' . $temp;
			
			if(substr($temp,-1) != '&'){
				$temp .= '&';
			}
		}else{
			$temp .= '?';
		}
	}
	
	return $temp;
}

function FFormatTime($datetime,$nullTips = '-',$format = ''){
	$time = strtotime($datetime);
	if($time === false || $datetime == '1000-01-01 00:00:00' || $datetime == '1970-01-01 08:00:00' || $time == '-30610253143'){
		return $nullTips;
	}else{
		if(strlen($format)){
			return date($format,$time);
		}else{
			return $datetime;
		}
	}
}

function FColorTF($tf,$tVal,$fVal,$trueIs1 = 1){
	if($trueIs1){
		if($tf){
			return '<i class="layui-icon layui-icon-ok color-yes"></i><span style="color:#57AC57">' . $tVal . '</span>';
		}else{
			return '<i class="layui-icon layui-icon-close color-no"></i><span style="color:#F26D15">' . $fVal . '</span>';
		}
	}else{
		if($tf){
			return '<i class="layui-icon layui-icon-close color-no"></i><span style="color:#F26D15">' . $tVal . '</span>';
		}else{
			return '<i class="layui-icon layui-icon-ok color-yes"></i><span style="color:#57AC57">' . $fVal . '</span>';
		}
	}
}

function FNumColor($num){
	$color = '#000000';
	switch($num){
		case 1:
			$color = '#5A9957';
			break;
		case 2:
			$color = '#5079B7';
			break;
		case 3:
			$color = '#DA7412';
			break;
		case 4:
			$color = '#AC58B8';
			break;
		case 5:
			$color = '#FF0000';
			break;
	}
	return $color;
}

/*
FCreateErrorPage(array(
	'title'=>L("提示"),
	'content'=>L("数据查询失败"),
	'btnStr'=>L('返回'),
	'url'=>FPrevUrl(),
	'isSuccess'=>0,
	'autoRedirectTime'=>0,
));
*/
function FCreateErrorPage($arr){
	echo '<!DOCTYPE html>
			<html lang="en">
				<head>
					<meta charset="utf-8" />
					<title>' , $arr['title'] , '</title>
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
					<link href="/assets/css/app.min.css" rel="stylesheet" type="text/css" />
					<style>.text-error{text-shadow:none;}</style>';

	$czWebSkinName = C('APP_TEMP_SRC');
	if(strlen($czWebSkinName) > 0 && $czWebSkinName != 'default'){
		echo '<link href="/assets/css/skin/' , $czWebSkinName , '.css" rel="stylesheet" type="text/css">';
	}

	echo '		</head>
				<body class="authentication-bg">
					<div class="account-pages mt-5 mb-5">
						<div class="container">
							<div class="row justify-content-center">
								<div class="col-lg-5">
									<div class="card">
										<div class="card-header pt-1 pb-1 text-center bg-primary">&nbsp;</div>
										<div class="card-body p-4">
											<div class="text-center">
												<h1 class="text-error">' , $arr['isSuccess'] ? '<i class="mdi mdi-emoticon-happy-outline"></i>' : '<i class="mdi mdi-emoticon-sad"></i>' , '</h1>
												<h4 class="text-uppercase text-danger mt-3">' , $arr['title'] , '</h4>
												<p class="text-muted mt-3">' , $arr['content'] , '</p>
												<a class="btn btn-info mt-3" href="#nolink" onclick="goUrl()"><i class="mdi mdi-reply"></i> ' , $arr['btnStr'] , '</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<script src="/assets/js/app.min.js"></script>
					<script>function goUrl(){
						if("" == "' , $arr['url'] , '"){
							window.history.back();
						}else{
							window.location.href = "' , $arr['url'] , '";
						}
					}
					if(' , intval($arr['autoRedirectTime']) , ' > 0){
						setTimeout(function(){goUrl();},' , intval($arr['autoRedirectTime']) , ');
					}
					</script>
				</body>
			</html>';
	exit;
}


