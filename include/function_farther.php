<?php
/*
补充的函数写在本页里，后期在适当的时候（更新版本），迁移进相应的函数文件中
*/

function admin_auto_load_lang($langfile = ''){
	global $CurrLangName;
	global $LoadLangFile;
	global $_lang;
	
	if(strlen($langfile) <= 0){
		$langfile = $LoadLangFile;
	}
	if(strlen($langfile) <= 0){
		$langfile = FGetCurrUrl(8);
	}
	$filename = $_SERVER['DOCUMENT_ROOT'] . '/lang/' . $CurrLangName . '/admin/' . $langfile;
	if(file_exists($filename) && substr($filename,-1) != '/'){
		$temp = include($filename);
		if(is_array($temp)){
			$_lang = array_merge($_lang, $temp);
		}
	}
}

function L($name = '_-|^_^|-_') {
    global $_lang;	
    if($name == '_-|^_^|-_'){
        return $_lang;
	}else{
		return isset($_lang[$name]) ? $_lang[$name] : $name;
	}
}

function session($name,$value = '_-|^_^|-_') {
	session_start();
	if($value == '_-|^_^|-_'){
		return $_SESSION[$name];
	}else{
		$_SESSION[$name] = $value;
	}
}
function S($name,$value = '_-|^_^|-_') {
	return session($name,$value);
}

function C($name) {
    global $webConfig;
	if(strpos($name,'.') !== false){
		$arr = explode('.',$name);
		if(count($arr) == 2){
			return $webConfig[$arr[0]][$arr[1]];
		}
	}
    return $webConfig[$name];
}

function M($tableName) {
    global $DB;
    return $DB->setActTable($tableName);
}

function ajaxReturn($data,$info='',$status=1) {
	$result  =  array();
	$result['status']  =  $status;
	$result['info'] =  $info;
	$result['data'] = $data;

	header('Content-type:text/json');
	
	exit(json_encode($result));
}

function this_error($info){
	ajaxReturn('',$info,0);
}

function this_success($info){
	ajaxReturn('',$info,1);
}

function arrAndKeysToNewArr($arr,$field = ''){
	$data = array_values($arr);
	if ($field) {
		$result = array();
		$field = explode(',', $field);
		foreach ($field as $key => $value) {
			$result[$value] = $data[$key];
		}
		return $result;
	}
	return $data;
}

function checkEmail($value) {
    if (preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $value)) {
        return true;
    } else {
        return false;
    }
}

function checkMobile($value) {
	if(is_numeric($value)){
		return true;
	}
	return false;

    if (preg_match("/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/", $value)) {
        return true;
    } else {
        return false;
    }
}

function encrypt($text, $type = 'md5') {
    return hash($type, $text);
}

function member_info($data = array()) {
    if (is_null($data)) {
        unset($_SESSION[C('USER_AUTH_KEY')]);
    } else if ($data) {
        $_SESSION[C('USER_AUTH_KEY')] = $data;
    } else {
        return $_SESSION[C('USER_AUTH_KEY')];
    }
}

function loginfo($dir,$loginfo) {
	$savePath = $_SERVER['DOCUMENT_ROOT'] . "/logs/" . $dir . "/" . date("Y/m/d/");
	@mkdir($savePath,0777,true);
	file_put_contents($savePath . date("Y-m-d") . '_' . md5(date('Y-m-d').'GH52#^g5ds') . ".log", date("Y-m-d H:i:s") . " " . $loginfo . PHP_EOL, FILE_APPEND);
}
function loginfo_debug($dir) {
	$str = "\r\n\r\n";
	$str .= (FIsHttps() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"] . "\r\n";
	$str .= "GET：\r\n";
	$str .= print_r($_GET,true);
	$str .= "\r\nPOST：\r\n";
	$str .= print_r($_POST,true);
	$str .= "\r\nphp://input：\r\n";
	$str .= print_r(file_get_contents('php://input', 'r'),true);
	$str .= "\r\n----------------\r\n\r\n";

	loginfo($dir,$str);
}

function getConfigValue($key, $servetid){
	global $DB;
	
	$configval = $DB->getDRow("select * from t_config_server where `configname` = '{$key}' and `server_id` = '{$servetid}'");
	if ($configval) {
		return $configval['configvalue'];
	}

	$configval = $DB->getDRow("select * from t_config where `name` = '{$key}' and status = '1'");
	if ($configval) {
		return $configval['value'];
	}

	return NULL;
}

function getSmtpConfig($serverid) {
	$smtp_arr = array();
	$smtp_arr['EMAIL_HOST'] = getConfigValue('EMAIL_HOST', $serverid);
	$smtp_arr['EMAIL_PORT'] = getConfigValue('EMAIL_PORT', $serverid);
	$smtp_arr['EMAIL'] = getConfigValue('EMAIL', $serverid);
	$smtp_arr['EMAIL_PASS'] = getConfigValue('EMAIL_PASS', $serverid);
	$smtp_arr['SUPPORT_SSL'] = getConfigValue('SUPPORT_SSL', $serverid);
	$smtp_arr['EMAIL_HONER'] = getConfigValue('EMAIL_HONER', $serverid);
	return $smtp_arr;
}

function getpaytype($type, $list = array()) {
    if ($type == 'remit') {
        return L("电汇转账");
    } else {
        return L("在线支付");
    }
}

function getinstatus($status) {
    if ($status == 0) {
        return L("审核中");
    } else if ($status == 1) {
        return L("已驳回");
    } else if ($status == 8) {
        return L("付款未入金");
    } else if ($status == 9) {
        return L("已入金");
    }
}

function getpaystatus($status) {
    if ($status == '1') {
        return L("支付完成");
    } elseif ($status == '-1') {
        return L("支付失败");
    } else {
        return L("未支付");
    }
}

function getattach($id){
	global $DB;
	$arr = array();
	$query = $DB->query("select * from `t_attach` where id in ({$id})");
	while($rs = $DB->fetchArray($query)){
		$arr[] = trim($rs['savepath'],'.') . $rs['savename'];
	}
	return $arr;
}

function getCurrMt4ServerName(){
	global $DRAdmin;
	global $DTMt4Server;
	$mt4Server = $DTMt4Server[$DRAdmin['server_id']];
	if($mt4Server){
		return ' (' . $mt4Server['mt4_name'] . ')';
	}else{
		return ' (???ERROR???)';
	}
}

//查询伞下用户列表，返回数组
//管理员直接获得所有用户列表
function getunderCustomerIds($member_ids,$isFloor1 = 0) {
	global $DB;
	
	$where = "`status` in (0,1)";
	$where .= " and `admin` = 0";//数据库中记录的是 管理员为2，这里的条件应当是 == 0？
	
	if(!$member_ids){
		$member_ids = '0';
	}

	$admin = false;
	if ($member_ids == "admin") {
		$admin = true;
	} else {
		if(is_array($member_ids)){
			$where .= " and `parent_id` in (" . implode(',',$member_ids) . ")";
		}else{
			$where .= " and `parent_id` in ({$member_ids})";
		}
	}

	$customers = $DB->getDTable("select id from t_member where {$where}");
	if (count($customers) <= 0) {
		return array();
	}

	$member_id_arr = array();
	foreach ($customers as $key => $value) {
		$member_id_arr[] = $value["id"];
	}

	if ($admin) {
		return $member_id_arr;
	}else if($isFloor1){
		//直接客户，第一层直接返回
		return $member_id_arr;
	} else {
		$memberchilds = getunderCustomerIds(implode(',',$member_id_arr));
		if ($memberchilds) {
			$member_id_arr = array_merge($member_id_arr, $memberchilds);
		}

		return $member_id_arr;
	}
}

function list_to_tree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                   
                    $parent['inode']=true;
                }
            }
        }
    }
    return $tree;
}

function admin_action_log() {
	global $DB;
	global $DRAdmin;
	global $Clause;
	
	/*$getData = '';
	foreach ($_GET as $key=>$value) {
		$getData .= $key . '=' . $value . '&';
	}
	$getData = substr($getData, 0, -1);*/
	
	$post = $_POST;
	if(is_array($post)){
		if($post){
			foreach($post as $key=>$val){
				if(stripos($key,'password') !== false){
					$post[$key] = substr($val,0,3) . '(..??)';
				}
			}
		}
	}
	
	$sqlFVArr = array(
		'user_id'=>$DRAdmin['id'],
		'server_id'=>$DRAdmin['server_id'],
		'ip'=>FGetClientIP(),
		'url'=>$_SERVER["PHP_SELF"],
		'postdata'=>$post ? json_encode($post) : '',
		'getdata'=>$_SERVER["QUERY_STRING"],
		'module_name'=>FGetCurrUrl(8),
		'action_name'=>$Clause,
		'create_time'=>FNow(),
	);
	$affectId = $DB->insert('t_logger',$sqlFVArr);
}

function wb_substr($str, $len = 140, $ext = '') {
    $str      = htmlspecialchars_decode(strip_tags(htmlspecialchars($str)));
    $strlenth = 0;
    $out      = '';
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
    foreach ($match[0] as $v) {
        preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs);
        if (!empty($matchs[0])) {
            $strlenth += 1;
        } elseif (is_numeric($v)) {
            $strlenth += 0.545;
        } else {
            $strlenth += 0.475;
        }
        if ($strlenth > $len) {
            $output .= $ext;
            break;
        }
        $output .= $v;
    }
    return $output;
}

function initAdmin($server_id){
	global $DB;
	global $DRAdmin;

	$DEAFAULT_ROLE_ADMIN = C('DEAFAULT_ROLE_ADMIN');
	
	$existadmin = $DB->getDRow("select * from t_member where f_roleId = '{$DEAFAULT_ROLE_ADMIN}' and server_id = '{$server_id}'");
	if(!$existadmin){
		$superadmin = $DB->getDRow("select * from t_member where f_roleId = '{$DEAFAULT_ROLE_ADMIN}' and server_id = '{$DRAdmin['server_id']}' order by id asc");
		if(!$superadmin){
			$superadmin = $DB->getDRow("select * from t_member where id = '{$DRAdmin['id']}'");
		}
		unset($superadmin['id']);
		$superadmin['server_id'] = $server_id;
		$superadmin['parent_id'] = 0;
		$superadmin['avatar'] = '';
		$superadmin['f_roleId'] = $DEAFAULT_ROLE_ADMIN;
		$member_id = $DB->insert("t_member",$superadmin);
	}
}

/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len=6,$type='',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借".$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLlIi和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    if($type!=4) {
        $chars   =   str_shuffle($chars);
        $str     =   substr($chars,0,$len);
    }else{
        // 中文随机字
        for($i=0;$i<$len;$i++){
          $str.= msubstr($chars, floor(mt_rand(0,mb_strlen($chars,'utf-8')-1)),1);
        }
    }
    return $str;
}

/**
+----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
+----------------------------------------------------------
 * @param string    $string   待转换的字符串
 * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string    $glue     分割符
+----------------------------------------------------------
 * @return string   处理后的字符串
+----------------------------------------------------------
 */
function hideStr($string, $bengin = 0, $len = 4, $type = 0, $glue = "@") {
    if (empty($string)) {
        return false;
    }

    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string  = mb_substr($string, 1, $strlen, "utf8");
            $strlen  = mb_strlen($string);
        }
    }
    if ($type == 0) {
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i])) {
                $array[$i] = "*";
            }

        }
        $string = implode("", $array);
    } else if ($type == 1) {
        $array = array_reverse($array);
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i])) {
                $array[$i] = "*";
            }

        }
        $string = implode("", array_reverse($array));
    } else if ($type == 2) {
        $array    = explode($glue, $string);
        $array[0] = hideStr($array[0], $bengin, $len, 1);
        $string   = implode($glue, $array);
    } else if ($type == 3) {
        $array    = explode($glue, $string);
        $array[1] = hideStr($array[1], $bengin, $len, 0);
        $string   = implode($glue, $array);
    } else if ($type == 4) {
        $left  = $bengin;
        $right = $len;
        $tem   = array();
        for ($i = 0; $i < ($length - $right); $i++) {
            if (isset($array[$i])) {
                $tem[] = $i >= $left ? "*" : $array[$i];
            }

        }
        $array = array_chunk(array_reverse($array), $right);
        $array = array_reverse($array[0]);
        for ($i = 0; $i < $right; $i++) {
            $tem[] = $array[$i];
        }
        $string = implode("", $tem);
    }
    return $string;
}

function mail_send($address, $subject, $body, $html = true, $attachment, $smtp_arr,$source = '') {
	global $DB;
	
	if(stripos($address,'@') === false){
		return false;
	}
	
	include_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPMailer/phpmailer.php');
	
	$mail = new PHPMailer(); //new一个PHPMailer对象出来
	$mail->CharSet = 'utf-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP(); // 设定使用SMTP服务
	$mail->SMTPAuth = true;                  // 启用 SMTP 验证功能
	$mail->SMTPDebug = 0;                     // 关闭SMTP调试功能
	$mail->Host = $smtp_arr['EMAIL_HOST'];      // SMTP 服务器
	$mail->Port = $smtp_arr['EMAIL_PORT'];                   // SMTP服务器的端口号
	$mail->Username = $smtp_arr['EMAIL'];  // SMTP服务器用户名
	$mail->Password = $smtp_arr['EMAIL_PASS'];            // SMTP服务器密码
	if ($smtp_arr['SUPPORT_SSL'] == '1')
		$mail->SMTPSecure = 'ssl';
	elseif ($smtp_arr['SUPPORT_SSL'] == '2')
			$mail->SMTPSecure = 'tls';
		
	$mail->SetFrom($smtp_arr['EMAIL'], $smtp_arr['EMAIL_HONER']); //发件人信息
	$mail->AddReplyTo($smtp_arr['EMAIL'], $smtp_arr['EMAIL_HONER']); //回复信息
	$mail->Subject = $subject; //邮件主题
	$html ? $mail->MsgHTML($body) : $mail->AltBody = $body; //邮件内容
	//C('MALL_SSL')&&$mail->SMTPSecure = "ssl";// 安全协议
	//  dump($mail->Port);exit;
	$mail->AddAddress($address, ''); //增加收件人

	if (is_array($attachment)) { // 添加附件
		foreach ($attachment as $key => $file) {
			is_file($file) && $mail->AddAttachment($file, $key);
		}
	}
	loginfo('mail_send',"send mail = " . $address . " subject=" . $subject . ' content=' . $body);
	
	$isopenapi = intval($DB->getField("select `configvalue` from `t_config_server` where `configname` = 'OPEN_MAIL_API_SEND' and server_id = '{$smtp_arr['server_id']}'"));
	if ($isopenapi == 1) {
		$status = $this->sendApiEmail($smtp_arr, $subject, $body, $address, $mail);
	} else {
		try{
			$status = $mail->Send();
		} catch (phpmailerException $e) {
			// 发送失败, 处理你的异常
			$status = 0;
		}
	}
	
	//ob_clean();
	//ob_flush();
	
	$updateArr = array('title' => $subject, 
					'temp_name' => $smtp_arr['temp_name'], 
					'contents' => $body, 
					'create_time' => time(), 
					'user_email' => $address, 
					'server_id' => $smtp_arr['server_id'], 
					'sendmail' => $smtp_arr['EMAIL'], 
					'table_name' => $smtp_arr['table_name'], 
					'table_id' => $smtp_arr['table_id'], 
					'status' => 1, 
					'error_info' => '');	
	if (!$status) {
		$updateArr['status'] = 0;
		$updateArr['error_info'] = $mail->ErrorInfo;
	}
	if ($source) {
		$DB->update('t_mail_log',$updateArr,"where id = '{$source}'");
	}else{
		$DB->insert('t_mail_log',$updateArr);
	}
	
	return $status ? true : $mail->ErrorInfo;
}

function sendApiEmail($config, $subject, $body, $address, $mail) {
	/*$apiUser= M("config_server")->where(array('configname' => 'MAIL_API_USER', 'server_id' => $config['server_id']))->getField('configvalue');
	$apiKey = M("config_server")->where(array('configname' => 'MAIL_API_KEY', 'server_id' => $config['server_id']))->getField('configvalue');
	$apiemail = M("config_server")->where(array('configname' => 'MAIL_API_SHOW_MAIL', 'server_id' => $config['server_id']))->getField('configvalue');

	$SoapClient = new SoapClient("http://app.focussend.com/webservice/FocusSendWebService.asmx?WSDL", array('trace' => 1));

	$FocusUser = new StdClass;
	$FocusUser->Email = $apiUser;
	$FocusUser->Password = sha1($apiKey);

	$FocusEmail = new StdClass;
	$FocusEmail->Body = $body;
	$FocusEmail->IsBodyHtml = true;
	$FocusTask = new StdClass;
	$FocusTask->TaskName = $config['temp_name'].date("YmdHis"). rand(10000, 99999);
	$FocusTask->Subject = $subject;
	$FocusTask->SenderName = $config['EMAIL_HONER'];
	$FocusTask->SenderEmail = $apiemail?$apiemail:$config['EMAIL'];
	$FocusTask->ReplyName = "";
	$FocusTask->ReplyEmail = "";
	$FocusTask->SendDate = date("Y-m-d\TH:m:s");
	$list= is_array($address)?$address:array($address);
	$FocusReceivers=array();
	foreach ($list as $key => $value){
		 $user = new StdClass;
		 $user->Email = $value;
		 $FocusReceivers[]=$user;
	}
	$result = $SoapClient->BatchSend(array("user" => $FocusUser, "email" => $FocusEmail, "task" => $FocusTask, "receivers" => $FocusReceivers));
	$rs=json_decode(json_encode( $result ),true);
	if($rs['BatchSendResult']=='success'){
		return true;
	}else{
		$mail->ErrorInfo=$rs['BatchSendResult'];
		$this->errorinfo=$rs['BatchSendResult'];
		return false;
	}*/
	return false;
}

