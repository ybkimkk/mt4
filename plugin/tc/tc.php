<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');

$CurrLangName = $_COOKIE['lang'];
$CurrLangName = 'zh-cn';

$_lang = array();
require_once($_SERVER['DOCUMENT_ROOT'] . '/plugin/tc/lang/' . $CurrLangName . '.php');
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Trading Central - ETO MARKETS 您的私人交易专家</title>
<link rel="stylesheet" href="/plugin/tc/assets/bootstrap-4.6.1-dist/css/bootstrap.min.css">
<script src="/plugin/tc/assets/js/jquery-3.0.0.slim.min.js"></script>
<script src="/plugin/tc/assets/bootstrap-4.6.1-dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="/plugin/tc/assets/css/cz.css">
</head>
<body>

<div style="width:100%;max-width:1295px; margin:0 auto;">
	<div class="banner"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/tc-banner.jpg" width="100%"></div>
    
	<div class="mt-1">&nbsp;</div>
    <div class="morning-paper">
    	<div class="cz-tentxt">Morning Paper</div>
        <div class="title">
        	<h2 class="line-txt"><span>晨报</span></h2>
        </div>
        <div class="cz-tsubtxt">发现可行的交易计划</div>
    </div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-2">&nbsp;</div>
            <div class="col-sm-3 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/morning-paper.jpg"></div>
            <div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5">
            	<div class="mt-4 d-none d-sm-block">&nbsp;</div>
                <div class="cz-dflh">Analyst Views 是世界上唯一提供高级市场专业知识和自动化算法的独特组合的金融市场研究解决方案。我们专有的模式识别监控市场，而我们的全球专家市场技术团队在场以验证所有分析输出，以确保仅发布最佳分析。</div>
            </div>
            <div class="col-sm-1">&nbsp;</div>
        </div>
        <div class="text-center cz-dflh">
        	<div class="mt-2">&nbsp;</div>
            在这里，我们正在查看Trading Central的签名图表。价格图表上可能绘制了指标、模式或趋势线。<br>
			这让您可以一瞥技术分析方法，但让我们关注输出。
        </div>
	</div>

	<div class="mt-4">&nbsp;</div>
    <div class="pl-3 pr-3" style="background-color:#F1F1F1;">
		<div class="pt-5">&nbsp;</div>
        <div class="text-center cz-tfonts">技术亮点</div>
		<div class="pt-1">&nbsp;</div>
		<div class="text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/technology.jpg" style="width:68%;"></div>
		<div class="pt-1">&nbsp;</div>
        <div class="row" style="width:70%;margin:0 auto;">
        	<div class="col-sm-4">1、清晰的方向感</div>
            <div class="col-sm-4 text-center">2、观察支点</div>
            <div class="col-sm-4 text-right">3、目标帮助您获利</div>
        </div>
		<div class="pb-5">&nbsp;</div>
	</div>

	<div class="mt-5">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/technology1.jpg"></div>
            <div class="col-sm-5 cz-dflh">
				<div class="mt-2 d-none d-sm-block">&nbsp;</div>
				<div class="cz-tfonts">1、清晰的方向感</div>
				<div>粗体蓝色箭头给出了直接而清晰的方向感。在这种情况下，预期的情况是价格可能会上涨。</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>

	<div class="mt-5">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 cz-dflh">
				<div class="cz-tfonts">2、观察支点</div>
				<div>
				只要价格保持在这条蓝色枢轴线上方，预期的情况只是偏好。如果价格越过枢轴线，另一种情况就会出现，预计价格会朝另一个方向发展。<br><br>
				止损帮助您保存资本<br>
				我们的交易客户经常使用这个枢轴水平作为止损价来保护他们的头寸。触及此止损意味着持仓的原因不再存在，因此他们平仓。
				</div>
			</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/technology2.jpg"></div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>

	<div class="mt-5">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/technology3.jpg"></div>
            <div class="col-sm-5 cz-dflh">
				<div class="mt-2 d-none d-sm-block">&nbsp;</div>
				<div class="cz-tfonts">3、目标帮助您获利</div>
				<div>现在，止损很重要，但让我们也计划获利退出！对于这种价格上涨的情况，我们提供了两个阻力位作为目标。一种典型的做法是在第一个目标位平仓一半以锁定一些利润，然后在第二个目标位平仓另一半头寸。</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>

	<div class="mt-5">&nbsp;</div>
    <div class="morning-paper">
    	<div class="cz-tentxt">TC Economic Calendar</div>
        <div class="title">
        	<h2 class="line-txt"><span>TC 经济日历</span></h2>
        </div>
        <div class="cz-tsubtxt">监控、预测和行动！</div>
    </div>
	<div class="text-center cz-dflh">
		<div class="mt-2">&nbsp;</div>
		TC 经济日历提供实时、可操作的宏观经济数据，以帮助投资者对潜在的市场变动事件采取行动。<br>
		使用实时数据过滤 38 个国家/地区的经济事件，并查看之前类似事件在价格和事件图表上的表现。
	</div>
	<div class="col-sm-1">&nbsp;</div>
	<div class="text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/ec.jpg"></div>

	<div class="mt-5">&nbsp;</div>
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
	<div class="col-sm-1">&nbsp;</div>
	<div class="text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/webtv.jpg"></div>

	<div class="mt-5">&nbsp;</div>
    <div class="morning-paper">
    	<div class="cz-tentxt">MT4 Index</div>
        <div class="title">
        	<h2 class="line-txt"><span>MT4指标</span></h2>
        </div>
    </div>

	<div class="mt-2">&nbsp;</div>
	<div class="cz-tfonts cz-tcolorr">分析师观点</div>
	<div class="text-center cz-dflh">
		我们的分析师观点指标可以给您提供趋势的方向和重要技术点位。<br>
		TC乾鼎凭借其屡获殊荣的技术分析方法，<br>对于每个图表分析运用方向性的预测，永远在这里支持您的投资决策。
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi1.jpg"></div>
            <div class="col-sm-5 cz-dflh">
				<div class="mt-2 d-none d-sm-block">&nbsp;</div>
				<div class="cz-tfonts">关键技术点位帮助您的交易</div>
				<div>将您的分析留给专家，您可以直接使用我们的分析结果来帮助您进行交易。您可以直观的了解到我们的趋势观点及目标点位。同时，您可以发现备选策略，通过我们给出的转折点，将会告诉您在何时转变趋势观点，并给出备选策略的目标价位。</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>

	<div class="mt-5">&nbsp;</div>
	<div class="mt-2">&nbsp;</div>
	<div class="cz-tfonts cz-tcolorr">自适应蜡烛图</div>
	<div class="text-center cz-dflh">
		交易者喜爱的日本蜡烛图中，您可以得到及时的蜡烛技术图形，<br>
		用来告诉您供求关系变化和买卖双方博弈的重要信息。
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 cz-dflh">
				<div class="mt-2 d-none d-sm-block">&nbsp;</div>
				<div class="cz-tfonts">我们精选的图形</div>
				<div>TC MT4指标可以对任何图表进行扫描，并及时发现１６种常用和精选的蜡烛技术图形。</div>
			</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi2.jpg"></div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi3.jpg"></div>
            <div class="col-sm-5 cz-dflh">
				<div class="mt-2 d-none d-sm-block">&nbsp;</div>
				<div class="cz-tfonts">专家为您筛选重要信息</div>
				<div>我们将蜡烛图与独特的量化和技术分析相结合，专注于对当前阶段的投资决策有帮助的相关技术图形</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>

	<div class="mt-5">&nbsp;</div>
	<div class="mt-2">&nbsp;</div>
	<div class="cz-tfonts cz-tcolorr">自适应趋异指标</div>
	<div class="text-center cz-dflh">
		如果您喜欢用MACD指标，您也同样会在短期交易策略中喜爱上自适应趋异指标(ADC)！<br>
		它比MACD指标在短期交易中更有帮助，并且能提供更多及时的交易信号。<br>
		同时，在市场震荡过程中，它的信号会对时间周期自动调整。
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 cz-dflh">
				<div class="cz-tfonts">多仓或空仓进场/离场信号</div>
				<div>标签会帮助提醒您交易机会: ↑ (多仓买入), LX (多仓离场), ↓ (空仓买入), SX (空仓离场)。信号是基于所有自适应趋异指标(ADC)组成部分而给出，包括价格线，指标和震荡因子。这些组成部分可以显示在可适应的价格数据窗口，它们相互之间独立，但是我们可以通过权重来做出决策。</div>
			</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi4.jpg"></div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi5.jpg"></div>
            <div class="col-sm-5 cz-dflh">
				<div class="cz-tfonts">快速或慢速价格指标</div>
				<div>我们使用一条加权前的移动均线作为慢速指标。快速指标用变化的加权移动均线—趋势市场下周期更短，震荡市场周期更长。这样可以更有效，因为，其他移动均线确实趋势，往往给出的信号更慢: 观察趋势向上，快速指标在慢速指标上方; 或者价格穿越指标的上方。这些是自适应趋异指标(ADC)组成部分的基本元素</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 cz-dflh">
				<div class="cz-tfonts">初始和平滑信号线</div>
				<div>对于快速信号，观察到向上的斜率和ADC初始信号线在ADC平滑信号线上方时，将会是买入信号。对于慢速信号，观察到ADC初始信号线及ADC平滑信号线同时在0线上方。如同MACD指标，初始信号线代表快慢指标的差值，这是平滑后的指数移动均线，但是在这种情况下，我们发现平滑后的自动长度值，通常在4左右，不同于MACD指标的值为9。</div>
			</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi6.jpg"></div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>
	<div class="mt-2">&nbsp;</div>
    <div class="pl-3 pr-3">
        <div class="row">
        	<div class="col-sm-1">&nbsp;</div>
            <div class="col-sm-5 text-center"><img src="/plugin/tc/assets/images/<?php echo $CurrLangName;?>/mi7.jpg"></div>
            <div class="col-sm-5 cz-dflh">
				<div class="cz-tfonts">震荡因子 快速和慢速</div>
				<div>ADC指标有两个震荡因子。对于快速信号，观察快速震荡因子将会向上，并在零线上方。对于慢速信号，观察快速震荡因子在慢速因子上方，或者观察到慢速震荡因子斜率向上，并处于快速因子下方，同时高于自适应变量。</div>
			</div>
			<div class="col-sm-1">&nbsp;</div>
        </div>
	</div>

	<?php
	require_once('footer.php');
	?>

</div>

</body>
</html>