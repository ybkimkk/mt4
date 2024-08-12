<?php
/*
2015-10-27 更新


*/

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>文件上传</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="script/exeUpload.js"></script>
<script type="text/javascript" src="script/swfUpload.js"></script>
</head>
<body>
<?php
require_once('../../upload/php/chk_logged.php');

require_once(CC_CZPHPHELPER_ABSPATH . 'utilcls/encrypt.class.php');

//由于swf里调用php文件，php文件无法获取到cookie（正确的说法是：ie能获取到永久cookie，其它浏览器不能获取任何cookie；火狐无法获取到session，其它浏览器可能有的能、有的不能）
//解决方法：
//1、加密数据=加密(当前域名|当前时间,加密key)
//2、接收页进行解密，必须：当前域名正确、当前时间不能超过3小时（即打开上传框，到上传完毕，不能超过3小时，一般是不会的）
//解决的是：域名使明文因各个站不同而不同，加大破解难度；时间则限制了网址被抓取也很快失效；加密key每个网站不同！
$c_encrypt = new CEncrypt();
$encryptStr = $c_encrypt->uc_authcode_encode($_SERVER['SERVER_NAME'] . '|' . time(),CC_ENCRYPT_KEY);
?>
<div class="upload_bg" style="margin-left:0px;">
	<div id="FlashLoadingOk"><div style="margin-top:180px; line-height:28px;">正在很努力的加载程序<br /><img src="images/11.gif" width="54" height="17" /></div></div>
	<div class="upload_top">
    	<ul>
        	<li><span id="AddButton"></span></li>
            <li class="ml10"><span id="UpButton" onmousedown="onk(this)" onmouseup="onu(this)"></span></li>
            <li class="fr"><span id="DelButton" onmousedown="onk(this)" onmouseup="onu(this)"></span></li>
        </ul>
    </div>
    <div class="upload_Center" id="label">请先添加要上传的图片，然后点击开始上传。</div>
    <div class="upload_Bottom">
    	<div class="Kb" id="div_off">没有图片哦，请先选择要上传的图片。</div>
        <div class="Content" id="div_on">
      		<div class="Content_title">
            	<ul>
                	<li class="W400">文件名</li>
                    <li class="W200">状态</li>
                    <li class="W49">操作</li>
                </ul>
            </div>
      	</div>
    </div>
</div>
<script type="text/javascript">
var Up = new ExeUpload("upobj","<?php echo($encryptStr); ?>");
Up.LoadUpComponents();
</script>
</body>
</html>