function sendMsg_send($phone, $content, $arrryinfo) {
	global $DB;
	
	if(strlen($phone) <= 0){
		return array('error' => 1, 'info' => 'phone is empty');
	}
	
	include_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/ChuanglanSmsHelper/ChuanglanSmsApi.php');

	$msg    = new ChuanglanSmsApi();
	$result = $msg->sendSMS($phone, strip_tags($content));
	$userid = $arrryinfo['userid'] ? $arrryinfo['userid'] : 0;
	
	$updateArr = array(
					//'title' => '', 
					'template' => $arrryinfo['temp_name'], 
					'content' => $content, 
					'create_time' => time(), 
					'phone' => $phone, 
					'table_name' => $arrryinfo['table_name'], 
					'table_id' => $arrryinfo['table_id'], 
					'server_id' => $arrryinfo['server_id'], 
					'userid' => $userid, 
					//'msgtype' => '1', 
					'status' => 1, 
					'error_info' => ''
					);
	
	if (!is_null(json_decode($result))) {
		$output = json_decode($result, true);
		if (isset($output['code']) && $output['code'] == '0') {			
			$DB->insert('t_msg_log',$updateArr);
			
			return array('error' => 0, 'info' => '发送成功');
		} else {
			$updateArr['status'] = 0;
			$updateArr['error_info'] = $output['errorMsg'];
			
			$DB->insert('t_msg_log',$updateArr);
			
			return array('error' => 1, 'info' => $output['errorMsg']);
		}
	} else {
		$updateArr['status'] = 0;
		$updateArr['error_info'] = $result;
			
		$DB->insert('t_msg_log',$updateArr);
		
		return array('error' => 1, 'info' => $result);
	}
}

//转义JSON
function escapeJsonString($value) {
    $escapers     = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result       = str_replace($escapers, $replacements, $value);
    return $result;
}

function _check_member_scope($parentid, $memberid) {
	global $TempGetunderCustomerIds;
	
	$member_id_arr = $TempGetunderCustomerIds;
	if ($member_id_arr) {
		$member_id_arr = array_merge($member_id_arr, array($parentid));
	} else {
		$member_id_arr = array($parentid);
	}

	$isin = in_array($memberid, $member_id_arr);

	if (!$isin && $parentid != 'admin') {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("该客户不是您的下级"),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}

	return $member_id_arr;
}

function write_country_option(){
	$country = array('阿富汗','阿尔巴尼亚','阿尔及利亚','美属萨摩亚','安道尔','安哥拉','安圭拉','南极洲','安提瓜和巴布达','阿根廷','亚美尼亚','阿鲁巴','澳大利亚','奥地利','阿塞拜疆','巴哈马','巴林','孟加拉国','巴巴多斯','白俄罗斯','比利时','伯利兹','贝宁','百慕大','不丹','波利维亚','加勒比荷兰','波黑','博茨瓦纳','布韦岛','巴西','英属印度洋领地','英属维尔京群岛','文莱','保加利亚','布基纳法索','布隆迪','柬埔塞','喀麦隆','加拿大','佛得角','开曼群岛','中非','乍得','智利','中国','圣诞岛','科科斯群岛','哥伦比亚','科摩罗','刚果（金）','刚果（布）','库克群岛','哥斯达黎加','科特迪瓦','克罗地亚','古巴','库拉索','塞浦路斯','捷克','丹麦','吉布提','多米尼克','多米尼加','厄瓜多尔','埃及','萨尔瓦多','赤道几内亚','厄立特里亚','爱沙尼亚','埃塞俄比亚','马尔维纳斯群岛（福克兰）','法罗群岛','斐济群岛','芬兰','法国','法属圭亚那','法属波利尼西亚','法属南部领地','加篷','冈比亚','格鲁吉亚','德国','加纳','直布罗陀','希腊','格陵兰','格林纳达','瓜德罗普','关岛','危地马拉','根西岛','几内亚','几内亚比绍','圭亚那','海地','赫德岛和麦克唐纳群岛','洪都拉斯','中国香港','匈牙利','冰岛','印度','印度尼西亚','伊朗','伊拉克','爱尔兰','马恩岛','以色列','意大利','牙买加','日本','泽西岛','约旦','哈萨克斯坦','肯尼亚','基里巴斯','科威特','吉尔吉斯斯坦','奥兰群岛','老挝','拉脱维亚','黎巴嫩','莱索托','利比里亚','利比亚','列支敦士登','立陶宛','卢森堡','中国澳门','马其顿','马达加斯加','马拉维','马来西亚','马尔代夫','马里','马耳他','马绍尔群岛','马提尼克','毛里塔尼亚','毛里求斯','马约特','墨西哥','密克罗尼西亚联邦','摩尔多瓦','摩纳哥','蒙古','黑山','蒙塞拉特岛','摩洛哥','莫桑比克','缅甸','纳米比亚','瑙鲁','尼泊尔','荷兰','新喀里多尼亚','新西兰','尼加拉瓜','尼日尔','尼日利亚','纽埃','诺福克岛','朝鲜（北韩）','北马里亚纳群岛','挪威','阿曼','巴基斯坦','帕劳','巴勒斯坦国','巴拿马','巴布亚新几内亚','巴拉圭','秘鲁','菲侓宾','皮特凯恩群岛','波兰','葡萄牙','波多黎各','卡塔尔','留尼汪','罗马尼亚','俄罗斯','卢旺达','圣巴泰勒米岛','圣赫勒拿岛','圣基茨和尼维斯','圣卢西亚','法属圣马丁','圣皮埃尔和密克隆','圣文森特和格林纳丁斯','萨摩亚','圣马力诺','沙特阿拉伯','塞内加尔','塞尔维亚','塞舌尔','塞拉利昂','新加坡','荷属圣马丁','斯洛伐克','斯洛文尼亚','圣多美和普林西比','所罗门群岛','索马里','南非','南乔治亚岛和南桑威奇群岛','韩国(南韩)','南苏丹共和国','西班牙','斯里兰卡','苏丹','苏里南','斯瓦尔巴群岛和扬马延岛','斯威士兰','瑞典','瑞士','叙利亚','中国台湾','塔吉克斯坦','坦桑尼亚','泰国','东帝汶','多哥','托克劳','汤加','特立尼达和多巴哥','突尼斯','土耳其','土库曼斯坦','特克斯和凯科斯群岛','图瓦卢','美国本土外小岛屿','美属维尔京群岛','乌干达','乌克兰','阿拉伯联合酋长国','英国','美国','乌拉圭','乌兹别克斯坦','瓦努阿图','梵蒂冈','委内瑞拉','越南','瓦利斯和富图纳','西撒哈拉','也门','赞比亚','津巴布韦',);
	echo '<option value="">-=' , L("请选择") , '=-</option>';
	foreach($country as $key=>$val){
		echo '<option value="' , $val , '">' , L($val) , '</option>';
	}
}

//创建入金记录并入金
function creaet_inmoney($member_id, $loginid, $server_id) {
	global $DB;
	
	$member    = $DB->getDRow("select * from `t_member` where id = '{$member_id}'");
	$server_id = $server_id ? $server_id : $member['server_id'];
	$server  = $DB->getDRow("select * from `t_mt4_server` where id = '{$server_id}'");

	if (!$loginid) {
		$mtlogin = $DB->getDRow("select * from `t_member_mtlogin` where `member_id` = '{$member_id}' and `status` = 1 and `mtserver` = '{$server_id}' order by id desc");
		$loginid   = $mtlogin['loginid'];
	}

	$sqlS = '';
	$sqlS .= "select * from t_credit_setting where";
	$sqlS .= " `Status` = 1";
	$sqlS .= " and `ServerId` = '{$server_id}'";
	$sqlS .= " and f_startTime <= '" . time() . "'";
	$sqlS .= " and EndTime >= '" . time() . "'";
	$rsCS = $DB->getDRow($sqlS . " and `Type` = 'REG' order by Id desc");
	if(!$rsCS){
		loginfo('creaet_inmoney',"注册赠金：未找到赠金规则");
		return;
	}

	$DEFAULT_INMONEY_ST   = date('Y-m-d H:i:s',$rsCS['f_startTime']);
	$DEFAULT_INMONEY_ET   = date('Y-m-d H:i:s',$rsCS['EndTime']);
	$DEFAULT_INMONEY_OverDays   = intval($rsCS['f_overDays']);
	$number   = floatval($rsCS['Result']);

	loginfo('creaet_inmoney',"注册赠金：" . $member_id . '|' . $loginid . '|$' . $number . '|' . $server_id . '|' . $DEFAULT_INMONEY_ST . '|' . $DEFAULT_INMONEY_ET);

	if(strlen($DEFAULT_INMONEY_ST) <= 0){
		return false;
	}
	if(strtotime($DEFAULT_INMONEY_ST) > time()){
		return false;
	}
	if(strlen($DEFAULT_INMONEY_ET) <= 0){
		return false;
	}
	if(strtotime($DEFAULT_INMONEY_ET) < time()){
		return false;
	}

	if ($loginid && $number > 0) {
		$chk = $DB->getDRow("select * from t_credit_record where MemberId = '{$member_id}' and MtLogin = '{$loginid}' and `Object` = 'reg'");
		if(!$chk){
			$data = array();
			$data['CreditId']= $rsCS['Id'];
			$data['Object']= 'reg';
			$data['InMoney']= 0;
			$data['ObjectId']= 0;
			$data['MemberId']= $member_id;
			$data['MtLogin']= $loginid;
			$data['Formula']= $number;
			$data['Result']= $number;
			$data['CreateTime']= time();
			$data['CreateUser']= 0;
			$data['UpdateTime']= time();
			$data['UpdateUser']= 0;
			$data['Status']= "0";
			$data['ServerId']= $server_id;
			if($DEFAULT_INMONEY_OverDays > 0){
				$data['f_endTime']= time() + 60 * 60 * 24 * $DEFAULT_INMONEY_OverDays;
			}
			$data['f_fromAbout']= '注册赠金';
			if($rsCS['f_zye_days'] > 0 && $rsCS['f_zye_lot'] > 0){
				$data['f_zye_endTime'] = time() + 60 * 60 * 24 * $rsCS['f_zye_days'];
			}
			$crId = $DB->insert("t_credit_record",$data);

			//-------------------------

			require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

			$reportModel = new ReportModel($server['db_name'],$server['ver']);
			$ticket = $reportModel->getTicketByComment('7',"Credit reg#" . $crId,$server['ver'],$loginid);
			if(!$ticket){
				try {
					$mt4api = new MtApiModel($server['ver']);
					$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
					$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
					if ($retarry['ret'] != 0) {
						loginfo('creaet_inmoney',"api错误：" . $retarry['info']);

						return false;
					}
					$inid = $mt4api->credit($loginid, $number, "Credit reg#" . $crId); //MT赠金
					$inid = $inid['ret'];
				} catch (Exception $e) {
					loginfo('creaet_inmoney',"api错误：" . L('连接mtapi接口异常'));

					return false;
				}
				if ($inid == '-88') {//入金失败
					loginfo('creaet_inmoney',"api错误：" . L('重复赠金'));

					return false;
				} else if ($inid <= '-1') {//入金失败
					loginfo('creaet_inmoney',"api错误：" . L('赠金审核失败') . '|' . $inid);

					return false;
				}

				$data = array();
				$data['Status'] = 1;
				$data['CheckUser'] = 0;
				$data['CheckTime'] = time();
				$data['Ticket'] = $inid;
				$data['InTime'] = time();
				$DB->update('t_credit_record',$data,"Id = '{$crId}'");

				loginfo('creaet_inmoney',"赠金成功：$" . $number);

				return true;
			}else{
				loginfo('creaet_inmoney',"经检测，重复了2：" . $crId);
			}
		}else{
			loginfo('creaet_inmoney',"经检测，重复了1：" . $chk['Id']);
		}
	}

	return false;
}

//创建入金记录并入金
function creaet_inmoney_OLD($member_id, $login, $server_id) {
	global $DB;
	
	$member    = $DB->getDRow("select * from `t_member` where id = '{$member_id}'");
	$server_id = $server_id ? $server_id : $member['server_id'];
	$mtserver  = $DB->getDRow("select * from `t_mt4_server` where id = '{$server_id}'");
	$default   = $DB->getField("select configvalue from `t_config_server` where `server_id` = '{$server_id}' and `configname` = 'DEFAULT_INMONEY'");

	if (!$login) {
		$mtlogin = $DB->getDRow("select * from `t_member_mtlogin` where `member_id` = '{$member_id}' and `status` = 1 and `mtserver` = '{$server_id}' order by id desc");
		$login   = $mtlogin['login'];
	}
	if ($mtserver['real'] == 0 && $login && $default > 0) {
		$loginid      = $login;
		$number       = $default;

		$data = array();
		$data['member_id']     = $member_id;
		$data['bankcode']      = '';
		$data['mtid']          = $loginid;
		$data['certificate']   = '';
		$data['number']        = $number;

		$pay                   = $DB->getDRow("select * from `t_pay` where `server_id` = '{$server_id}' and `Status` = 1 order by Id asc");
		$type         = $pay['PayCode'];
		if($pay['f_pa'] == 'auto'){
			if($mtserver['ver'] == 5){
				$autoPa = $DB->getDRow("select * from " . $mtserver['db_name'] . ".mt5_prices where Symbol = '{$pay['f_symbolsER']}'");
				if($autoPa){
					$pay['f_fixedER'] = $autoPa['BidLast'];
					//ASK
				}
			}else{
				$autoPa = $DB->getDRow("select * from " . $mtserver['db_name'] . ".mt4_prices where SYMBOL = '{$pay['f_symbolsER']}'");
				if($autoPa){
					$pay['f_fixedER'] = $autoPa['BID'];
					//ASK
				}
			}
		}

		$existpayno            = false;
		do {
			$data['payno'] = date('YmdHis') . rand(100000, 999999);

			//检测订单是否重复，重复重新生成
			$existpayno          = $DB->getDRow("select * from `t_inmoney` where `payno` = '{$data['payno']}'");
		} while ($existpayno);
		$data['type']        = $type;
		$data['create_time'] = time();
		$data['status']      = 0;
		$data['server_id']   = $member['server_id'];

		$data['exchange'] = $pay['f_fixedER'];
		$data['price']    = round(floatval($data['exchange']) * floatval($number), 2); //实际支付价格

		if ($data['exchange'] <= 0) {
			return false;
		}

		$id = $DB->insert("t_inmoney",$data);
		autoinmoney($id);
	} else {
		return false;
	}
}

function autoinmoney($id) {
	global $DB;
	
	$info      = $DB->getDRow("select * from `t_inmoney` where `id` = '{$id}'");
	if (!$info) {
		return array('error' => '1', 'info' => "入金信息不存在！");
	}
	if ($info['status'] == 1) {
		return array('error' => '1', 'info' => "该信息已被驳回！");
	}
	if ($info['status'] < 0) {
		return array('error' => '1', 'info' => "该信息已被作废！");
	}
	if ($info['status'] == 9) {
		return array('error' => '1', 'info' => "该信息已经审核通过了！");
	}
	
	$DB->startTrans();

	$mtlogin = $DB->getDRow("select * from `t_member_mtlogin` where `loginid` = '{$info['mtid']}' and `member_id` = '{$info['member_id']}' and `status` = 1");
	if (!$mtlogin) {
		return array('error' => '1', 'info' => "该账号未绑定！");
	}

	$server                = $DB->getDRow("select * from `t_mt4_server` where `id` = '{$mtlogin['mtserver']}' and `status` = 1");
	if (!$server) {
		return array('error' => '1', 'info' => "mt4服务器不存在！");
	}

	$mt4api                 = new MtApiModel($server['ver']);
	$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
	$retarry                = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
	if ($retarry['ret'] != 0) {
		$DB->rollback();
		
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L($retarry['info']),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}
	$inid = $mt4api->balance($info['mtid'], $info['number'], "Deposit maxib#" . $info['id'], 1); //MT4入金
	$inid = $inid['ret'];
	
	if ($inid == '-88') {
		//入金失败
		$DB->commit();
		return array('error' => '1', 'info' => "重复入金！");
	} else if ($inid <= '-1') {
		//入金失败
		$DB->rollback();
		return array('error' => '1', 'info' => "入金失败！");
	}

	$data = array();
	$data['status']     = 9;
	$data['paystatus']  = 1;
	$data['adminid']    = 0;
	$data['visit_time'] = time();
	$data['inid']       = $inid;
	$data['intime']     = time();
	$data['content']    = "模拟仓自动入金";
	$id                 = $DB->update("t_inmoney",$data,"where id = '{$id}'");

	if ($id) {
		//$id = D("inmoney")->where($map)->save(array('inid'=>$ids,'intime'=>time()));
		$DB->commit();
		//  $result = api('gold://Mail/sendBalanceInEmail', array($map['id']));
		return true;
	} else {
		$DB->rollback();
		return false;
	}
}

//检测是否存在上下级死循环
function checkDie($mid, $parentId,$parents = '') {
	global $DB;
	
	if ($parentId == $mid) {
		return true;
	}
	
	if(strlen($parents) <= 0){
		$parents = $mid;
	}
	
	$rs = $DB->getDRow("select id,parent_id from t_member where id = '{$parentId}'");
	if(!$rs){
		return false;
	}
	if(stripos(',' . $parents . ',',',' . $rs['id'] . ',') !== false){
		return true;
	}
	if($rs['parent_id'] > 0){
		return checkDie($mid, $rs['parent_id'],$parents . ',' . $rs['id']);
	}else{
		return false;
	}
}

function checkmemeber($userid) {
	global $DB;
	
	$nextmember = getunderCustomerIds($userid, 'member');
	if ($nextmember) {
		this_error(L("该用户下级还有员工帐号，不能调整角色"));
	}
}	
	
function saveGroup() {
	global $DB;
	
	$bbookgroups = FPostStr('bbookgroups');
	$abookgroups = FPostStr('abookgroups');
	$user_id = FPostInt('user_id');
	
	if(strlen($bbookgroups) <= 0 && strlen($abookgroups) <= 0){
		return;
	}

	$data = array();
	if (strlen($abookgroups))
		$data['abook'] = $abookgroups;
	if (strlen($bbookgroups))
		$data['bbook'] = $bbookgroups;
	
	$DB->update("t_member",$data,"where id = '{$user_id}'");
}
	
//设置员工
function setMember($uid) {
	global $DB;
	
	$member = $DB->getDRow("select * from t_member where id = '{$uid}'");
	$setparenet = 0; //释放上级
	if ($member['parent_id']) {
		$parent = $DB->getDRow("select * from t_member where id = '{$member['parent_id']}'");
		if ($parent['userType'] == 'member') {
			$setparenet = 1;
		}
	}
	if ($setparenet == 0) {
		$DB->update("t_member",array('parent_id' => 0, 'userType' => 'member'),"where id = '{$uid}'");
	} else {
		$DB->update("t_member",array('userType' => 'member'),"where id = '{$uid}'");
	}
}	
	

