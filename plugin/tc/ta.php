<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/include/function.php');

$CurrLangName = $_COOKIE['lang'];
$CurrLangName = 'zh-cn';

$_lang = array();
require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/lang/' . $CurrLangName . '.php');
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>分析师观点 - ETO MARKETS 您的私人交易专家</title>
<link rel="stylesheet" href="/plugin/tc/assets/bootstrap-4.6.1-dist/css/bootstrap.min.css">
<script src="/plugin/tc/assets/js/jquery-3.0.0.slim.min.js"></script>
<script src="/plugin/tc/assets/bootstrap-4.6.1-dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="/plugin/tc/assets/css/cz.css">
</head>
<body>

<div style="width:100%;max-width:1295px; margin:0 auto;">
	<div class="banner"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/ta-banner.jpg" width="100%"></div>
    
    
    <?php
    $id = FGetInt('id');
	$fsign = FGetStr('sign');
	if($id > 0 && strlen($fsign) > 0){
		$f = dz_authcode($fsign,'DECODE',CC_PLUGIN_TC_SIGN_KEY,0);
		if(strlen($f) <= 0){
			echo 'sign error!';
			exit;
		}
		
		$xml = FReadFileUtf8($_SERVER['DOCUMENT_ROOT'] . $f);
		$obj = simplexml_load_string($xml,"SimpleXMLElement", LIBXML_NOCDATA);
		foreach($obj->article as $article){
			$aid = $article['id'];
			if($aid == $id){
				$title = $article->analysis->content->story->title;
				
				$date = date('Y-m-d',strtotime($article->analysis->content->header->date));
				$hour = $article->analysis->content->header->hour;
				
				$media = $article->analysis->content->header->media;
				?>
                <div>
                    <div class="mt-2">&nbsp;</div>
                    <div class="morning-paper">
                        <div class="cz-tfonts"><?php echo $title;?></div>
                        <div class="cz-tentxt" style="text-align:left;"><?php echo $date , ' ' , $hour;?></div>
                    </div>
                
                    <div class="mt-2">&nbsp;</div>
                    <div class="cz-dflh">
                    	<img src="<?php echo $media;?>"><br><br>
                        <?php
                        foreach($article->analysis->content->story->children() as $a=>$b){
							if(!in_array($a,array('keywords','title','summary'))){
								if($b['type'] != 'disclaimer'){
									echo $b , '<br>';
								}
							}
						}
						?>
                    </div>
                </div>
				<?php
				break;
			}
		}
	}else{
		?>
		<div class="pl-3 pr-3" style="background-color:#f8f8f8;margin:20px 0;">
			<div class="row">
				<div class="col-sm-4">
					<div style="margin:20px 10px; background-color:#ffffff;padding:20px 0;">
						<div class="text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/ta-1.jpg"></div>
						<div class="text-center">实时更新</div>
						<div class="cz-stxt">实时更新资深分析师观点，您可基于分析师提供的价位建立订单。</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div style="margin:20px 10px; background-color:#ffffff;padding:20px 0;">
						<div class="text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/ta-2.jpg"></div>
						<div class="text-center">7*24全天候监控</div>
						<div class="cz-stxt">通过8,000多种金融工具7*24全天候监控，实时更新分析报告。</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div style="margin:20px 10px; background-color:#ffffff;padding:20px 0;">
						<div class="text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/ta-3.jpg"></div>
						<div class="text-center">双重分析</div>
						<div class="cz-stxt">独特的量化投资和技术分析相结合，适用于多数投资类型及方式。</div>
					</div>
				</div>
			</div>
		</div>
	
		<div style="background-color:#f8f8f8;">
			<div class="mt-2">&nbsp;</div>
			<div class="morning-paper">
				<div class="cz-tentxt">Learn from the Experts</div>
				<div class="title">
					<h2 class="line-txt"><span>向专家学习</span></h2>
				</div>
			</div>
		
			<div class="mt-2">&nbsp;</div>
			<div class="text-center cz-dflh">
				ETO MARKETS联合Trading Central的全球研究团队在这里帮助您利用产品的力量！<br>
				在下面的市场更新中，我们的专家分析师使用技术洞察来寻找新的机会！
			</div>
			
			<?php
			$arr = plugin_tc_api_get_ta($filePath,$fileName);
			$f = $filePath . $fileName;
			$fsign = urlencode(dz_authcode($f,'ENCODE',CC_PLUGIN_TC_SIGN_KEY,0));
			foreach($arr->article as $article){
				$aid = $article['id'];
				
				$title = $article->analysis->content->story->title;
				$summary = $article->analysis->content->story->summary;
				
				$media = $article->analysis->content->header->media;
				$date = date('Y-m-d',strtotime($article->analysis->content->header->date));
				$hour = $article->analysis->content->header->hour;
				?>
				<div class="mt-2">&nbsp;</div>
				<div class="pl-3 pr-3" style="background-color:#ffffff; margin:0 15px;padding:25px 0;">
					<div class="row">
						<div class="col-sm-1">&nbsp;</div>
						<div class="col-sm-5 text-center"><img src="<?php echo $media;?>" width="400" height="300"></div>
						<div class="col-sm-5 cz-dflh">
							<div class="cz-tfonts"><?php echo $title;?></div>
							<div class="cz-stxt1"><?php echo $summary;?></div>
							<div>
								<a href="?sign=<?php echo $fsign;?>&id=<?php echo $aid;?>" class="ta-btn1" target="_blank">点击查看&gt;</a>
								<span style="display:inline-block; float:right;color:#727272;font-size:1rem;"><?php echo $date , ' ' , $hour;?></span>
							</div>
						</div>
						<div class="col-sm-1">&nbsp;</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>

	<?php
	require_once('footer.php');
	?>

</div>

</body>
</html>