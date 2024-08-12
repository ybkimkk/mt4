<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/include/function.php');

$CurrLangName = $_COOKIE['lang'];
$CurrLangName = 'zh-cn';

$_lang = array();
require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/lang/' . $CurrLangName . '.php');

$key = 'H5w4NwH=x!fuHshBb8Rr5R=?';
$baseURL = "https://site.recognia.com/etomarkets/serve.shtml?tkn=";


$token = "page=economic_calendar";   #economic_calendar (Economic Insight),av_forex_ideas(Analyst Views)
$token .= "&usi=testUser";           # USER ID must be that of the user logged into your website
$token .= "&aci=" . gmdate("YmdHis");# Timestamp
$token .= "&lang=cs";                #en(English)，cs=简体中文

//$calendar_url = $baseURL . urlencode(base64_encode(TripleDES::encrypt($token, $key, false, false)));
$calendar_url = $baseURL . urlencode(TripleDES::encrypt($token, $key, false, false));
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>经济日历 - ETO MARKETS 您的私人交易专家</title>
<link rel="stylesheet" href="/plugin/tc/assets/bootstrap-4.6.1-dist/css/bootstrap.min.css">
<script src="/plugin/tc/assets/js/jquery-3.0.0.slim.min.js"></script>
<script src="/plugin/tc/assets/bootstrap-4.6.1-dist/js/bootstrap.bundle.min.js"></script>
<script src="/plugin/tc/assets/js/iframeResizer.js"></script>
<link rel="stylesheet" href="/plugin/tc/assets/css/cz.css">
</head>
<body>

<div style="width:100%;max-width:1295px; margin:0 auto;">
	<div class="banner"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/calendar-banner.jpg" width="100%"></div>

	<div class="text-center cz-dflh">
		<div class="mt-2">&nbsp;</div>
		TC 经济日历提供实时、可操作的宏观经济数据，以帮助投资者对潜在的市场变动事件采取行动。<br>
		使用实时数据过滤 38 个国家/地区的经济事件，并查看之前类似事件在价格和事件图表上的表现。
	</div>
    
	<div class="mt-1">&nbsp;</div>
    <div>
		<iframe id="tradingcentral" src="<?php echo $calendar_url;?>" allowfullscreen="true" frameborder="0" scrolling="no" width="100%" height="2500"></iframe>
    </div>

	<?php
	require_once('footer.php');
	?>

</div>

<script language="javascript">iFrameResize();</script>

</body>
</html>