//获取风控风险用户
function getMonitorRisk(){
	return array();
	/*
    $server_id = session("user.serverid");
    $depositModel        = new DepositModel(session('user.mt4dbname'));
    //黑名单预警账号
    $where['configname'] = 'BLACK_NUMBER';
    $black_number        = D('ConfigServer')->where($where)->field('configvalue')->find();
    $black_number        = explode(',', $black_number['configvalue']);
    //风险用户（PC端手动操作的用户）
    $blacklist = D('Blacklist')->where(['status' => 2, 'server_id' => $server_id])->Field('login_id,m_id')->select();
    if ($blacklist) {
        foreach ($blacklist as $k => $v) {
            if ($v['login_id'] == 0) {
                $mtloginwhere['member_id'] = $v['m_id'];
                $mtloginwhere['status']    = '1'; //状态正常
                $mtloginwhere['mt_type']   = '0'; //返佣帐户
                $mtusers                   = M('member_mtlogin')->field('loginid')->where($mtloginwhere)->find();
                if (!empty($mtusers['loginid'])) {
                    $black_login = $mtusers['loginid'];
                }

            } else {
                $black_login = $v['login_id'];

            }
            if (!empty($black_login)) {
                $black_list[] = $black_login;
            }
        }
    }
    //黑名单预警组
    $where['configname'] = 'BLACK_GROUP_NUMBER';
    $black_group         = D('ConfigServer')->where($where)->field('configvalue')->find();
    $userswhere['GROUP'] = ['in', $black_group['configvalue']];
    $users_login_arr     = $depositModel->where($userswhere)->getField('LOGIN', true);
    //保证金仓位数量
    $where['configname'] = 'MARGIN_POSITION_NUMBER';
    $position_number     = D('ConfigServer')->where($where)->field('configvalue')->find();
    if ($position_number['configvalue']) {
        $positionwhere['MARGIN_LEVEL'] = ['elt', $position_number['configvalue']];
        $positionwhere['_string']      = ' MARGIN_LEVEL <> 0';
        $position_login_arr            = $depositModel->where($positionwhere)->getField('LOGIN', true);
    }  

    //风险系数
    $where['configname'] = 'RISK_PRIORITY_NUMBER';
    $risk                = D('ConfigServer')->where($where)->field('configvalue')->find();
    $riskList            = D('Member')->getRiskProblemList($risk['configvalue']);
    //所有预警用户 = 黑名单预警账号+黑名单预警组下所有账号+风险用户+超过保证金仓位数量用户+超过风险系数用户
    $loginList = array_unique(array_merge($black_list ? $black_list : [], empty($black_number[0]) ? [] : $black_number, $users_login_arr ? $users_login_arr : [], $position_login_arr ? $position_login_arr : [], $riskList ? $riskList : []));
    return $loginList;*/
}

function getpaytypedesc($paycode, $list) {
    foreach ($list as $key => $value) {
        if ($paycode == $value['PayCode']) {
            return $value['description'];
        }

    }
    return L('未知通道');
}

/**
 * 赠金处理
 * @param unknown $info
 */
function credit_credit($info,$object){
	global $DB;
	global $DRAdmin;

	$time = time();
	$count = 0;
	
	//时间段内、达标数值范围
	//首次和单笔，各检测一次
	
	//所有获得赠金的规则
	$csList = array();
	
	$sqlS = '';
	$sqlS .= "select * from t_credit_setting where";
	$sqlS .= " `Status` = 1";
	$sqlS .= " and `ServerId` = '{$info['server_id']}'";
	$sqlS .= " and f_startTime <= '" . $time . "'";
	$sqlS .= " and EndTime >= '" . $time . "'";
	$sqlS .= " and `Condition` <= '" . $info['number'] . "'";
	$sqlS .= " and f_conditionEnd >= '" . $info['number'] . "'";
	
	$mt4user = getuser($info['mtid']);
	if (!$mt4user) {
		loginfo('credit_credit',"用户" . $info['mtid'] . "，mt4中不存在");
		return;
	}
	$sqlS .= " and f_group like '%," . str_replace('\\','\\\\\\\\',$mt4user['GROUP']) . ",%'";
	
	//首次入金
	$rsCS = $DB->getDRow($sqlS . " and `Type` = 'BALANCE_FIRST'");
	if($rsCS){
		$exist = $DB->getDRow("select * from t_inmoney where `mtid` = '{$info['mtid']}' and `member_id` = '{$info['member_id']}' and `server_id` = '{$info['server_id']}' and `status` = 9 and id <> '{$info['id']}'");
		if($exist){
			loginfo('credit_credit',$info['mtid']."非首次入金，不满足条件");
		}else{
			$rsCS['f_fromAbout'] = '首次入金：' . $info['payno'];
		
			$csList[] = $rsCS;
		}
	}
	
	//单笔
	$rsCS = $DB->getDRow($sqlS . " and `Type` = 'BALANCE_PER'");
	if($rsCS){
		$rsCS['f_fromAbout'] = '单笔入金：' . $info['payno'];
		
		$csList[] = $rsCS;
	}

	//将符合的规则，赠金
	foreach($csList as $key=>$val){
		//检测是否关联活动报名
		if($val['f_activityId'] > 0){
			$chkRs = $DB->getDRow("select * from t_activity_join where f_pid = '{$val['f_activityId']}' and f_uid = '{$info['member_id']}' and f_status = '审核通过'");
			if(!$chkRs){
				loginfo('credit_credit',$val['Id'] . "要求报名并通过活动（" . $val['f_activityId'] . "），用户（" . $info['member_id'] . "）未报名或未通过");
				continue;
			}
		}

		if($val['Scale'] == 'Scale'){
			$formula = $val['Result']."%*".$info['number'];
			$result = $val['Result']*$info['number']/100;
		}else if($val['Scale'] == 'Fixed'){
			$formula =  $val['Result'];
			$result = $val['Result'];
		}else{
			$formula = 'ERROR-' . $val['Scale'];
			$result = 0;
		}
		
		$data = array();
		$data['CreditId']= $val['Id'];
		$data['Object']= $object;
		$data['InMoney']= $info['number'];
		$data['ObjectId']= $info['id'];
		$data['MemberId']= $info['member_id'];
		$data['MtLogin']= $info['mtid'];
		$data['Formula']= $formula;
		$data['Result']= $result;
		$data['CreateTime']= time();
		$data['CreateUser']= $DRAdmin['id'];
		$data['UpdateTime']= time();
		$data['UpdateUser']= $DRAdmin['id'];
		$data['Status']= "0";
		$data['ServerId']= $info['server_id'];
		if(intval($val['f_overDays']) > 0){
			$data['f_endTime'] = time() + 60 * 60 * 24 * $val['f_overDays'];
		}
		$data['f_fromAbout']= $val['f_fromAbout'];
		if($val['f_zye_days'] > 0 && $val['f_zye_lot'] > 0){
			$data['f_zye_endTime'] = time() + 60 * 60 * 24 * $val['f_zye_days'];
		}
		$ret = $DB->insert("t_credit_record",$data);
		
		if($ret){
			$count++;
		}
		
		loginfo('credit_credit',$info['mtid']."赠金状态".$ret . "|" . $val['f_fromAbout']);
	}

	if($count > 0){
		return true;
	}else{
		return false;
	}
}

function getouttype($type,$forwordmtlogin = 0) {
	global $DB;
	if($forwordmtlogin > 0){
		return L("MT转账");
	}

	$DRPay = $DB->getDRow("select a.*,b.f_title,b.f_pa,b.f_ers,b.f_fixedEROut,b.f_symbolsER,b.f_erAlgo from (select * from t_out_config where Id = '{$type}') a left join t_pay_currency b on a.f_currencyId = b.id");
	if (!$DRPay) {
		return '(ERROR)';
	}

	return $DRPay['PayName'];

    if ($type == 1) {
        return L("银联");
    } else if ($type == 2) {
        return L("电汇");
    } else if ($type == 3) {
        return L("MT转账");
    }
}

function getoutstatus($status) {
    if ($status == 0) {
        return L("审核中");
    } else if ($status == 1) {
        return L("已驳回");
    } else if ($status == 8) {
        return L("已下账");
    } else if ($status == 9) {
        return L("已出金");
    }
}

function MillionRound($number,$fixed){
    if($number>100000000){
        return round($number/100000000,$fixed).L("亿");
    }if($number>1000000){
        return round($number/10000,$fixed).L("万");
    }else{
        return round($number,$fixed);
    }
}

// 生成数据唯一标识 用于数据安全更新
function uuid() {
    return md5(uniqid(mt_rand(), true));
}

/**
 * 注册开户
 * @param type $res  用户信息
 * @return type
 */
function reg_open_mt4login($info, $mtserver) {
	global $DB;
	
	$openfield = getConfigValue('OPEN_MT4_FILEDS', $mtserver); //系统开户字段
	$server = $DB->getDRow("select * from t_mt4_server where `id` = '{$mtserver}' and `status` = 1");
	
	$datas['name'] = $openfield == 'chineseName' ? $info['chineseName'] : $info['nickname'];
	$datas['mt4_server_id'] = $server['id'];
	$datas['email'] = $info['email'];

	//客户选择杠杆
	if ($info['leverage']) {
		$datas['leverage'] = $info['leverage'];
	}else {
		$datas['leverage'] = getConfigValue('DEFAULT_LEVER', $mtserver);
	}
	//默认杠杆
	$datas['member_id'] = $info['id'];
	$datas['status'] = 0;
	$datas['phone'] = $info['phone'];
	$datas['city'] = $info['city'];
	$datas['address'] = $info['address'];
	$datas['create_time'] = time();
	
	$mt4group = $info['register_group'];
	if (!$mt4group) {
		if ($info['parent_id'] != '0') {
			//查找上级的返佣分组
			$mtmap = "where member_id = '{$info['parent_id']}' and mtserver = '{$mtserver}' and status = 1";
			//推广码优先 如果有推广码 则分组跟随推广码所在的分组
			if ($info['invent_code']) {
				$mtmap .= " and loginid = '{$info['invent_code']}'";
			}else{
				$mtmap .= " and mt_type = '0'";
			}
			$group = $DB->getDRow("select * from t_member_mtlogin where {$mtmap}");
			
			if ($group) {
				if($server['ver'] == 5){
					$mt4user = $DB->getDRow("select * from " . $server['db_name'] . ".mt5_users where `Login` = '{$group['loginid']}'");
					if ($mt4user) {
						$mt4group = $mt4user['Group'];
					}
				}else{
					$mt4user = $DB->getDRow("select * from " . $server['db_name'] . ".mt4_users where `LOGIN` = '{$group['loginid']}'");
					if ($mt4user) {
						$mt4group = $mt4user['GROUP'];
					}
				}
			}
		}
	}
	if (!$mt4group) {
		//如果有同名账户，则选择同名账户的原有组
		$samenamelogin = $DB->getDRow("select * from t_member_mtlogin where member_id = '{$info['id']}' and mtserver = '{$mtserver}' and mt_type = 0 and status = 1");
		if ($samenamelogin) {
			if($server['ver'] == 5){
				$mt4user = $DB->getDRow("select * from " . $server['db_name'] . ".mt5_users where `Login` = '{$samenamelogin['loginid']}'");
				if ($mt4user) {
					$mt4group = $mt4user['Group'];
				}
			}else{
				$mt4user = $DB->getDRow("select * from " . $server['db_name'] . ".mt4_users where `LOGIN` = '{$samenamelogin['loginid']}'");
				if ($mt4user) {
					$mt4group = $mt4user['GROUP'];
				}
			}
		}
	}
	if (!$mt4group) {
		//如果上级无分组，则选择第一个组
		$group = $DB->getDRow("select * from t_groups where server_id = '{$server['id']}' and status = 0");
		if ($server) {
			$mt4group = $group['group'];
		}
	}
	
	$datas['group'] = $mt4group;
	
	//$datas['group'] = str_replace('\\','\\\\',$datas['group']);
	
	$id = $DB->insert("t_mt4_apply",$datas);
	
	if ($id) {
		$MT4_AUTO_CHECKED = getConfigValue('MT4_AUTO_CHECKED', $mtserver);
		if ($MT4_AUTO_CHECKED == '1') {
			//开启自动审核
			$result = api('gold://Mt4api/genericmtlogin', array($info['id'], $server['id'], $mt4group, $datas['leverage'], $datas['name'], "", ""));
			//loginfo('api_gold_Mt4api_genericmtlogin',"call api:" . json_encode($result));
			if ($result['status'] == '0') {
				//生成成功
				$checkresult = api('gold://Mt4api/checkMt4', array($result['data']['user'], $result['data']['pass'], $info['id'], $server['id'], $mt4group, $id));
				if ($checkresult['status'] == '0') {
					//发送开户邮件给客户
					$resultchecked = api('gold://Mail/sendMtCheckedmail', array($info['id'], $result['data']['user'], $result['data']['pass'], $result['data']['rdpass']));
					if ($resultchecked['status'] == '0') {
						creaet_inmoney($info['id'], $result['data']['user'], $datas['mt4_server_id']); //开户后自动入金

						//发送开户邮件成功
						$rst = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], "autochecked", $server['id']));
						return array('error' => 0, 'info' => L('验证成功，并已自动开户，开户信息已经发送到您注册的邮箱中'));
					} else {
						//发送失败，通知管理员
						$rst = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], L("发送开户 邮件给客户失败，请手动处理"), $server['id']));
						return array('error' => 0, 'info' => L('验证成功，并已自动开户，等待邮件发送'));
					}
				}
			} else {
				//审核失败 发送邮件提醒管理员人为审核
				$result = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], $result['info'], $server['id']));
			}
		} else {
			$result = api('gold://Mail/sendApplyMt4Notify', array($info['nickname'], '', $server['id']));
		}
		return array('error' => 0, 'info' => L('验证成功'));
	} else {
		return array('error' => 0, 'info' => L('验证失败'));
	}
}

/**
*
* 生成推广二维码
* @param
*/
function showqrcode($value, $size, $savePath = false) {
	include_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/phpqrcode/phpqrcode.php');

	if (is_file($_SERVER['DOCUMENT_ROOT'] . $savePath)) {
		return $savePath;
	}

	$QRcode = New QRcode();
	$errorCorrectionLevel = "L";
	// 点的大小：1到10
	$matrixPointSize = $size ? $size : 8;
	$QRcode->png($value, $_SERVER['DOCUMENT_ROOT'] . $savePath, $errorCorrectionLevel, $matrixPointSize);
	return $savePath;
}


//列表页权限
function getid_arr($q = '', $usertype = '', $group = '' , $reject = '',$source = '') {
	global $DRAdmin;
	global $DB;
	
	if($DRAdmin['_dataRange'] <= 1){
		$member_id_arr = getCustomerIds($DRAdmin['id']);
		$member_id_arr = array_merge($member_id_arr, array($DRAdmin['id']));
	}else{
		$member_id_arr = getCustomerIds('admin');
		$member_id_arr = array_merge($member_id_arr, array($DRAdmin['id']));
	}
	
	$table = "t_member a";
	$join = 't_member_mtlogin b ON a.id = b.member_id';
	$where = "where a.server_id = '{$DRAdmin['server_id']}'";

	//搜索
	if ($q || $group) {
		if ($q) {
			if ($source == 1) {
				$where .= " and a.status = 1";
			}else{
				$where .= " and b.status = 1";
			}
			if ($usertype) {
				$where1 = $where . " and (a.email ='" . $q . "'  OR  b.loginid ='" . $q . "' or a.nickname like '" . $q . "%')";
				
				$userdata = $DB->getDRow("select b.loginid,a.id from {$table} right join {$join} {$where1}");
				if (!$userdata) {
				   this_error(L('该用户不存在'));
				}

				$userdataloginid = $DB->getDTable("select b.loginid from {$table} right join {$join} {$where1}");

				$arr_login       = array();
				if ($userdataloginid) {
					foreach ($userdataloginid as $k => $v) {
						$arr_login[$k] = $v['loginid'];
						if($v['loginid'] == null){
							unset($arr_login[$k]);
						}
					}
				}
			}
			if ($userdata['id']) {
				//输入了查询条件账户
				if($DRAdmin['_dataRange'] <= 1){
					if (!in_array($userdata['id'], $member_id_arr)) {
						this_error(L('不是您的下级'));
					}
				}
			}
			if ($usertype == 2) {
				if ($userdata) {
					$allMID  = getCustomerIds($userdata['id']);
					if(!$allMID){
						$allMID = array('0');
					}
					$loginID = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$allMID) . ") and status = 1");
				}			   
			} elseif ($usertype == 1) {
				$loginID = $arr_login;
				$allMID = $userdata['id'];
			} elseif ($usertype == 3) {
				if ($userdata) {
					$allMID  = getCustomerIds($userdata['id']);
					if(!$allMID){
						$allMID = array('0');
					}
					$loginID = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$allMID) . ") and status = 1");

					$loginID = array_merge($arr_login, $loginID ? $loginID : array());
					$allMID  = array_merge($allMID ? $allMID : array(), array($userdata['id']));
				}				
			}
		}

		if ($group) {
			if (!$q) {
				$loginID = $DB->getField2Arr("select b.id,b.loginid from {$table} right join {$join} {$where} and b.member_id in (" . implode(',',$member_id_arr) . ")");
			}

			if($GLOBALS['deposit_mt4dbver'] == 5){
				$userswhere = "where `Group` in ('" . (is_array($group) ? str_ireplace('\\','\\\\',implode("','",$group)) : $group) . "') and Login in (" . (is_array($loginID) ? implode(",",$loginID) : ($loginID ? $loginID : '0')) . ")";
				$users_login_arr = $DB->getField("select Login from " . $DRAdmin['mt4dbname'] . ".mt5_users {$userswhere}",true);
			}else{
				$userswhere = "where `GROUP` in ('" . (is_array($group) ? implode("','",$group) : $group) . "') and LOGIN in (" . (is_array($loginID) ? implode(",",$loginID) : ($loginID ? $loginID : '0')) . ")";
				$users_login_arr = $DB->getField("select LOGIN from " . $DRAdmin['mt4dbname'] . ".mt4_users {$userswhere}",true);
			}

			$loginID = $users_login_arr;
			$allMID  = $DB->getField2Arr("select id,member_id from t_member_mtlogin where member_id in (" . implode(',',$member_id_arr) . ") and loginid in (" . ($users_login_arr ? implode(',',$users_login_arr) : '0') . ") and status = 1");
		}
	} else {
		$loginID = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$member_id_arr) . ") and status = 1");
		$allMID  = $member_id_arr;
	}
		 

	if ($reject) {
		//获取权限所有id
		$allloginID = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$member_id_arr) . ") and status = 1");
		$all_MID  = $member_id_arr;
		$loginID = array_diff($allloginID,$loginID ? $loginID : array());
		if (!is_array($allMID)) {
			$allMID = array($allMID);
		}
		$allMID = array_diff($all_MID,$allMID ? $allMID : array());
	}


	return array('loginID' => $loginID ? $loginID:'无', 'allMID' => $allMID ? $allMID : '无');
}

