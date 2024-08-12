<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/conn.php');

$CurrLangName = $_COOKIE['lang'];
$CurrLangName = 'zh-cn';

$_lang = array();
require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/lang/' . $CurrLangName . '.php');

$webTvArr = $DB->getDTable("select * from t_plugin_tc_webtv order by id desc limit 10",'id');
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Web TV - ETO MARKETS 您的私人交易专家</title>
<link rel="stylesheet" href="/plugin/tc/assets/bootstrap-4.6.1-dist/css/bootstrap.min.css">
<script src="/plugin/tc/assets/js/jquery-3.0.0.slim.min.js"></script>
<script src="/plugin/tc/assets/bootstrap-4.6.1-dist/js/bootstrap.bundle.min.js"></script>
<script src="/plugin/tc/assets/js/iframeResizer.js"></script>
<link rel="stylesheet" href="/plugin/tc/assets/css/cz.css">
</head>
<body>

<div style="width:100%;max-width:1295px; margin:0 auto;">
	<div class="banner"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/webtv-banner.jpg" width="100%"></div>

    <div class="morning-paper">
    	<div class="cz-tentxt">Web TV Market News</div>
        <div class="title">
        	<h2 class="line-txt"><span>Web TV 市场新闻</span></h2>
        </div>
    </div>
	<div class="text-center cz-dflh">
		<div class="mt-2">&nbsp;</div>
		通过结合我们的快速仪器更新和由 TC 研究部门和纽约证券交易所的记者为您带来的每日市场概览，<br>
		随时了解所有关键动态！
	</div>
    <div class="mt-1">&nbsp;</div>
    <div class="pl-3 pr-3">
		<?php
			$pid = FGetInt('pid');
			foreach($webTvArr as $key=>$val){
				if($pid <= 0){
					 $df = $val;
					 break;
				}else if($pid == $val['f_pid']){
					 $df = $val;
					 break;
				}
			}
			
            $dfMp4 = $df['f_mp4D'];
            if(strlen($dfMp4) <= 0){
                $dfMp4 = $df['f_mp4'];
            }
            
            $dfPic = $df['f_picBigD'];
            if(strlen($dfPic) <= 0){
                $dfPic = $df['f_picBig'];
            }
        ?>
        <video width="100%" src="<?php echo $dfMp4;?>" poster="<?php echo $dfPic;?>" controls="controls">
            您的浏览器不支持 video 标签。
        </video>
        <div><?php echo $df['f_title'];?></div>
        <div><?php echo $df['f_createTime'];?></div>
        <div><?php echo $df['f_description'];?></div>
        <div class="mt-1">&nbsp;</div>
        <div style="height:138px; width:100%; overflow:scroll;">
		<?php
			echo '<div style="width:' , count($webTvArr) * 165 , 'px;">';
			foreach($webTvArr as $key=>$val){
				$picSmall = $val['f_picSmallD'];
				if(strlen($picSmall) <= 0){
					$picSmall = $val['f_picSmall'];
				}
				
				echo '<a href="?pid=' , $val['f_pid'] , '"><img src="' , $picSmall , '" width="160" height="120"></a> ';
			}
			echo '</div>';
        ?>
        </div>
	</div>

	<?php
	require_once('footer.php');
	?>

</div>

<script language="javascript">iFrameResize();</script>

</body>
</html>