function getTotalList($kind = '',$loginID,$page = '',$status='',$start='',$end='',$total=''){
	global $DRAdmin;
	global $DB;
	
	if(!is_array($loginID)){
		if($loginID == '无'){
			$loginID = '0';
		}
		$loginID = array($loginID);
	}
	
	if($page == -999){
		$pageSql = '';
	}else{
		$page     = !empty($page) ? $page : 1;
		$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
		$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;
	}

	$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
	//账号
	if($mtserver['ver'] == 5){
		$sub = "(select Login as LOGIN,`Name` as `NAME` from {$DRAdmin['mt4dbname']}.mt5_users where Login in (" . implode(',',$loginID) . ") group by Login)";
	}else{
		$sub = "(select LOGIN,`NAME` from {$DRAdmin['mt4dbname']}.mt4_users where LOGIN in (" . implode(',',$loginID) . ") group by LOGIN)";
	}
	$starttime              = $start ? strtotime($start . ' 00:00:00') : 0;
	$endtime                = $end ? strtotime($end . ' 23:59:59') : time();

	$inwhere = "where 1 = 1";
	$outwhere = "where 1 = 1";

	if ($status) {
	   if ($status == 1) {
			if ($mtserver['ver'] == 5) {
				$inwhere .= " and `COMMENT` like 'Deposit%'";
				$outwhere .= " and `COMMENT` like 'Withdraw%'";
			}else{
				$inwhere .= " and `COMMENT` like 'Deposit maxib#%'";
				$outwhere .= " and `COMMENT` like 'Withdraw maxib#%'";
			}

		} elseif ($status == 2) {
			if ($mtserver['ver'] == 5) {
				$inwhere .= " and COMMENT like 'Deposit%' and COMMENT not like 'Deposit %'";
				$outwhere .= " and COMMENT like 'Withdraw%' and COMMENT not like 'Withdraw %'";
			}else{
				$inwhere .= " and COMMENT like 'Deposit%' and COMMENT not like 'Deposit maxib#%'";
				$outwhere .= " and COMMENT like 'Withdraw%' and COMMENT not like 'Withdraw maxib#%'";
			}
		}
	}else{
		$inwhere .= " and `COMMENT` like 'Deposit%'";
		$outwhere .= " and `COMMENT` like 'Withdraw%'";
	}
	
	/////////入金////////////////
	if($mtserver['ver'] == 5){
		$inwhere .= " and `Action` in (2,3)";
		$inwhere .= " and Login in (" . implode(',',$loginID) . ")";
		$inwhere .= " and Profit > 0";
		$inwhere .= " and `Time` between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$sub2 = "(select Login as LOGIN from {$DRAdmin['mt4dbname']}.mt5_deals {$inwhere} group by Login)";
	}else{
		$inwhere .= " and `CMD` in (6,7)";
		$inwhere .= " and LOGIN in (" . implode(',',$loginID) . ")";
		$inwhere .= " and PROFIT > 0";
		$inwhere .= " and CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$sub2 = "(select LOGIN from {$DRAdmin['mt4dbname']}.mt4_trades {$inwhere} group by LOGIN)";
	}
	/////////出金////////////////
	if($mtserver['ver'] == 5){
		$outwhere .= " and `Action` in (2,3)";
		$outwhere .= " and Login in (" . implode(',',$loginID) . ")";
		$outwhere .= " and Profit < 0";
		$outwhere .= " and `Time` between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$sub3 = "(select Login from {$DRAdmin['mt4dbname']}.mt5_deals {$outwhere} group by Login)";
	}else{
		$outwhere .= " and `CMD` in (6,7)";
		$outwhere .= " and LOGIN in (" . implode(',',$loginID) . ")";
		$outwhere .= " and PROFIT < 0";
		$outwhere .= " and CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$sub3 = "(select LOGIN from {$DRAdmin['mt4dbname']}.mt4_trades {$outwhere} group by LOGIN)";
	}
	/////////赠金////////////////
	if($mtserver['ver'] == 5){
		$creditwhere = "where Action = 7";
		$creditwhere .= " and Login in (" . implode(',',$loginID) . ")";
		$creditwhere.= " and `Time` between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$sub4 = "(select Login from {$DRAdmin['mt4dbname']}.mt5_deals {$creditwhere} group by Login)";
	}else{
		$creditwhere = "where CMD = 7";
		$creditwhere .= " and LOGIN in (" . implode(',',$loginID) . ")";
		$creditwhere.= " and OPEN_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$sub4 = "(select LOGIN from {$DRAdmin['mt4dbname']}.mt4_trades {$creditwhere} group by LOGIN)";
	}
	/////////mt转账///////////////
	$mtAwhere = "where visit_time between '{$starttime}' and '{$endtime}'";
	$mtAwhere .= " and mtid in (" . implode(',',$loginID) . ")";
	$mtAwhere .= " and status = 9";
	$mtAwhere .= " and `type` = 3";
	$sub5 = "(select mtid as LOGIN from t_outmoney {$mtAwhere})";
	
	$mtBwhere = "where visit_time between '{$starttime}' and '{$endtime}'";
	$mtBwhere .= " and forwordmtlogin in (" . implode(',',$loginID) . ")";
	$mtBwhere .= " and status = 9";
	$mtBwhere .= " and `type` = 3";
	$sub6 = "(select LOGIN from (((select forwordmtlogin as LOGIN from t_outmoney {$mtBwhere}) union {$sub5})) c group by LOGIN)";

	/////////mt佣金
	$mtCwhere = "where NEWLOGIN in (" . implode(',',$loginID) . ")";
	$mtCwhere .= " and CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
	$sub7 = "(select NEWLOGIN as LOGIN from t_new_mt_commission {$mtCwhere} group by LOGIN)";
 
	//导出数据
	if ($total == 1) {
		if ($kind == 1) {
			//入金
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub2} b on a.LOGIN = b.LOGIN");
		} elseif ($kind == 2) {
			//出金
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub3} c on a.LOGIN = c.LOGIN");
		} elseif ($kind == 3) {
			//MT转账报表
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a inner join {$sub6} b on a.LOGIN = b.LOGIN");
		  
		} elseif ($kind == 4) {
			//赠金
			
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub4} c on a.LOGIN = c.LOGIN");

		}elseif ($kind == 6) { 
			//MT返佣
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub7} c on a.LOGIN = c.LOGIN");
		}else {
			//资金报表
			if($GLOBALS['deposit_mt4dbver'] == 5){
				$zjwhere = "where a.Login in (" . implode(',',$loginID) . ")";
				$zjwhere .= " and (a.Balance <> 0 or(
										(b.Comment LIKE 'Deposit%' and b.Time BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."') 
										or 
										(b.Comment LIKE 'Withdraw%' and b.Time BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."')
										))";
				$listsub = "(select a.Login loginid,a.Name loginname from {$DRAdmin['mt4dbname']}.mt5_users a left join {$DRAdmin['mt4dbname']}.mt5_deals b on a.Login = b.Login {$zjwhere} group by a.Login)";
				$data['list'] = $DB->getDTable("select * from {$listsub} a");
			}else{
				$zjwhere = "where a.LOGIN in (" . implode(',',$loginID) . ")";
				$zjwhere .= " and (a.BALANCE <> 0 or(
										(b.COMMENT LIKE 'Deposit%' and b.CLOSE_TIME BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."') 
										or 
										(b.COMMENT LIKE 'Withdraw%' and b.CLOSE_TIME BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."')
										))";
				$listsub = "(select a.LOGIN loginid,a.NAME loginname from {$DRAdmin['mt4dbname']}.mt4_users a left join {$DRAdmin['mt4dbname']}.mt4_trades b on a.LOGIN = b.LOGIN {$zjwhere} group by a.LOGIN)";
				$data['list'] = $DB->getDTable("select * from {$listsub} a");
			}
		}
	}else{
		if ($kind == 1) {
			//入金
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub2} b on a.LOGIN = b.LOGIN {$pageSql}");

			$data['count'] = intval($DB->getField("select count(*) as count1 from {$sub} a right join {$sub2} b on a.LOGIN = b.LOGIN"));
		} elseif ($kind == 2) {
			//出金
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub3} c on a.LOGIN = c.LOGIN {$pageSql}");
			
			$data['count'] = intval($DB->getField("select count(*) as count1 from {$sub} a right join {$sub3} c on a.LOGIN = c.LOGIN"));
		} elseif ($kind == 3) {
			//MT转账报表
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a inner join {$sub6} b on a.LOGIN = b.LOGIN {$pageSql}");
			
			$data['count'] = intval($DB->getField("select count(*) as count1 from {$sub} a inner join {$sub6} b on a.LOGIN = b.LOGIN"));
		} elseif ($kind == 4) {
			//赠金
			//echo "select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub4} c on a.LOGIN = c.LOGIN {$pageSql}";
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub4} c on a.LOGIN = c.LOGIN {$pageSql}");

			$data['count'] = intval($DB->getField("select count(*) as count1 from {$sub} a right join {$sub4} c on a.LOGIN = c.LOGIN"));
		}elseif ($kind == 6) { 
			//MT返佣
			$data['list'] = $DB->getDTable("select a.LOGIN loginid,a.NAME loginname from {$sub} a right join {$sub7} c on a.LOGIN = c.LOGIN {$pageSql}");

			$data['count'] = intval($DB->getField("select count(*) as count1 from {$sub} a right join {$sub7} c on a.LOGIN = c.LOGIN"));
		}else {
			//资金报表，很耗资源，需要优化
			if($GLOBALS['deposit_mt4dbver'] == 5){
				$zjwhere = "where a.Login in (" . implode(',',$loginID) . ")";
				$zjwhere .= " and (a.Balance <> 0 or (
										(b.Comment LIKE '%Deposit%' and b.Time BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."') 
										or 
										(b.Comment LIKE '%Withdraw%' and b.Time BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."')
										))";
	
				$listsub = "(select a.Login loginid,a.Name loginname from {$DRAdmin['mt4dbname']}.mt5_users a left join {$DRAdmin['mt4dbname']}.mt5_deals b on a.Login = b.Login {$zjwhere} group by a.Login)";
				
				$data['list'] = $DB->getDTable("select * from {$listsub} a {$pageSql}");
				
				$data['count'] = intval($DB->getField("select count(*) as count1 from {$listsub} a"));
			}else{
				$zjwhere = "where a.LOGIN in (" . implode(',',$loginID) . ")";
				$zjwhere .= " and (a.BALANCE <> 0 or (
										(b.COMMENT LIKE '%Deposit%' and b.CLOSE_TIME BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."') 
										or 
										(b.COMMENT LIKE '%Withdraw%' and b.CLOSE_TIME BETWEEN '".date('Y-m-d H:i:s', $starttime)."' and '".date('Y-m-d H:i:s', $endtime)."')
										))";
	
				$listsub = "(select a.LOGIN loginid,a.NAME loginname from {$DRAdmin['mt4dbname']}.mt4_users a left join {$DRAdmin['mt4dbname']}.mt4_trades b on a.LOGIN = b.LOGIN {$zjwhere} group by a.LOGIN)";
				
				$data['list'] = $DB->getDTable("select * from {$listsub} a {$pageSql}");
				
				$data['count'] = intval($DB->getField("select count(*) as count1 from {$listsub} a"));
			}
		}
	}

	return $data;
}


function getCapitaList($kind = '', $page = '', $start = '', $end = '', $status = '', $loginID = '', $allMID = '',$total='') {
	global $DRAdmin;
	global $DB;
	
	if(!is_array($allMID)){
		if($allMID == '无'){
			$allMID = '0';
		}
		$allMID = array($allMID);
	}
	
	if(!is_array($loginID)){
		if($loginID == '无'){
			$loginID = '0';
		}
		$loginID = array($loginID);
	}
	
	$server_id            = $DRAdmin['server_id'];

	$reportModel   = new ReportModel($DRAdmin['mt4dbname'],$DRAdmin['ver']);
	//$depositModel  = new DepositModel(session('user.mt4dbname'));
	//$mt5_buyModel = new Mt5_buyModel(session('user.mt4dbname'));

	$data = getTotalList($kind,$loginID,$page,$status,$start,$end,$total);

	$starttime              = $start ? strtotime($start . ' 00:00:00') : 0;
	$endtime                = $end ? strtotime($end . ' 23:59:59') : time();
	$mt4where = "where CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
	//mt转账时间
	$mtwhere = "where visit_time between '{$starttime}' and '{$endtime}'";
	
	$table = "t_member a";
	$join = 't_member_mtlogin b ON a.id = b.member_id';
	$where = "where a.server_id = '{$DRAdmin['server_id']}' and b.status = 1";
	foreach ($data['list'] as $k => $v) {
		$user_data = $DB->getDRow("select a.id, a.`email`,a.parent_id, a.`nickname`, a.`userType`,a.`level`, a.`realname`, a.server_id, b.loginid from {$table} right join {$join} {$where} and b.loginid in ({$v['loginid']})");
		$data['list'][$k]['id'] = $user_data['id'];
		$data['list'][$k]['email'] = $user_data['email'];
		$data['list'][$k]['parent_id'] = $user_data['parent_id'];
		$data['list'][$k]['nickname'] = $user_data['nickname'];
		$data['list'][$k]['userType'] = $user_data['userType'];
		$data['list'][$k]['level'] = $user_data['level'];
		$data['list'][$k]['realname'] = $user_data['realname'];
		$data['list'][$k]['server_id'] = $user_data['server_id'];
		//mt名字
		// $getUser                       = $reportModel->getMTnickname($v['loginid']);
		// $data['list'][$k]['loginname'] = $getUser['NAME'] ? $getUser['NAME'] : '无';
		//获取上级id
		$data['list'][$k]['parent_id'] = $user_data['parent_id'] ? $user_data['parent_id'] : 0;
		if ($data['list'][$k]['parent_id'] != 0) {
			$parentMem = $DB->getDRow("select * from t_member where id = '" . $data['list'][$k]['parent_id'] . "'");
			if ($parentMem) {
				$data['list'][$k]['parent_name']  = $parentMem['nickname'];
				$data['list'][$k]['parent_email'] = $parentMem['email'];
			} else {
				$data['list'][$k]['parent_name'] = "无";
			}
		} else {
			$data['list'][$k]['parent_name'] = "无";
		}

		if ($kind == 1) {
			//入金报表
			$data['list'][$k]['innumber'] = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 1, '', $status);
			$data['list'][$k]['incount']  = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 1, 1, $status);
		} elseif ($kind == 2) {
			//出金报表
			$outnumber                     = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 0, '', $status);
			$data['list'][$k]['outnumber'] = round($outnumber, 2);
			$data['list'][$k]['outcount']  = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 0, 1, $status);
		} elseif ($kind == 3) {
			//MT转账报表
			$innumber                     = $DB->getDRow("select count(*) count,sum(number) number from t_outmoney {$mtwhere} and status = 9 and `type` = 3 and forwordmtlogin = '{$v['loginid']}'");
			$data['list'][$k]['innumber'] = round($innumber['number'], 2);
			$data['list'][$k]['incount'] = $innumber['count'];
			
			//出金量
			$outnumber                     = $DB->getDRow("select count(*) count,sum(number) number from t_outmoney {$mtwhere} and status = 9 and `type` = 3 and mtid = '{$v['loginid']}'");
			$data['list'][$k]['outnumber'] = round($outnumber['number'], 2);
			$data['list'][$k]['outcount']  = $outnumber['count'];
		} elseif ($kind == 4) {
			//赠金
			$credit                          = $reportModel->getCreditTotal($v['loginid'], $mt4where);
			$data['list'][$k]['credit']      = round($credit['PROFIT'], 2);
			$data['list'][$k]['countCredit'] = $credit['count'] ? $credit['count'] : 0;
			// $totalcredit += $data['list'][$k]['credit'];
			// $totalcountCredit += $data['list'][$k]['countCredit'];

		} elseif ($kind == 6) { 
			$starttime              = $start ? strtotime($start . ' 00:00:00') : 0;
			$endtime                = $end ? strtotime($end . ' 23:59:59') : time();
			$maplogin = "where CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
			$maplogin .= " and NEWLOGIN in (" . $v['loginid'] . ")";
			$totalDatas = $DB->getDRow("select count(*) count,sum(PROFIT) as PROFIT from t_new_mt_commission {$maplogin}");
			$data['list'][$k]['PROFIT'] = round($totalDatas['PROFIT'],2);
			$data['list'][$k]['count'] = $totalDatas['count'];
		}else {
			//资金报表
			//客户入金量
			$innumber                     = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 1);
			$data['list'][$k]['innumber'] = round($innumber, 2);

			$data['list'][$k]['incount'] = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 1, 1);
			//客户出金量
			$outnumber                     = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 0);
			$data['list'][$k]['outnumber'] = round($outnumber, 2);
			$data['list'][$k]['outcount']  = $reportModel->sumRiskEarningOutBalance($v['loginid'], $mt4where, 0, 1);
			//净入金
			$netmoney                     = $innumber + $outnumber;
			$data['list'][$k]['netmoney'] = round($netmoney, 2);
			if($DRAdmin['ver'] == 5){
				$getUsers                     = $reportModel->getUserData($v['loginid'], 'Balance as BALANCE');
			}else{
				$getUsers                     = $reportModel->getUserData($v['loginid'], 'BALANCE');
			}
			$data['list'][$k]['balance']  = $getUsers['BALANCE'] ? $getUsers['BALANCE'] : 0;

		}

	}

	//汇总数据
	$mwhere = "where id in (" . ($allMID ? implode(',',$allMID) : '0') . ")";
	$mwhere .= " and status in (0,1)";
	$mwhere .= " and server_id = '{$server_id}'";
	$collect['agentnumber']  = intval($DB->getField("select count(*) as count1 from t_member {$mwhere} and userType = 'agent'"));
	$collect['directnumber'] = intval($DB->getField("select count(*) as count1 from t_member {$mwhere} and userType = 'direct'"));
	$collect['membernumber'] = intval($DB->getField("select count(*) as count1 from t_member {$mwhere} and userType = 'member'"));

	//缓存是否存在数据
	//$is_have_Cache = $DB->getDRow("select * from t_mt_member_trades where loginid in (" . implode(',',$mtlist) . ") and server_id = '{$DRAdmin['server_id']}'");

	if ($kind == 4) {
		$totalcredit                 = $reportModel->getCreditTotal($loginID ? $loginID : '', $mt4where);
		$collect['totalcredit']      = round($totalcredit['PROFIT'], 2);
		$collect['totalcountCredit'] = $totalcredit['count'] ? $totalcredit['count'] : 0;
	} elseif ($kind == 3) {
		$mtwhere .= " and `type` = 3";
		$mtwhere .= " and server_id = '{$server_id}'";
		if ($reject) {
			$mtwhere .= " and (mtid not in (" . ($loginID ? implode(',',$loginID) : '1') . "))";
		} else {
			$mtwhere .= " and mtid in (" . ($loginID ? implode(',',$loginID) : '1') . ")";
		}
		$totalnumber              = $DB->getDRow("select count(*) count,sum(number) number from t_outmoney {$mtwhere}");
		$collect['totalinnumber'] = round($totalnumber['number'], 2);
		$collect['totalincount']  = $totalnumber['count'];
	} elseif ($kind == 1) {
		$totalinumber             = $reportModel->getCapitalSummary($loginID ? $loginID : '1', $mt4where, 1, $status);
		$collect['totalinnumber'] = $totalinumber['PROFIT'];
		$collect['totalincount']  = $totalinumber['count'];
	} elseif ($kind == 2) {
		$totaloutnumber            = $reportModel->getCapitalSummary($loginID ? $loginID : '1', $mt4where, 2, $status);
		$collect['totaloutnumber'] = $totaloutnumber['PROFIT'];
		$collect['totaloutcount']  = $totaloutnumber['count'];
	}elseif ($kind == 6) { 
		$starttime              = $start ? strtotime($start . ' 00:00:00') : 0;
		$endtime                = $end ? strtotime($end . ' 23:59:59') : time();
		
		$maplogin = "where CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		$maplogin .= " and NEWLOGIN in (" . ($loginID ? $loginID : '1') . ")";		
		$totalDatas = $DB->getDRow("select count(*) count,sum(PROFIT) as PROFIT from t_new_mt_commission {$maplogin}");
		$collect['totalcount'] = $totalDatas['count'];
		$collect['totalprofit'] = round($totalDatas['PROFIT'],2);
	}else {
		$summary                   = $reportModel->getCapitalSummary($loginID ? $loginID : '1', $mt4where,'','');
		$collect['totalinnumber']  = $summary['inmoney']['PROFIT'];
		$collect['totalincount']   = $summary['inmoney']['count'];
		$collect['totaloutnumber'] = $summary['outmoney']['PROFIT'];
		$collect['totaloutcount']  = $summary['outmoney']['count'];
		$collect['totalnetmoney']  = $summary['outmoney']['PROFIT'] + $summary['inmoney']['PROFIT'];
	}


	// $collect['totalcredit']      = $totalcredit;
	// $collect['totalcountCredit'] = $totalcountCredit;
	$data['totaldata'] = $collect;
	return $data;
}

function getCustomerIds($member_ids, $from = '',$server_id = '') {
	global $DB;
	global $DRAdmin;
	
	$memberwhere = "where status = 1";
	$memberwhere .= " and admin <> 1";
	$memberwhere .= " and server_id = '{$DRAdmin['server_id']}'";

	if(!$member_ids){
		$member_ids = array('0');
	}

	//客户数
	$admin = false;
	if ($member_ids == 'admin') {
		$admin = true;
	} else {
		$memberwhere .= " and parent_id in (" . ($member_ids && is_array($member_ids) ? implode(',',$member_ids) : $member_ids) . ")";
	}
	if ($from) {
		$memberwhere .= " and userType in (" . implode(',',$from) . ")";
	}
	//查询下级客户
	$customers = $DB->getDTable("select id from t_member {$memberwhere}");

	//把客户的id放入数组里面
	if (count($customers) <= 0) {
		return array();
	}

	$member_id_arr = array();
	foreach ($customers as $key => $value) {
		if (@!in_array($value['id'], $member_ids)) {
			$member_id_arr[] = $value['id'];
		}
	}

	//管理员，查询所有客户
	if ($admin) {
		return $member_id_arr;
	} else {
		//迭代查询下级
		$memberchilds = getunderCustomerIds($member_id_arr, $from);

		if ($memberchilds) {
			$member_id_arr = array_merge($member_id_arr, $memberchilds);
		}
		return $member_id_arr;
	}
}


function getPositionList($page = '', $start = '', $end = '', $type = '', $loginID = '', $allMID = '') {
	global $DB;
	global $DRAdmin;
	
	$server_id = $DRAdmin['server_id'];

	$where = '';
	$page = !empty($page) ? $page : 1;
	if ($start != '' or $end != '') {
		$starttime          = $start ? strtotime($start . ' 00:00:00') : 0;
		$endtime            = $end ? strtotime($end . ' 23:59:59') : time();
		if ($type == 1 || $type == 3) {
			$where = "where OPEN_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		}else{
			$where = "where CLOSE_TIME between '" . date('Y-m-d H:i:s', $starttime) . "' and '" . date('Y-m-d H:i:s', $endtime) . "'";
		}
	}
	$reportModel = new ReportModel($DRAdmin['mt4dbname']);
	if ($type == 1) {
		$data = $reportModel->getPositionOrderReport($page, $loginID, $where, 1);
	} elseif ($type == 2) {
		$data = $reportModel->getPositionOrderReport($page, $loginID, $where, 2);
	} elseif ($type == 3) {
		
		$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
		// $timezone               = D('Mt4Server')->where(['id' => session("user.serverid")])->field('time_zone')->find();
		$gmtime = time()+ 3600 * 8;
		if ($mtserver['ver'] == 5) {
			$where = "where TimeSetup between '" . ($starttime ? $starttime : 0) . "' and '" . ($endtime ? $endtime : $gmtime) . "'";
		}

		$data = $reportModel->getPositionOrderReport($page, $loginID, $where, 3);
	}
	
	$table = "t_member a";
	$join = 't_member_mtlogin b ON a.id = b.member_id';
	foreach ($data['list'] as $k => $v) {
		$user = $DB->getDRow("select a.id, a.`nickname`, a.level,a.userType,a.parent_id from {$table} right join {$join} where b.loginid = '{$v['LOGIN']}' and b.status = 1");
		$data['list'][$k]['nickname'] = $user['nickname'];
		$data['list'][$k]['level']    = $user['level'];
		$data['list'][$k]['userType'] = $user['userType'];
		//获取上级id
		$parent_id = $user['parent_id'] ? $user['parent_id'] : 0;
		if ($parent_id != 0) {
			$parentMem      = $DB->getDRow("select * from t_member where id = '{$parent_id}'");
			if ($parentMem) {
				$data['list'][$k]['parent_name']  = $parentMem['nickname'];
				$data['list'][$k]['parent_email'] = $parentMem['email'];
			} else {
				$data['list'][$k]['parent_name'] = "无";
			}
		} else {
			$data['list'][$k]['parent_name'] = "无";
		}
		// $innumber                 = $reportModel->sumRiskEarningOutBalance($v['LOGIN'], $mt4where, 1);
		// $outnumber                = $reportModel->sumRiskEarningOutBalance($v['LOGIN'], $mt4where, 0);
		// $data['list'][$k]['risk'] = ROUND((($v['PROFIT']) / ($innumber + $outnumber)) * 100, 2);
	}

	$collect['PROFIT']  = $data['total']['PROFIT'];
	$collect['VOLUME']  = $data['total']['VOLUME'];
	$collect['BALANCE'] = $data['total']['BALANCE'];
	$data['totaldata']  = $collect;
	return $data;
}

function _request($key,$val = ''){
	if(strlen($val) <= 0){
		return FRequestStr($key);
	}else{
		$_GET[$key] = $val;
	}
}

function cz_where_to_str($where){
	$sql = "";
	foreach($where as $key=>$val){
		if(stripos($key,'.') !== false){
			$key1 = $key;
		}else{
			$key1 = '`' . $key . '`';
		}
		
		if(strtolower($key) == '_string'){
			if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
			$sql .= "({$val})";
		}else if(is_array($val)){
			if(is_array($val[0]) && is_array($val[1])){
				if(strtolower($val[0][0]) == 'egt'){
					if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
					$sql .= "{$key1} >= '{$val[0][1]}'";
				}
				if(strtolower($val[1][0]) == 'elt'){
					if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
					$sql .= "{$key1} <= '{$val[1][1]}'";
				}
			}else if(strtolower($val[0]) == 'in'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				if(is_array($val[1]) && $val[1]){
					//有bug，如果值类似：a,b,c，需要修正
					$sql .= "{$key1} in (" . implode(',',$val[1]) . ")";
				}else if(!is_array($val[1]) && strlen($val[1])){
					$sql .= "{$key1} in ({$val[1]})";
				}else{
					$sql .= "{$key1} in ('987654321')";
				}
			}else if(strtolower($val[0]) == 'between'){
				if(is_array($val[1])){
					$betweenVal_ = $val[1];
				}else{
					$betweenVal_ = explode(',',$val[1]);
				}
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} between '" . $betweenVal_[0] . "' and '" . $betweenVal_[1] . "'";
			}else if(strtolower($val[0]) == 'gt'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} > '{$val[1]}'";
			}else if(strtolower($val[0]) == 'lt'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} < '{$val[1]}'";
			}else if(strtolower($val[0]) == 'eq'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} = '{$val[1]}'";
			}else if(strtolower($val[0]) == 'neq'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} <> '{$val[1]}'";
			}else if(strtolower($val[0]) == 'egt'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} >= '{$val[1]}'";
			}else if(strtolower($val[0]) == 'elt'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} <= '{$val[1]}'";
			}else if(strtolower($val[0]) == 'like' && count($val) == 2){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "{$key1} like '{$val[1]}'";
			}else if(strtolower($val[0]) == 'like' && count($val) == 3 && is_array($val[1]) && strtolower($val[0]) == 'or'){
				if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
				$sql .= "({$key1} like '{$val[1][0]}' or {$key1} like '{$val[1][1]}')";
			}
		}else{
			if(strlen($sql) <= 0){$sql = "where ";}else{$sql .= " and ";}
			$sql .= "{$key1} = '{$val}'";
		}
	}
	return $sql;
}

function getuser($loginid){
	global $DB;
	global $DRAdmin;
	
	$dbName = $DRAdmin['mt4dbname'];
	if(strlen($dbName) <= 0){
		$dbName = $GLOBALS['deposit_mt4dbname'];
	}
	
	if($GLOBALS['deposit_mt4dbver'] == 5){
		$arr = $DB->getDRow("select * from " . $dbName . ".mt5_users where Login = '{$loginid}'");
		$arr1 = array();
		foreach($arr as $key=>$val){
			$arr1[strtoupper($key)] = $val;
		}
		
		$arr = $DB->getDRow("select * from " . $dbName . ".mt5_accounts where Login = '{$loginid}'");
		foreach($arr as $key=>$val){
			$arr1[strtoupper($key)] = $val;
		}
		
		return $arr1;
	}else{
		return $DB->getDRow("select * from " . $dbName . ".mt4_users where LOGIN = '{$loginid}'");
	}
}

function mt4_trad() {
	if (_request('TICKET'))
		$where['TICKET'] = _request('TICKET');
	if (_request('CMD') != '')//类型
		$where['CMD'] = array('in', _request('CMD'));
	if (_request('CMD') != '' && _request('searchuser') == 'all')
		$where['CMD'] = array('in', _request('CMD'));
	if (_request('LOGIN') && _request('searchuser') != 'all') //账号查询
		$where['LOGIN'] = _request('LOGIN');
	if (_request('SYMBOL'))
		$where['SYMBOL'] = _request('SYMBOL');
	if ($_REQUEST['closetime'] == 1) {
		$where['CLOSE_TIME'] = array('gt', '1970-01-01 00:00:00');
	} else if ($_REQUEST['closetime'] == 2) {
		$where['CLOSE_TIME'] = array('eq', '1970-01-01 00:00:00');
	}
	if (_request('OPEN_TIME_s') && _request('OPEN_TIME_e')) {
		$where['OPEN_TIME'] = array(array('EGT', _request('OPEN_TIME_s')), array('ELT', _request('OPEN_TIME_e')));
	} elseif ($_REQUEST['OPEN_TIME_s']) {
		$where['OPEN_TIME'] = array('EGT', _request('OPEN_TIME_s'));
	} elseif ($_REQUEST['OPEN_TIME_e']) {
		$where['OPEN_TIME'] = array('ELT', _request('OPEN_TIME_e'));
	}
	if (_request('OPEN_PRICE_s') && _request('OPEN_PRICE_e')) {
		$where['OPEN_PRICE'] = array(array('EGT', _request('OPEN_PRICE_s')), array('ELT', _request('OPEN_PRICE_e')));
	} elseif ($_REQUEST['OPEN_PRICE_s']) {
		$where['OPEN_PRICE'] = array('EGT', _request('OPEN_PRICE_s'));
	} elseif ($_REQUEST['OPEN_PRICE_e']) {
		$where['OPEN_PRICE'] = array('ELT', _request('OPEN_PRICE_e'));
	}
	if (_request('CLOSE_TIME_s') && _request('CLOSE_TIME_e')) {
		$where['CLOSE_TIME'] = array(array('EGT', _request('CLOSE_TIME_s')), array('ELT', _request('CLOSE_TIME_e') . ' 23:59:59'));
	} elseif ($_REQUEST['CLOSE_TIME_s']) {
		$where['CLOSE_TIME'] = array('EGT', _request('CLOSE_TIME_s'));
	} elseif ($_REQUEST['CLOSE_TIME_e']) {
		$where['CLOSE_TIME'] = array('ELT', _request('CLOSE_TIME_e') . ' 23:59:59');
	}
	if (_request('PROFIT_s') && _request('PROFIT_e')) { //盈亏查询
		$where['PROFIT'] = array(array('EGT', _request('PROFIT_s')), array('ELT', _request('PROFIT_e')));
	} elseif ($_REQUEST['PROFIT_s']) {
		$where['PROFIT'] = array('EGT', _request('PROFIT_s'));
	} elseif ($_REQUEST['PROFIT_e']) {
		$where['PROFIT'] = array('ELT', _request('PROFIT_e'));
	}
	if (_request('MODIFY_TIME_s') && _request('MODIFY_TIME_e')) {
		$where['MODIFY_TIME'] = array(array('EGT', _request('MODIFY_TIME_s')), array('ELT', _request('MODIFY_TIME_e')));
	} elseif ($_REQUEST['MODIFY_TIME_s']) {
		$where['MODIFY_TIME'] = array('EGT', _request('MODIFY_TIME_s'));
	} elseif ($_REQUEST['MODIFY_TIME_e']) {
		$where['MODIFY_TIME'] = array('ELT', _request('MODIFY_TIME_e'));
	}

	if (_request('comm_type') == '1') {  //返佣查询
		$where['COMMENT'] = array('like', 'Commission maxib%'); // CRM返佣
	} else if (_request('comm_type') == '2') {
		$where['COMMENT'] = array('like', 'agent%'); // MT返佣
	}
	if ($_REQUEST['BALANCE'] == '1') {
		$where['COMMENT'] = array('like', 'Deposit%'); // 入金注释
		//$where['PROFIT'] = array('gt','0');
	} else if ($_REQUEST['BALANCE'] == '-1') {
		$where['COMMENT'] = array('like', 'Withdraw%'); // 出金注释
		// $where['PROFIT'] = array('lt','0');
	} else if ($_REQUEST['BALANCE'] == '2') {
		$where['_string'] = "COMMENT like 'Withdraw%' or COMMENT like 'Deposit%'"; // 出金注释
		// $where['PROFIT'] = array('lt','0');
	}

	return $where;
}

function getunderMT4Ids($member_id_arr) {
	global $DB;
	
	if(!is_array($member_id_arr)){
		$member_id_arr = array($member_id_arr);
	}
	if(!$member_id_arr){
		return array();
	}
	
	//账户数量
	$account = $DB->getDTable("select * from t_member_mtlogin where status = 1 and member_id in (" . implode(',',$member_id_arr) . ")");
	$account_id_arr = array();
	foreach ($account as $key => $value) {
		$account_id_arr[] = $value['loginid'];
	}
	return $account_id_arr;
}

function report_getid_arr() {
	global $DB;
	global $DRAdmin;
	
	$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");

	if (_request('LOGIN') && _request('searchuser') == 'all') {
		$loginid = _request('LOGIN');
		$memberlogin = $DB->getDRow("select member_id from t_member_mtlogin where loginid = '{$loginid}' and status = 1");
		$memberuser = $DB->getDRow("select * from t_member where id = '{$memberlogin['member_id']}'");
		if (!$memberlogin) {
			FCreateErrorPage(array(
				'title'=>L("提示"),
				'content'=>L("当前账号不存在"),
				'btnStr'=>L('返回'),
				'url'=>FPrevUrl(),
				'isSuccess'=>0,
				'autoRedirectTime'=>0,
			));
		}
		$_REQUEST['username'] = $memberuser['email'];
	}
	if ($DRAdmin['_dataRange'] <= 1) {
		$memberid = $DRAdmin['id'];
		$username = urldecode(_request('username'));
		
		//输入账号条件
		if ($username) {
			$member = $DB->getRow("select id from t_member where server_id = '{$DRAdmin['server_id']}' and status = 1 and (email = '" . $username . "' or nickname='" . $username . "' or chineseName='" . $username . "')");
			if (!$member) {
				FCreateErrorPage(array(
					'title'=>L("提示"),
					'content'=>L("信息不存在"),
					'btnStr'=>L('返回'),
					'url'=>FPrevUrl(),
					'isSuccess'=>0,
					'autoRedirectTime'=>0,
				));
			}
			$memberid = $member['id'];
			if ($DRAdmin['_dataRange'] >= 2) {
				$parent_id = 'admin';
			} else {
				$parent_id = $DRAdmin['id'];
			}
			_check_member_scope($parent_id, $member['id']);
		}

		//输入账号条件
		$member_id_arr = array();
		$member_id_arr = getunderCustomerIds($memberid);

		$member_id_arr = array_merge($member_id_arr, array($memberid));
		$account_id_arr = getunderMT4Ids($member_id_arr);
		if (_request('LOGIN')) {//输入了查询条件账户
			$isin = in_array(_request('LOGIN'), $account_id_arr);
			if (!$isin) {
				FCreateErrorPage(array(
					'title'=>L("提示"),
					'content'=>L("不是您的下级"),
					'btnStr'=>L('返回'),
					'url'=>FPrevUrl(),
					'isSuccess'=>0,
					'autoRedirectTime'=>0,
				));
			}
		}
	} else {
		$username = urldecode(_request('username'));
		//输入账号条件
		$memberid = 0;
		if ($username) {
			$member = $DB->getDRow("select id from t_member where server_id = '{$DRAdmin['server_id']}' and status = 1 and (email = '" . $username . "' or nickname='" . $username . "'  or chineseName='" . $username . "')");
			if (!$member) {
				FCreateErrorPage(array(
					'title'=>L("提示"),
					'content'=>L("信息不存在"),
					'btnStr'=>L('返回'),
					'url'=>FPrevUrl(),
					'isSuccess'=>0,
					'autoRedirectTime'=>0,
				));
			}
			$memberid = $member['id'];
		}
		
		$loginid = _request('loginid');
		if ($loginid) {
			$memberlogin = $DB->getDRow("select member_id from t_member_mtlogin where loginid = '{$loginid}' and status = 1");
			if (!$memberlogin) {
				FCreateErrorPage(array(
					'title'=>L("提示"),
					'content'=>L("当前账号不存在"),
					'btnStr'=>L('返回'),
					'url'=>FPrevUrl(),
					'isSuccess'=>0,
					'autoRedirectTime'=>0,
				));
			}
			$memberid = $memberlogin['member_id'];
		}

		$member_id_arr = getunderCustomerIds($memberid);
		if ($memberid) {
			$member_id_arr = array_merge($member_id_arr, array($memberid));
		} else {
			$member_id_arr = array_merge($member_id_arr, array($DRAdmin['id']));
		}
		array_unshift($member_id_arr, $memberid);
		$account_id_arr = getunderMT4Ids($member_id_arr);
	}

	//客户列表页面过来的数据 在这里做分析，主要是MT4 帐号需要做对比 取帐号的交集
	if ($_REQUEST['pmemids'] || $_REQUEST['time_start']) {
		$sever_id = $DRAdmin['server_id'];
		$pmemids = $_REQUEST['pmemids'];if(!is_array($pmemids)){$pmemids = array($pmemids);}
		//取我的下级和传过来账号的交易 避免查看别人的帐号
		$parent_member = $DB->getField2Arr("select id,id as ids from t_member where parent_id in (" . implode(',',$pmemids) . ") and server_id = '{$sever_id}' and `scope` <> 1 and status = 1");

		$member_id_arr = array_intersect($member_id_arr, $parent_member);
		$parent_account = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$member_id_arr) . ") and mtserver = '{$sever_id}' and status = 1");

		if ($parent_account) {
			$account_id_arr = $parent_account;
		} else {
			$account_id_arr = array(-1);
		}
	}

	/* 看下人员分类 */
	$isnext = chk_in_access('/datas/looknexttrades');
	if ($memberid && $_REQUEST['datascope']) {
		//输入loginid的会员
		$userid = $memberid;
		if (_request('LOGIN') && _request('username') && $DRAdmin['_dataRange'] <= 1 && !$memberid) {
			$userid = $DRAdmin['id'];
		}
	}

	if ($_REQUEST['datascope'] == 'my' && $userid) {
		$member_id_arrs = array($userid);
	} else if ($_REQUEST['datascope'] == 'next' && $isnext && $userid) {
		$member_id_arrs = $DB->getField2Arr("select id,id as ids from t_member where parent_id = '" . $userid . "' and status = 1");
	} else if ($_REQUEST['datascope'] == 'nextall' && $isnext && $userid) {
		$member_id_arrs = getunderCustomerIds($userid);
	} else if ($_REQUEST['datascope'] == 'all' && $userid) {
		$member_id_arrs = getunderCustomerIds($userid);
		$member_id_arrs = array_merge(array($userid), $member_id_arrs);
	}
	if ($_REQUEST['datascope'] && !$member_id_arrs) {
		$where['TICKET'] = array('lt', 0);
	}

	if ($member_id_arrs) {
		$account_id_arr = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id in (" . implode(',',$member_id_arrs) . ") and status = 1");
	}
	/* end查看人员分类型 */

	if ($_GET['GROUP_NAME']) {
		$group = $_GET['GROUP_NAME'];
		if(!is_array($group)){
			$group = array($group);
		}
		//dump($group);
		if (in_array('all_group',$group)) {
			$arr_group = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'",true);
			if(!$arr_group){
				$arr_group = array('0');
			}
			$userswhere = "where `GROUP` in ('" . implode("','",$group) . "')";
		} else {
			$userswhere = "where `GROUP` in ('" . implode("','",$group) . "')";
		}
		$users_login_arr = $DB->getField("select LOGIN from " . $DRAdmin['mt4dbname'] . ".mt4_users {$userswhere}",true);
		if (_request('reject')) {
			$account_id_arr = array_diff($account_id_arr, $users_login_arr); //剔除组查询取差集
		} else {
			$account_id_arr = array_intersect($account_id_arr, $users_login_arr); //所属组查询取交集
		}
	}

	if (_request('T_LOGIN')) {
		$t_login = explode(',', _request('T_LOGIN'));
		$account_id_arr = array_diff($account_id_arr, $t_login); //剔除MT账号
	}

	return $account_id_arr;
}

/**
* 总余额
*/
function sumBalance($loginidarr,$where = array()){
	global $DB;
	global $DRAdmin;
	
	if(!$loginidarr){
		return 0;
	}
	if(!is_array($loginidarr)){
		$loginidarr = array($loginidarr);
	}
	
	if ($DRAdmin['ver'] == 5) {
		$maplogin['Login'] = array('in',$loginidarr);
		if($where)
			$maplogin = array_merge($maplogin, $where);
			
		$maploginStr = cz_where_to_str($maplogin);	
		
		$totalDatas = floatval($DB->getField("select sum(Balance) as BALANCE from " . $DRAdmin['mt4dbname'] . ".`mt5_users` {$maploginStr}"));
	}else{
		$maplogin['ENABLE']=1;
		$maplogin['LOGIN'] = array('in',$loginidarr);
		if($where)
			$maplogin = array_merge($maplogin, $where);
			
		$maploginStr = cz_where_to_str($maplogin);	
		
		$totalDatas = floatval($DB->getField("select sum(BALANCE) as BALANCE from " . $DRAdmin['mt4dbname'] . ".`mt4_users` {$maploginStr}"));
	}
	return $totalDatas;
}

/**
* 统计总净值
*/
function sumEquity($loginidarr,$where){
	global $DB;
	global $DRAdmin;
	
	if(!$loginidarr){
		return 0;
	}
	if(!is_array($loginidarr)){
		$loginidarr = array($loginidarr);
	}
	
	if ($DRAdmin['ver'] == 5) {
		//$maplogin['ENABLE']=1;
		$maplogin['Login'] = array('in',$loginidarr);
		if($where)
			$maplogin = array_merge($maplogin, $where);
		
		$maploginStr = cz_where_to_str($maplogin);	
		
		$totalDatas = floatval($DB->getField("select sum(Equity) as EQUITY from " . $DRAdmin['mt4dbname'] . ".`mt5_accounts` {$maploginStr}"));
	}else{
		$maplogin['ENABLE']=1;
		$maplogin['LOGIN'] = array('in',$loginidarr);
		if($where)
			$maplogin = array_merge($maplogin, $where);
		
		$maploginStr = cz_where_to_str($maplogin);	
		
		$totalDatas = floatval($DB->getField("select sum(EQUITY) as EQUITY from " . $DRAdmin['mt4dbname'] . ".`mt4_users` {$maploginStr}"));
	}
	return $totalDatas;
}

function totaltradedata($statictradelist) {
	$sumdata['totalmember'] = 0;
	$sumdata['totalvolumes'] = 0;
	$sumdata['totalInBalance'] = 0;
	$sumdata['totalOutBalance'] = 0;
	$sumdata['equityBalance'] = 0;
	$sumdata['totalProfit'] = 0;
	$sumdata['totalCount'] = 0;
	$sumdata['totalInbalanceCount'] = 0;
	$sumdata['totalOutbalanceCount'] = 0;
	$sumdata['totalCommbanalce'] = 0;
	$sumdata['spreadProfitCount'] = 0;
	$sumdata['dayweek'] = L('汇总');
	foreach ($statictradelist as $data) {
		$sumdata['totalmember'] = $sumdata['totalmember'] + $data['totalmember'];
		$sumdata['totalvolumes'] = $sumdata['totalvolumes'] + $data['totalvolumes'];
		$sumdata['totalInBalance'] = $sumdata['totalInBalance'] + $data['totalInBalance'];
		$sumdata['totalOutBalance'] = $sumdata['totalOutBalance'] + $data['totalOutBalance'];
		$sumdata['equityBalance'] = $sumdata['equityBalance'] + $data['equityBalance'];
		$sumdata['totalProfit'] = $sumdata['totalProfit'] + $data['totalProfit'];
		$sumdata['totalCount'] = $sumdata['totalCount'] + $data['totalCount'];
		$sumdata['totalInbalanceCount'] = $sumdata['totalInbalanceCount'] + $data['totalInbalanceCount'];
		$sumdata['totalOutbalanceCount'] = $sumdata['totalOutbalanceCount'] + $data['totalOutbalanceCount'];
		$sumdata['commission_banalce'] = $sumdata['commission_banalce'] + $data['commission_banalce'];
		$sumdata['spreadProfitCount'] = $sumdata['spreadProfitCount'] + $data['spreadProfitCount'];
	}
	return $sumdata;
}
	

function getTotalData($loginidarr, $memberids, $starttime, $endtime) {
	global $DB;
	global $DRAdmin;

	$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
	
	$map['balance'] = round(sumBalance($loginidarr, $timewher), 2);
	
	//file_put_contents();
	$map['equity'] = round(sumEquity($loginidarr, $timewher), 2);
	if ($mtserver['ver'] == 5) {
		$mt5_positionsModel = new Mt5_positionsModel($DRAdmin['mt4dbname']);
		
		$msg['TimeCreate'] = array(array('EGT', strtotime($starttime)), array('ELT', strtotime($endtime)));
		$map['unclosecount'] = $mt5_positionsModel->sumcloseCount($loginidarr, $msg);
		$map['unclosevolume'] = round($mt5_positionsModel->sumcloseVolume($loginidarr, $msg), 2);
		$map['uncloseamount'] = round($mt5_positionsModel->sumcloseAmount($loginidarr, $msg), 2);
	} else {
		$reportModel = new ReportModel($DRAdmin['mt4dbname']);
		
		$where['CLOSE_TIME'] = array('eq', '1970-01-01 00:00:00');
		$where['OPEN_TIME'] = array('between', array($starttime, $endtime));
		$map['unclosecount'] = $reportModel->sumUncloseCount($loginidarr, $where);
		$map['unclosevolume'] = round($reportModel->sumUncloseVolume($loginidarr, $where), 2);
		$map['uncloseamount'] = round($reportModel->sumUncloseAmount($loginidarr, $where), 2);
	}

	$map['mt4num'] = count($loginidarr);
	foreach ($memberids as $key => $value) {
		if ($value == 0)
			unset($memberids[$key]);
	}
	$map['membernum'] = count($memberids) - 1; //减掉自己
	return $map;
}


function getStaticByDay($day, $account_id_arr, $member_id_arr, $symbols, $mtserver, $start, $end) {
	global $DB;
	global $DRAdmin;
	
	unset($memberwhere);
	$memberwhere['status'] = array("in", "0,1");
	$memberwhere['admin'] = array('neq', '1');
	$memberwhere['server_id'] = $DRAdmin['server_id'];;
	//客户数
	if (empty($member_id_arr))
		$memberwhere['parent_id'] = array('in', '-1');
	else
		$memberwhere['parent_id'] = array('in', $member_id_arr);

	if ($start && $end) {
		$memberwhere['create_time'] = array(array('egt', strtotime($day . ' ' . $end)), array('elt', strtotime($day . ' ' . $start)));
		$where['CLOSE_TIME'] = array(array('egt', $day . ' ' . $end), array('elt', $day . ' ' . $start));
		$starttime = strtotime($day . ' ' . $end) + 3600 * floatval(8 - $DRAdmin['timezone']);
		$endtime = strtotime($day . ' ' . $start) + 3600 * floatval(8 - $DRAdmin['timezone']);
		$startstr = ' ' . $start;
		$endstr = ' ' . $end;
	} else if ($start && !$end) {
		$memberwhere['create_time'] = array(array('egt', strtotime($day . ' 00:00:00')), array('elt', strtotime($day . $start)));
		$where['CLOSE_TIME'] = array(array('egt', $day . ' 00:00:00'), array('elt', $day . ' ' . $start));
		$starttime = strtotime($day . ' 00:00:00') + 3600 * floatval(8 - $DRAdmin['timezone']);
		$endtime = strtotime($day . ' ' . $start) + 3600 * floatval(8 - $DRAdmin['timezone']);
		$startstr = ' 00:00:00';
		$endstr = ' ' . $start;
	} else if (!$start && $end) {
		$memberwhere['create_time'] = array(array('egt', strtotime($day . $end)), array('elt', strtotime($day . ' 23:59:59')));
		$where['CLOSE_TIME'] = array(array('egt', $day . ' ' . $end), array('elt', $day . ' 23:59:59'));
		$starttime = strtotime($day . ' ' . $end) + 3600 * floatval(8 - $DRAdmin['timezone']);
		$endtime = strtotime($day . ' 23:59:59') + 3600 * floatval(8 - $DRAdmin['timezone']);
		$startstr = ' ' . $end;
		$endstr = ' 23:59:59 ';
	} else {
		$memberwhere['create_time'] = array(array('egt', strtotime($day . ' 00:00:00')), array('elt', strtotime($day . ' 23:59:59')));
		$where['CLOSE_TIME'] = array(array('egt', $day . ' 00:00:00'), array('elt', $day . ' 23:59:59'));
		//返佣总额 t_sale_commission_banalce  TYPE=0 求和Amount.
		$starttime = strtotime($day . ' 00:00:00') + 3600 * floatval(8 - $DRAdmin['timezone']);
		$endtime = strtotime($day . ' 23:59:59') + 3600 * floatval(8 - $DRAdmin['timezone']);
	}

	$memberwhereStr = cz_where_to_str($memberwhere);
	$customer_num = $DB->getDTable("select id from t_member {$memberwhereStr}");
	
	//连接数据库
	$reportModel = new ReportModel($DRAdmin['mt4dbname']);

	if (!empty($symbols)) {
		$where['SYMBOL'] = array('in', $symbols);
	}
	/* if(!empty($group)){
	  $where['users.GROUP'] = $group;
	  } */
	$total = 1;
	if (count($account_id_arr) > 0) {
		//$is_have_Cache = $DB->getDRow("select * from t_mt_member_trades where server_id = '{$DRAdmin['server_id']}' and loginid in (" . implode(',',$mtlist) . ")");
		/*if ($is_have_Cache && empty($symbols)) {
			$where['loginid'] = ['in',$account_id_arr];
			$where['server_id'] = $DRAdmin['server_id'];;
			//缓存
			$nowwhere = $reportModel->getTimeNowWhere();
			unset($where['CLOSE_TIME']);
			$where['date']= [['egt', $starttime], ['elt', $endtime]];
			$dataCache = D('MtMemberTrades')->where($where)->field('sum(total_volumes) total_volumes,sum(total_count) total_count,sum(innumber) innumber,sum(incount) incount,sum(outnumber) outnumber, sum(outcount) outcount,sum(totalprofit) totalprofit')->find();
			$volumeCache = $dataCache['total_volumes'];
			$countCache = $dataCache['total_count'];
			$depoistCache = $dataCache['innumber'];
			$depoistCountCache = $dataCache['incount'];
			$withdrawCache = $dataCache['outnumber'];
			$withdrawCountCache = $dataCache['outcount'];
			$profitCache = $dataCache['totalprofit'];
			//实时
			if ($nowwhere != false) {
				$volumeNow = $reportModel->sumVolume($account_id_arr,$nowwhere);
				$countNow = $reportModel->sumCount($account_id_arr,$nowwhere);
				$depoistNow = $reportModel->sumInBalance($account_id_arr,$nowwhere);
				$depoistCountNow = $reportModel->sumInBalanceCount($account_id_arr,$nowwhere);
				$withdrawNow = $reportModel->sumOutBalance($account_id_arr,$nowwhere);
				$withdrawCOuntNow = $reportModel->sumOutBalanceCount($account_id_arr,$nowwhere);
				$profitNow = $reportModel->sumProfit($account_id_arr,$nowwhere);
			}
			//总交易数
			$totalvolumes =  round(($volumeCache + ($volumeNow ? $volumeNow : 0)),2);
			//总入金量
			$totalInBalance = round(($depoistCache + ($depoistNow ? $depoistNow : 0)), 2);
			//总出金量
			$totalOutBalance = round(($withdrawCache + ($withdrawNow ? $withdrawNow : 0)), 2);
			//总盈利
			$totalProfit = round(($profitCache + ($profitNow ? $profitNow : 0)), 2);
			//总笔数
			$totalCount = $countCache + ($countNow ? $countNow : 0);
			//入金总笔数
			$totalInbalanceCount = $depoistCache + ($depoistNow ? $depoistNow : 0);
			//出金总笔数
			$totalOutbalanceCount = $withdrawCountCache + ($withdrawCOuntNow ? $withdrawCOuntNow : 0);

			$commission_banalce = D('sale_commission_balance')
					->where(array('MEMBER_ID' => array('in', $member_id_arr), 'TYPE' => '0', 'CREATE_TIME' => array(array('egt', $starttime), array('elt', $endtime))))
					->sum('Amount');
			if ($mtserver['ver'] == 5) {
				//点差统计
				$spreadProfitCount = $reportModel->sumSpread($account_id_arr, $where);
			}
		}else{*/
			if ($mtserver['ver'] == 5) {
				require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_buyModel.class.php');
				
				$mt5_buyModel = new Mt5_buyModel($DRAdmin['mt4dbname']);
				
				//总交易股数
				$totalvolumes = $mt5_buyModel->sumVolume($account_id_arr, $where);
				//总入金量
				$totalInBalance = round($mt5_buyModel->sumInBalance($account_id_arr, $where), 2);
				//总出金量
				$totalOutBalance = round($mt5_buyModel->sumOutBalance($account_id_arr, $where), 2);
				//总盈利
				$totalProfit = round($mt5_buyModel->sumProfit($account_id_arr, $where,$DRAdmin['mt4dbname']), 2);

				//总笔数
				$totalCount = $mt5_buyModel->sumCount($account_id_arr, $where);
				//入金总笔数
				$totalInbalanceCount = $mt5_buyModel->sumInBalanceCount($account_id_arr, $where);
				//出金总笔数
				$totalOutbalanceCount = $mt5_buyModel->sumOutBalanceCount($account_id_arr, $where);
				$commission_banalce = floatval($DB->getField("select sum(Amount) as sum1 from t_sale_commission_balance where MEMBER_ID in (" . implode(',',$member_id_arr) . ") and `TYPE` = 0 and CREATE_TIME >= '{$starttime}' and CREATE_TIME <= '{$endtime}'"));
			} else {
				
				//总交易股数
				$totalvolumes = $reportModel->sumVolume($account_id_arr, $where);
				//总入金量
				$totalInBalance = round($reportModel->sumInBalance($account_id_arr, $where), 2);
				//总出金量
				$totalOutBalance = round($reportModel->sumOutBalance($account_id_arr, $where), 2);
				//总盈利
				$totalProfit = round($reportModel->sumProfit($account_id_arr, $where), 2);
				//总笔数
				$totalCount = $reportModel->sumCount($account_id_arr, $where);
				//入金总笔数
				$totalInbalanceCount = $reportModel->sumInBalanceCount($account_id_arr, $where);
				//出金总笔数
				$totalOutbalanceCount = $reportModel->sumOutBalanceCount($account_id_arr, $where);

				$commission_banalce = $DB->getField("select sum(Amount) as sum1 from t_sale_commission_balance where MEMBER_ID in (" . implode(',',$member_id_arr) . ") and `TYPE` = 0 and CREATE_TIME >= '{$starttime}' and CREATE_TIME <= '{$endtime}'");

				//点差统计
				$spreadProfitCount = $reportModel->sumSpread($account_id_arr, $where);
			}
		//}
	}
	$totalvolumes = floatval($totalvolumes);
	$totalProfit = floatval($totalProfit);
	$totalInBalance = floatval($totalInBalance);
	$totalOutBalance = floatval($totalOutBalance);
	$totalOutbalanceCount = floatval($totalOutbalanceCount);
	$totalInbalanceCount = floatval($totalInbalanceCount);
	$commission_banalce = floatval($commission_banalce);
	$totalCount = floatval($totalCount);
	$week = array(L('日'), '一', '二', '三', '四', '五', '六');
	$dayweek = $week[date('w', strtotime($day))];

	//$mapstatic = array('dayweek' => $day . L('星期' . $dayweek),
	$mapstatic = array('dayweek' => $day,
		'day' => $day,
		'starthis' => $startstr,
		'endhis' => $endstr,
		'totalmember' => count($customer_num),
		'totalvolumes' => $totalvolumes,
		'totalInBalance' => $totalInBalance,
		'totalOutBalance' => $totalOutBalance,
		'equityBalance' => floatval($totalInBalance) + floatval($totalOutBalance),
		'totalProfit' => $totalProfit,
		'totalCount' => $totalCount,
		'totalInbalanceCount' => $totalInbalanceCount,
		'totalOutbalanceCount' => $totalOutbalanceCount,
		'commission_banalce' => $commission_banalce,
		'spreadProfitCount' => $spreadProfitCount);
	
	return $mapstatic;
}

/**
 * 统计时间段内汇总交易数据
 */
function custonmer_indexstatic_totaltradedata($statictradelist) {
	$sumdata['totalmembers'] = 0;
	$sumdata['totalmts'] = 0;
	$sumdata['totalvolumes'] = 0;
	$sumdata['totalcounts'] = 0;
	$sumdata['totalInBalance'] = 0;
	$sumdata['totalInBalanceCount'] = 0;
	$sumdata['totalOutBalance'] = 0;
	$sumdata['totalOutBalanceCount'] = 0;
	$sumdata['totalProfit'] = 0;
	$sumdata['totalCommbanalce'] = 0;
	$sumdata['totalUncloseamount'] = 0;
	$sumdata['totalUnclosecount'] = 0;
	$sumdata['totalUnclosevolume'] = 0;
	$sumdata['spreadProfitCount'] = 0;
	$step = 0;
	foreach ($statictradelist as $data) {
		$step++;
		if($step==1) continue;
		$sumdata['totalmembers'] = $sumdata['totalmembers'] + $data['num'];
		$sumdata['totalmts'] = $sumdata['totalmts'] + $data['mt4num'];
		$sumdata['totalvolumes'] = $sumdata['totalvolumes'] + $data['totalvolume'];
		$sumdata['totalcounts'] = $sumdata['totalcounts'] + $data['totalcount'];
		$sumdata['totalInBalance'] = $sumdata['totalInBalance'] + $data['depoistamount'];
		$sumdata['totalOutBalance'] = $sumdata['totalOutBalance'] + $data['withdrawamount'];
		$sumdata['totalProfit'] = $sumdata['totalProfit'] + $data['profitamount'];
		$sumdata['totalInbalanceCount'] = $sumdata['totalInbalanceCount'] + $data['depoistcount'];
		$sumdata['totalOutbalanceCount'] = $sumdata['totalOutbalanceCount'] + $data['withdrawcount'];
		$sumdata['totalCommbanalce'] = $sumdata['totalCommbanalce'] + $data['commissionbanalce'];
		$sumdata['totalUncloseamount'] = $sumdata['totalUncloseamount'] + $data['uncloseamount'];
		$sumdata['totalUnclosecount'] = $sumdata['totalUnclosecount'] + $data['unclosecount'];
		$sumdata['totalUnclosevolume'] = $sumdata['totalUnclosevolume'] + $data['unclosevolume'];
		$sumdata['spreadProfitCount'] = $sumdata['spreadProfitCount'] + $data['spreadProfitCount'];
	}
	return $sumdata;
}
		
		
//查询下线用户数据
function custonmer_indexstatic_staticunder($memberid,$start,$end,$level,$symbols,$self,$group){
	global $DB;
	global $DRAdmin;
	global $calclevel;
	global $list;
	
	$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");

	if($self){
		if($memberid=='admin')
		   $map['id']= $DRAdmin['id'];
		else 
			$map['id']=$memberid;
	}else{
		if($memberid=='admin')
			$map['parent_id']=array('in','0');//管理员查询所有
		else
		  $map['parent_id']=array('in',$memberid);
	}

	$map['status']='1';
	$map['server_id']= $DRAdmin['server_id'];
	$map['admin'] = array('neq', 1);

	$mapStr = cz_where_to_str($map);
	
	//print_r($mapStr);

	$childmemberList = $DB->getDTable("select * from t_member {$mapStr}");

	$reportModel = new ReportModel($mtserver['db_name'],$mtserver['ver']);
	
	if($mtserver['ver']==5){
		$mt5_positionsModel = new Mt5_positionsModel($DRAdmin['mt4dbname']);
		$mt5_buyModel = new Mt5_buyModel($DRAdmin['mt4dbname']);
	}
	
	if(empty($childmemberList)&&!$self){
		return;
	} 
	$instr = "";
	$level++;
	if($level>$calclevel){
		return;
	}
	if(!$self){			 
		for($i=0;$i<count($childmemberList);$i++){
			if($i==count($childmemberList)-1)
				$instr=$instr.$childmemberList[$i]['id'];
			else
				$instr=$instr.$childmemberList[$i]['id'].',';
		}
	}else{
		if($memberid=='admin')
		   $instr = '0';
		else
			$instr=$memberid;
	}
			 
	$mtlist = $DB->getField2Arr("select id,loginid from t_member_mtlogin where mtserver = '{$DRAdmin['server_id']}' and status = 1 and member_id in (" . ($instr ? $instr : '0') . ")");
	
	if($group){
		if($group == 'all_group'){
			$arr_group = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'",true);
			$userswhere['GROUP'] = array('in',$arr_group);
		}else{
			$userswhere['GROUP'] = array('in',$group);
		}
		$users_login_arr = $DB->getField("select `LOGIN` from " . $DRAdmin['mt4dbname'] . ".mt4_users " . cz_where_to_str($userswhere),true);
		if(_request('reject')){
			$mtlist = array_diff($mtlist,$users_login_arr);   //剔除组查询取差集
		}else{
			$mtlist = array_intersect($mtlist,$users_login_arr); //所属组查询取交集
		}      
	}

	if(_request('T_LOGIN')){
		$t_login = explode(',', _request('T_LOGIN'));
		$mtlist = array_diff($mtlist, $t_login); //剔除MT账号
	}
	
	unset($where);
	$where['CLOSE_TIME']= array("between",array($start.' 00:00:00',$end.' 23:59:59'));

	$msg['TimeCreate'] = array(array('EGT', strtotime($start)), array('ELT', strtotime($end. ' 23:59:59')));
	$total = 0;
	if(count($mtlist)>0){
		if(!empty($symbols)){ 
			$where['SYMBOL'] = $msg['Symbol'] = array('in',$symbols);
		}
		$totalvolume = $reportModel->sumVolume($mtlist,$where);
		$totalcount = $reportModel->sumCount($mtlist,$where);
		$depoistamount = $reportModel->sumInBalance($mtlist,$where);
		$depoistcount = $reportModel->sumInBalanceCount($mtlist,$where);
		$withdrawamount = $reportModel->sumOutBalance($mtlist,$where);
		$withdrawcount = $reportModel->sumOutBalanceCount($mtlist,$where);
		$profitamount = $reportModel->sumProfit($mtlist,$where);
		if ($mtserver['ver'] == 5) {
			
			$profitamount = $mt5_buyModel->sumProfit($mtlist,$where);
		}else{
			
			$profitamount = $reportModel->sumProfit($mtlist,$where);
		}


//          $newwhere['loginid'] = ['in',$mtlist];
//          $newwhere['server_id'] = $DRAdmin['server_id'];
//          $is_have_Cache = D('MtMemberTrades')->where($newwhere)->find();
//          if ($is_have_Cache && empty($symbols)) {
//              $cache_data = $reportModel->getCacheData($mtlist,$start,$end,1);
//              $totalvolume = $cache_data['totalvolume'];
		// 	$totalcount = $cache_data['totalcount'];
		// 	$depoistamount = $cache_data['depoistamount'];
		// 	$depoistcount = $cache_data['depoistcount'];
		// 	$withdrawamount = $cache_data['withdrawamount'];
		// 	$withdrawcount = $cache_data['withdrawcount'];
		// 	$profitamount = $cache_data['profitamount'];

//          }else{

		// 	$totalvolume = $reportModel->sumVolume($mtlist,$where);
		// 	$totalvolume = $totalvolume / 100;
		// 	$totalcount = $reportModel->sumCount($mtlist,$where);
		// 	$depoistamount = $reportModel->sumInBalance($mtlist,$where);
		// 	$depoistcount = $reportModel->sumInBalanceCount($mtlist,$where);
		// 	$withdrawamount = $reportModel->sumOutBalance($mtlist,$where);
		// 	$withdrawcount = $reportModel->sumOutBalanceCount($mtlist,$where);
		// 	$profitamount = $reportModel->sumProfit($mtlist,$where);
		// }
		//点差统计
		$spreadProfitCount = $reportModel->sumSpread($mtlist, $where);
		if($mtserver['ver']==5){
			$uncloseamount = $mt5_positionsModel->sumcloseAmount($mtlist,$msg);
			$unclosecount = $mt5_positionsModel->sumcloseCount($mtlist,$msg);
			$unclosevolume = $mt5_positionsModel->sumcloseVolume($mtlist,$msg);
		}else{
			$where['CLOSE_TIME']='1970-01-01 00:00:00';
			// $where['OPEN_TIME']=array("between",array($start.' 00:00:00',$end.' 23:59:59'));
			$uncloseamount = $reportModel->sumUncloseAmount($mtlist,$where);
			$unclosecount = $reportModel->sumUncloseCount($mtlist,$where);
			$unclosevolume = $reportModel->sumUncloseVolume($mtlist,$where);
		}

		//返佣总额 t_sale_commission_banalce  TYPE=0 求和Amount.
		$starttime = strtotime($start . ' 00:00:00') + 3600 * floatval(8 - $DRAdmin['timezone']);
		$endtime = strtotime($end . ' 23:59:59') + 3600 * floatval(8 - $DRAdmin['timezone']);
		$commission_banalce = floatval($DB->getField("select sum(Amount) as sum1 from t_sale_commission_balance " . cz_where_to_str(array('MEMBER_ID' => array('in', $memberid), 'TYPE' => '0', 'CREATE_TIME' => array(array('egt', $starttime), array('elt', $endtime))))));
	}
	
	$arr = array(
		"totalvolume"=>$totalvolume,
		"totalcount"=>$totalcount,
		"depoistamount"=>$depoistamount,
		"depoistcount"=>$depoistcount,
		"withdrawamount"=>$withdrawamount,
		"withdrawcount"=>$withdrawcount,
		"profitamount"=>$profitamount,
		"uncloseamount"=>$uncloseamount,
		"unclosecount"=>$unclosecount,
		"unclosevolume"=>$unclosevolume,
		"commissionbanalce"=>$commission_banalce,
		"spreadProfitCount" => $spreadProfitCount,
		"num"=>count($childmemberList),
		"parent_ids"=>$memberid,
		'mt4num'=>count($mtlist));
	if($self){
		$arr['nickname'] = $childmemberList[0]['nickname'];
		$arr['email'] = $childmemberList[0]['email'];
	}
	$list[$level-1] = $arr;
	custonmer_indexstatic_staticunder($instr,$start,$end,$level,$symbols,false,$group);
}


/**
 * 时区转换
 * @param unknown $time
 * @param unknown $zone
 */
function time2mt4zone($time, $zone, $timeformat) {
    $time = $time - 3600 * floatval(8 - $zone);
    return date($timeformat, $time);
}


//数据转成树型结构
function toTree($data, $pid = 0, $id = 0) {
	static $result = array();
	static $level=0;
	empty($id) && $level=0;
	empty($id) && $result = array();
	foreach ($data as $key => $value) {
		if ($value['pid'] == $pid) {
			$value['level']=$level;
			$result[]=$value;
			$level++;
			unset($data[$key]);
			toTree($data, $value['id'], $value['id']);
		}
	}

	$level--;
	return $result;
}

function array_unset($arr, $key) {
    //$arr->传入数组   $key->判断的key值
    //建立一个目标数组
    $res = array();
    foreach ($arr as $value) {
        //查看有没有重复项
        if (isset($res[$value[$key]])) {
            //有：销毁
            unset($value[$key]);
        } else {
            $res[$value[$key]] = $value;
        }
    }
    return $res;
}

/**
 * 将MT帐号转移到指定的manager
 * @param type $loginid MT帐号
 * @param type $server_id 现在manager
 * @param type $toserver_id 目标manager
 */
function forwordMt($member_id, $loginid, $server_id, $toserver_id) {
	global $DB;
	
	$member = $DB->getDRow("select * from t_member " . cz_where_to_str(array('id' => $member_id, 'server_id' => $server_id, 'status' => 1)));
	if (!$member) {
		return array('status'=>'fail','msg'=>'会员不存在');
	}
	$frominfo=$DB->getDRow("select * from t_mt4_server where id = '{$server_id}'");
	$toinfo=$DB->getDRow("select * from t_mt4_server where id = '{$toserver_id}'");
	if($frominfo['ver']!=$toinfo['ver']){
		return array('status'=>'fail','msg'=>'MT版本不一致');
	}
	$mtinfo = $DB->getField2Arr("select id,loginid from t_member_mtlogin where loginid = '{$loginid}' and mtserver = '{$server_id}'");
	//是否有跟单配置
	$isfollow = intval($DB->getField("select count(*) as count1 from mt4svr_config " . cz_where_to_str(array('LOGINID' => array('in', $mtinfo),'STATUS'=>0, 'SERVER_ID' => $server_id))));
	if ($isfollow) {
		return array('status'=>'fail','msg'=>'该账号还有跟单配置，请先取消');
	}
	//是否有对冲配置
	$isearent = intval($DB->getField("select count(*) as count1 from t_earnest_user " . cz_where_to_str(array('Login' => array('in', $mtinfo),'Status'=>array('neq',-2), 'ServerId' => $server_id))));
	if ($isearent) {
		return array('status'=>'fail','msg'=>'该账号还有对冲配置，请先取消');
	}

	//查询是否存在同样的邮箱号。
	$isnew = $DB->getDRow("select * from t_member " . cz_where_to_str(array('email' => $member['email'], 'status' => array('in', '0,1'), 'server_id' => $toserver_id)));
	$count = intval($DB->getField("select count(*) as count1 from t_member_mtlogin " . cz_where_to_str(array('member_id' => $mtinfo['member_id'], 'mtserver' => $server_id))));
	//全部移过去并且不存在同样的邮箱
	if ($member_id > 0 && !$loginid && !$isnew && $count<2) {
		//转移会员
		$savedata = array('server_id' => $toserver_id, 'parent_id' => 0);
		$DB->update("t_member",$savedata,"where id = '{$member_id}'");
		
		//转移会员绑定
		$savedata = array('mtserver' => $toserver_id,'mt_type'=>1);
		$DB->update("t_member_mtlogin",$savedata,"where member_id = '{$member_id}' and status = 1");
		
		$DB->query("update t_inmoney set server_id = '{$toserver_id}' where member_id = '{$member_id}'");
		$DB->query("update t_outmoney set server_id = '{$toserver_id}' where member_id = '{$member_id}'");
		$DB->query("update t_mt4_apply set server_id = '{$toserver_id}' where member_id = '{$member_id}'");
		//移动上下级
		$parent_id = 0; //默认释放掉
		if ($member['parent_id'] > 0) {
			$parent_id = $member['parent_id'];
		}
		$nextuser = $DB->getDTable("select * from t_member " . cz_where_to_str(array("parent_id" => array("eq", $member['id']), 'status' => array('in', '0,1'))));
		foreach ($nextuser as $key => $value) {
			$DB->query("update t_member set parent_id = '{$parent_id}' " . cz_where_to_str(array("id" => array("eq", $value['id']), 'status' => array('in', '0,1'))));
		}
	} else if ($member_id > 0 && !$loginid && $isnew  && $count<2) {
		//转移会员
		$DB->query("update t_member set status = -1 where id = '{$member_id}'");//注销掉以前的会员
		//转移会员绑定
		$savedata = array('mtserver' => $toserver_id, 'member_id' => $isnew['id'],'mt_type'=>1);
		$DB->update("t_member_mtlogin",$savedata,"where member_id = '{$member_id}' and status = 1");
		
		$DB->query("update t_inmoney set server_id = '{$toserver_id}',member_id = '{$isnew['id']}' where member_id = '{$member_id}'");
		$DB->query("update t_outmoney set server_id = '{$toserver_id}',member_id = '{$isnew['id']}' where member_id = '{$member_id}'");
		$DB->query("update t_mt4_apply set server_id = '{$toserver_id}',member_id = '{$isnew['id']}' where member_id = '{$member_id}'");

		//移动上下级
		$parent_id = 0; //默认释放掉
		if ($member['parent_id'] > 0) {
			$parent_id = $member['parent_id'];
		}
		$nextuser = $DB->getDTable("select * from t_member " . cz_where_to_str(array("parent_id" => array("eq", $member['id']), 'status' => array('in', '0,1'))));
		foreach ($nextuser as $key => $value) {
			$DB->query("update t_member set parent_id = '{$parent_id}' " . cz_where_to_str(array("id" => array("eq", $value['id']), 'status' => array('in', '0,1'))));
		}
	} else {
		//组装新数据
		$newmember = $member;
		unset($newmember['id']);
		$newmember['password']=rand(110000,999999);
		$newmember['parent_id']=0;
		$newmember['password']=$member['password'];
		$newmember['server_id'] = $toserver_id;
		$newmember['status'] = 1;
		$newmember['amount'] = 0;
		if ($isnew) {
			$id = $isnew['id'];
		} else {
			//复制一条新的会员
			$id = $DB->insert("t_member",$newmember);
		}
		//转移会员绑定
		$DB->query("update t_member_mtlogin set mtserver = '{$toserver_id}',member_id = '{$id}' " . cz_where_to_str(array('member_id' => $member_id, 'status' => 1, 'mtserver' => $server_id, 'loginid' => array('in', $loginid))));
		$DB->query("update t_inmoney set server_id = '{$toserver_id}',member_id = '{$id}' " . cz_where_to_str(array('member_id' => $member_id, 'server_id' => $server_id, 'mtid' => array('in', $loginid))));
		$DB->query("update t_outmoney set server_id = '{$toserver_id}',member_id = '{$id}' " . cz_where_to_str(array('member_id' => $member_id, 'server_id' => $server_id, 'mtid' => array('in', $loginid))));
		$DB->query("update t_mt4_apply set server_id = '{$toserver_id}',member_id = '{$id}' " . cz_where_to_str(array('member_id' => $member_id, 'server_id' => $server_id, 'login' => array('in', $loginid))));
	}
	
	return array('status'=>'success','msg'=>'成功');
}

/**
 * $orderid 商户订单号
 * $amount 交易额
 * $systime 系统时间戳
 * $transtat:1 成功，-1：失败
 * $message :支付结果描述
 * $serialno：三方支付订单号
 * $bankorderid：银行订单号
 * $bankcode：支付渠道
 * $code：支付返回代码
 * $PayCode：支付通道代码
 */
function pay_updateOrder($orderid, $amount, $systime, $transtat, $message, $serialno, $bankorderid, $bankcode, $code, $PayCode) {
	global $DB;
	
	$inmoney = $DB->getDRow("select * from t_inmoney where `payno` = '{$orderid}'");
	if ($inmoney) {
		if ($inmoney['paystatus'] == '1') {//已经支付
			return;
		}

		if ($transtat == '-1') {//交易失败
			loginfo('pay_in_s2s_' . $PayCode,'交易失败！错误代码：' . $code . ' 错误信息：' . $message);
			
			$indata['paytime'] = $systime;
			$indata['bankcode'] = $bankcode;
			$indata['paycode'] = $code;
			$indata['paystatus'] = '-1'; //交易失败
			$indata['content'] = $message . '';
			$indata['bankorderid'] = $bankorderid . '';
			$indata['serialno'] = $serialno . '';
			$ret = $DB->update("t_inmoney",$indata,"where `payno` = '{$orderid}' and `paystatus` = ''");
			return;
		} else {
			if ($amount > 0 && $inmoney['price'] != $amount) {
				loginfo('pay_in_s2s_' . $PayCode,'金额篡改！price=' . $inmoney['price'] . ' amount=' . $amount);
			}else{
				unset($indata);
				$indata['paytime'] = $systime;
				$indata['bankcode'] = $bankcode;
				$indata['paycode'] = $code;
				$indata['paystatus'] = '1';
				$indata['content'] = $message . '';
				$indata['bankorderid'] = $bankorderid . '';
				$indata['serialno'] = $serialno . '';
				$ret = $DB->update("t_inmoney",$indata,"where `payno` = '{$orderid}' and `paystatus` in ('','-1')");
				if ($ret) {
					unset($notifydata);
					$notifydata['payno'] = $orderid . '';
					$notifydata['paytype'] = $PayCode;
					$notifydata['notify_time'] = time();
					
					loginfo('pay_in_s2s_' . $PayCode,'通知订单' . $orderid . '记录成功，处理业务队列:' . json_encode($notifydata));
					
					$addnotifyres = $DB->getDRow("select * from t_pay_notify_order where payno = '{$notifydata['payno']}' and paytype = '{$notifydata['paytype']}'");
					if ($addnotifyres) {
						//已经在处理业务
						loginfo('pay_in_s2s_' . $PayCode,'通知订单' . $orderid . '已经存在，正在处理中');
					} else {
						//新增成功，初次通知
						$DB->insert("t_pay_notify_order",$notifydata);
						
						pay_Deposit($inmoney);
						
						$DB->query("delete from t_pay_notify_order where payno = '{$orderid}' and paytype = '{$PayCode}'");
					}
				} else {
					loginfo('pay_in_s2s_' . $PayCode,'回写失败，订单状态已更改！');
				}
			}
		}
	} else {
		loginfo('pay_in_s2s_' . $PayCode,$orderid . '订单不存在！');
	}
}



/**
 * 存入MT4
 */
function pay_Deposit($info) {
	global $DB;
	
	$memlogin['loginid'] = $info['mtid'];
	$memlogin['member_id'] = $info['member_id'];
	$memlogin['mtserver'] = $info['server_id'];
	$memlogin['status'] = '1';

	$mtlogin = $DB->getDRow("select * from t_member_mtlogin " . cz_where_to_str($memlogin));
	if (!$mtlogin) {
		loginfo('pay_in_s2s_','该账号未绑定' . json_encode($memlogin));
		return;
	}
	$inid = '';
	$auto = getConfigValue('AUTO_TRANSFER_MT4', $info['server_id']);
	loginfo('pay_in_s2s_','到账方式：' . $auto . ' 1:代表需要审核；其他：默认自动到账');
	$memberData = $DB->getDRow("select * from t_member where id = '{$info['member_id']}'");
	$server = $DB->getDRow("select * from t_mt4_server where id = '{$info['server_id']}' and status = 1");
	if (!$server) {
		loginfo('pay_in_s2s_',"mt4服务器" . $mtlogin['mtserver'] . "不存在");
		return;
	}
	//补
	$GLOBALS['deposit_mt4dbname'] = $server['db_name'];
	$GLOBALS['deposit_mt4dbver'] = $server['ver'];
	
	//查找是否a book用户
	$mt4user = getuser($info['mtid']);
	$GROUP = $DB->getDRow("select * from t_groups " .cz_where_to_str(array('group' => $mt4user['GROUP'], 'status' => array('in', '0,1'))));
	$autoabook = getConfigValue('AUTO_TRANSFER_MT4_ABOOK', $info['server_id']);
	//loginfo('pay_in_s2s_','分组信息'.json_encode($GROUP).' sql:'.M()->_sql());
	if ($GROUP['type'] == 'A' && $autoabook == '0') {
		loginfo('pay_in_s2s_',$mt4user['GROUP'] . "分组为ABOOK,需要手动审核");
		
		$result1 = api('gold://Mail/sendDepositNotify', array($memberData['nickname'], $info['mtid'], $info['number'], $info['type'], $info['server_id']));
		
		loginfo('pay_in_s2s_',"A BOOK 用户第三方支付到账成功,邮件：" . json_encode($result1));

	} elseif ($auto != '1') {//默认开启自动到账
		loginfo('pay_in_s2s_','默认开启自动到账');
		
		$reportModel = new ReportModel($server['db_name']);
		$ticket = $reportModel->getTicketByComment('6', "Deposit maxib#" . $info['id'], $server['ver'], $info['mtid']);
		if ($ticket) {
			$inid = $ticket['TICKET'];
			
			loginfo('pay_in_s2s_','ticket:' . $inid);
		} else {
			loginfo('pay_in_s2s_','try');
			
			try {
				$mt4api = new MtApiModel($server['ver']);
				loginfo('pay_in_s2s_',"server:" . $server['mt4_server']);
				$server['mt4_password'] = think_decrypt($server['mt4_password'], C('PASSWORD_KEY'));
				$retarry = $mt4api->connectMT4($server['mt4_server'], $server['mt4_manager'], $server['mt4_password']);
				if ($retarry['ret'] != 0) {
					loginfo('pay_in_s2s_',$retarry['info']);
				}
				loginfo('pay_in_s2s_',"开始准备入金:" . $info['mtid'] . ' amount:' . $info['number']);

				$dbinmoney = $DB->getDRow("select * from t_inmoney where `payno` = '{$info['payno']}' and status = 0");
				if ($dbinmoney) {
					if ($dbinmoney['status'] == '9') {//已经入金
						loginfo('pay_in_s2s_','该订单已经入金！已忽略');
						return;
					} else if ($dbinmoney['status'] == '8') {//入金过程中
						loginfo('pay_in_s2s_','有通知在入金中！已经忽略本地通知.');
						return;
					} else {
						$idtemp = $DB->query("update t_inmoney set status = 8 where `payno` = '{$info['payno']}' and status = 0");
						if ($idtemp) {
							loginfo('pay_in_s2s_','守护本次入金通知，其他通知排除在外！');
						}
					}
				} else {
					loginfo('pay_in_s2s_','订单状态已改变，不能入金！');
					return;
				}

				$inid = $mt4api->balance($info['mtid'], $info['number'], "Deposit maxib#" . $info['id']); //MT4入金
				$inid = $inid['ret'];
				loginfo('pay_in_s2s_',"入金返回状态:" . $inid);
			} catch (Exception $e) {
				loginfo('pay_in_s2s_',"连接mt4api接口异常");
				$data['content'] = "连接mt4api接口异常";
			}

			if ($inid == '-88') {//入金失败
				loginfo('pay_in_s2s_',"重复入金！");
				$data['content'] = "在线入金成功";
			} elseif ($inid <= 0) {//入金失败
				loginfo('pay_in_s2s_',"入金失败！");
				$data['content'] = "入金失败,状态：" . $inid;
			}
		}
		
		if ($inid == '-88') {//入金失败
			loginfo('pay_in_s2s_',"重复入金！");
			$data['content'] = "在线入金成功";
		} elseif ($inid <= 0) {//入金失败
			loginfo('pay_in_s2s_',"入金失败！");
			$data['content'] = "入金失败,状态：" . $inid;
		} else {
			$data['visit_time'] = time();
			$data['inid'] = $inid;
			$data['status'] = 9;
			$data['intime'] = time();
			$data['content'] = "在线入金成功";
		}
		
		$data['adminid'] = "onlinepay"; //线上支付

		$id = $DB->update("t_inmoney",$data,"id = '{$info['id']}'");
		if ($id) {
			$credit_ret = credit_credit($info, 'inmoney');
			$msg = "";
			if ($credit_ret) {
				$msg = "满足条件，自动为您发起赠金申请！";
				$list = api('gold://Mail/creditInEmail', array($info['id']));
			}
			//$id = D("inmoney")->where($map)->save(array('inid'=>$ids,'intime'=>time()));
			$result = api('gold://Mail/sendBalanceInEmail', array($info['id']));
			$result1 = api('gold://Mail/sendDepositNotify', array($memberData['nickname'], $info['mtid'], $info['number'], $info['type'], $info['server_id']));
			loginfo('pay_in_s2s_',$msg . "入金成功,邮件：" . json_encode($result));
		} else {
			loginfo('pay_in_s2s_',"入金更新状态失败！");
		}		
	} else {
		loginfo('pay_in_s2s_',"系统未开启自动到账，需要手动审核！AUTO_TRANSFER_MT4=0 或者其他");
		
		$result1 = api('gold://Mail/sendDepositNotify', array($memberData['nickname'], $info['mtid'], $info['number'], $info['type'], $info['server_id']));
		
		loginfo('pay_in_s2s_',"第三方支付到账成功,邮件：" . json_encode($result1));
	}
}


function chk_in_access($macc = ''){
	global $DRAdmin;
	
	$currPage = strtolower(FGetCurrUrl(8));
	if(stripos($currPage,'.') === false){
		$currPage = 'index.php';
	}
	
	//echo $currPage . $macc , '|';
	//print_r($DRAdmin['_pageAccess']);
	
	if(!in_array($currPage . $macc,$DRAdmin['_pageAccess'])){
		if(strlen($macc) <= 0){
			FRedirect('access_error.php');
		}else{
			return 0;
		}
	}
		
	return 1;	
}

function get_lang_otherset_arr($type,$serverId = -1){
	global $DB;
	global $DRAdmin;
	if($serverId < 0){
		if($DRAdmin['server_id']){
			$serverId = $DRAdmin['server_id'];
		}else{
			$dfServer = $DB->getDRow("select * from t_mt4_server where `status` = 1 order by `real` desc,default_open_svr desc");
			$serverId = intval($dfServer['id']);
		}
	}
	
	$arr = array();
	$query = $DB->query("select * from t_lang_otherset where f_serverId = '" . $serverId . "' and f_type = '{$type}'");
	while($rs = $DB->fetchArray($query)){
		$arr[$rs['f_lang']] = $rs;
	}
	
	return $arr;
}

function get_lang_otherset_val($type,$lang = '',$serverId = -1,$ifNullGetDf = 0){
	$rs = get_lang_otherset_drow($type,$lang,$serverId,$ifNullGetDf);	
	return $rs['f_val'];
}

function get_lang_otherset_drow($type,$lang = '',$serverId = -1,$ifNullGetDf = 0){
	global $DB;
	global $DRAdmin;
	global $CurrLangName;
	if($serverId < 0){
		if($DRAdmin['server_id']){
			$serverId = $DRAdmin['server_id'];
		}else{
			$dfServer = $DB->getDRow("select * from t_mt4_server where `status` = 1 order by `real` desc,default_open_svr desc");
			$serverId = intval($dfServer['id']);
		}
	}
	if(strlen($lang) <= 0){
		$lang = $CurrLangName;
	}
	
	$rs = $DB->getDRow("select * from t_lang_otherset where f_serverId = '" . $serverId . "' and f_type = '{$type}' and f_lang = '{$lang}'");
	if(!$rs && $ifNullGetDf){
		$rs = $DB->getDRow("select * from t_lang_otherset where f_serverId = '" . $serverId . "' and f_type = '{$type}'");
	}
	
	return $rs;
}

function get_mail_template_lang($name){
	global $DB;
	global $DRAdmin;
	global $CurrLangName;
	
	$template = $DB->getDRow("select * from t_mail_template where `name` = '{$name}' and status = 1");
	if($template){
		$rs = get_lang_otherset_drow($template['name'],$CurrLangName,0,1);
		
		$template['title'] = $rs['f_title'];
		$template['content'] = $rs['f_val'];
		
		//短信内容，过滤换行、html代码
		if($template['sendtype'] == 1){
			$template['content'] = trim(FRemoveHTML(str_replace("\r\n",'',$template['content'])));
		}
	}else{
		$template = array();
	}
	
	return $template;
}

function html_entity_decode_mail_template($val){
	return $val;
}

function can_look_parent_info(){
	global $DRAdmin;
	
	if(@in_array('查看客户上级归属信息',$DRAdmin['_access']['other'])){
		return true;
	}
	return false;
}

function str_jg_cal_type($jgCalType){
	$str = '';
	switch($jgCalType){
		case 'all':
			$str = L('间接客户返佣与团队返佣一起拿');
			break;
		case 'jj':
			$str = L('间接客户返佣与团队返佣只拿一个，优先拿间接客户返佣');
			break;
		case 'group':
			$str = L('间接客户返佣与团队返佣只拿一个，优先拿团队返佣');
			break;
		default:
			$str = '<span style="color:#ff0000">ERROR</span>';
			break;
	}
	return $str;
}

function L_level_name($lvName){
	if($GLOBALS['CurrLangName'] != 'zh-cn'){
		//统一为英文：
		//1级代理 = Level-1 Agent
		//1级员工 = Level-1 Staff
		//1级直客 = Level-1 Client
		//1层直客 = Level-1 Client
		//1级 = Level-1
		if(strpos($lvName,'级代理') !== false){
			return 'Level-' . str_replace('级代理','',$lvName) . ' Agent';
		}else if(strpos($lvName,'级员工') !== false){
			return 'Level-' . str_replace('级员工','',$lvName) . ' Staff';
		}else if(strpos($lvName,'级直客') !== false){
			return 'Level-' . str_replace('级直客','',$lvName) . ' Client';
		}else if(strpos($lvName,'层直客') !== false){
			return 'Level-' . str_replace('层直客','',$lvName) . ' Client';
		}else if(strpos($lvName,'级') !== false){
			return 'Level-' . str_replace('层直客','',$lvName);
		}
	}
	
	return $lvName;
}

function get_mt_root_agent($memberId,$floor = 1){
	global $DB;
	if($floor >= 100){
		return 0;
	}
	
	$rs = $DB->getDRow("select * from t_member where id = '{$memberId}'");
	if(!$rs){
		return 0;
	}
	if($rs['parent_id'] > 0){
		return get_mt_root_agent($rs['parent_id'],$floor+1);
	}
	return $rs['id'];
}

function chk_mt_is_root_agent($mid1,$mid2){
	$ra1 = get_mt_root_agent($mid1);
	$ra2 = get_mt_root_agent($mid2);
	if($ra1 > 0 && $ra1 == $ra2){
		return true;
	}
	return false;
}

function cvd_str_cal_type($calType,$calNum){
	$str = '';
	switch($calType){
		case 'FIXED':
			$str = '$' . ($calNum * 1) . '/' . L('手');
			break;
		case 'SCALE':
			$str = L('交易量') . '*' . ($calNum * 1) . '%';
			break;
		case 'POINT':
			$str = ($calNum * 1) . '/pip/' . L('手');
			break;
		case 'WIN':
			$str = L('盈利额') . '*' . ($calNum * 1) . '%';
			break;
		case 'group_win':
			$str = L('团队返佣额') . '*' . ($calNum * 1) . '%';
			break;
		default:
			$str = '<span style="color:#ff0000">ERROR</span>';
			break;
	}
	return $str;
}