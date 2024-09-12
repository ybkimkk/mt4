<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/encoding.php');
require_once('conn.php');
require_once('chk_logged.php');


//print_r($webConfig);exit;

$LoadCSSArr = array(
    '/assets/css/vendor/jquery-jvectormap-1.2.2.css'
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_buyModel.class.php');

//MT用户数
$userInfo = $DRAdmin;
$mtlist = $DB->getField2Arr("select id,loginid from t_member_mtlogin where `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}' and `member_id` = '{$DRAdmin['id']}'");
$mtcount = count($mtlist);

$mtserver = $DB->getDRow("select * from t_mt4_server where `status` = 1 and id = '{$DRAdmin['server_id']}'");

//入金数
$reportModel = new ReportModel($DRAdmin['mt4dbname'], $DRAdmin['ver']);

//$depositModel = new DepositModel($DRAdmin['mt4dbname']);
//

//$is_have_Cache = $DB->getDRow("select * from t_mt_member_trades where server_id = '{$DRAdmin['server_id']}' and loginid in (" . implode(',',$mtlist) . ")");
//if ($is_have_Cache) {
//	$cache_data = $reportModel->getCacheData($mtlist,date('Y-m-d',100000),date('Y-m-d',time()),1);
//	$depoistamount = $cache_data['depoistamount'];
//	$withdrawamount = $cache_data['withdrawamount'];
//	$profitamount = $cache_data['profitamount'];
//	$totalvolume = $cache_data['totalvolume'];
//}else{
$depoistamount = $reportModel->sumInBalance($mtlist);
$withdrawamount = $reportModel->sumOutBalance($mtlist);
$profitamount = $reportModel->sumProfit($mtlist, '');
$totalvolume = $reportModel->sumVolume($mtlist);
$totalvolume = $totalvolume / 100;
//}
//缓存


//$this->mttotal = $reportModel->sumProfit($mtlist,$where);
if ($mtserver['ver'] == 5) {
    $uncloseamount = $DB->getDRow("select sum(Volume) as VOLUME,round(sum(Profit),2) as Profit from " . $mtserver['db_name'] . ".mt5_positions where Login in (" . ($mtlist ? implode(',', $mtlist) : '0') . ") and Action in (0,1)");
    $unclosevolume = $uncloseamount['VOLUME'];
    $uncloseamount = $uncloseamount['Profit'];
} else {
    $where['CLOSE_TIME'] = '1970-01-01 00:00:00';
    $unclosevolume = $reportModel->sumUncloseVolume($mtlist, $where);
    $uncloseamount = $reportModel->sumUncloseAmount($mtlist, $where);
}

$mttotal = sumBalance($mtlist);
//返佣总额 t_sale_commission_banalce  TYPE=0 求和Amount.

$starttime = 0;
$endtime = time();
$commission_banalce1 = floatval($DB->getField("select sum(Amount) from t_sale_commission_balance " . cz_where_to_str(array('SERVER_ID' => $DRAdmin['server_id'], 'MEMBER_ID' => $DRAdmin['id'], 'TYPE' => '0', 'CREATE_TIME' => array(array('egt', $starttime), array('elt', $endtime))))));
$commission_banalce2 = floatval($DB->getField("select sum(f_cal) from t_sale_commission_other " . cz_where_to_str(array('f_serverId' => $DRAdmin['server_id'], 'f_uid' => $DRAdmin['id'], 'f_isJs' => '1', 'f_jsTime' => array(array('egt', date('Y-m-d H:i:s', $starttime)), array('elt', date('Y-m-d H:i:s', $endtime)))))));
$commission_banalce = $commission_banalce1 + $commission_banalce2;

$data = $DB->getDRow("select * from t_member where id = '{$DRAdmin['id']}'");
if (!$data['avatar']) {
    $url = C('cz_REGISTER_URL') . "?fromuser=" . $data['id'];
    $data['avatar'] = showqrcode($url, 8, '/upload/member/qrcode/' . md5($data['id'] . $data['email']) . '.png');

    $DB->query("update t_member set `avatar` = '{$data['avatar']}' where id = '{$DRAdmin['id']}'");
} else {
    $url = C('cz_REGISTER_URL') . "?fromuser=" . $data['id'];
    $data['avatar'] = showqrcode($url, 8, '/upload/member/qrcode/' . md5($data['id'] . $data['email']) . '.png');

    $DB->query("update t_member set `avatar` = '{$data['avatar']}' where id = '{$DRAdmin['id']}'");
}

//查询跟单是否配置跟单系统
/*
$isopenfollow = C("OPEN_INDEX_FOLLOW_STATUS");
if ($isopenfollow == 1) {
	$follomodel=new FollowModel($DRAdmin['mt4dbname']);
	$indexfollow      = 50;
	if($this->mtserver['ver']=='5'){
		$this->followlist = D("Mt5_buy")->get_follow_mt5profit($indexfollow);
	}else{
		$this->followlist = $follomodel->get_follow_profit($indexfollow,$DRAdmin['mt4dbname']);
	}

	//查询最近最近30天的盈利排行
	$serverwhere['status'] = '1';

	$serverwhere['id'] = $DRAdmin['server_id'];
	$servers = M('mt4_server')->where($serverwhere)->find();
	$showday= explode(',',C("FOLLOE_SHOW_DAY"));
	$showrankday='-7 days';
	if(in_array(7, $showday)){
		$showrankday='-7 days';
	}else if(in_array(30, $showday)){
		$showrankday='-30 days';
	}else if(in_array(365, $showday)){
		$showrankday='-365 days';
	}
	$time7 = gmdate("Y-m-d", strtotime(date('Y-m-d', strtotime($showrankday))) + 3600 * floatval($servers['time_zone']));

	if (C("FOLLOW_STAR_INDEX")) {
		$star_user = C("FOLLOW_STAR_INDEX");
	} else {
		$star_user = '';
	}

	$this->monthsort = $follomodel->getRank($time7, gmdate("Y-m-d", time() + 3600 * floatval($servers['time_zone']) + 86400), 8, 'desc', $star_user, 0);
	$this->number = count($this->monthsort);
	$this->openredect = C('OPEN_DIRECT');
	//禁止交易分组
	$isnotalowgroup = M("follow_group_config")->getField('ftype,content');
	if ($isnotalowgroup[0]) {
		$alowgroup[0] = implode(',', M("symbol")->where(array('symbol' => array('not in', $isnotalowgroup[0])))->getField('id,symbol'));
	} else {
		$alowgroup[0] = implode(',', M("symbol")->getField('id,symbol'));
	}
	if ($isnotalowgroup[1]) {
		$alowgroup[1] = implode(',', M("symbol")->where(array('symbol' => array('not in', $isnotalowgroup[1])))->getField('id,symbol'));
	} else {
		$alowgroup[1] = implode(',', M("symbol")->getField('id,symbol'));
	}

	$this->followgroup = $alowgroup;
	//信号源收费
	$follow_used       = M("follow_sale_setting")->where(array('type' => array('in', 'follow,parent,system'), 'status' => 1, 'number_type' => 0, 'server_id' => $DRAdmin['server_id']))->getField('sum(number)');
	$this->follow_used = $follow_used * 100;
	//查询我的跟单情况
	$this->mylogin = D("MemberMtlogin")->where(array('member_id' => $DRAdmin['id'], 'status' => 1))->select();
	$loginarray    = array();
	foreach ($this->mylogin as $key => $value) {
		$loginarray[] = $value['loginid'];
	}
}
*/

$invent_code = $DB->getField("select loginid from t_member_mtlogin " . cz_where_to_str(array('member_id' => $DRAdmin['id'], 'mtserver' => $DRAdmin['server_id'], 'status' => 1)));
$followamount = $DB->getField("select sum(AMOUNT) as sum1 from t_sale_commission_balance where COMM_TYPE = 1 and MEMBER_ID = '{$uid}'");
//$lastnotice = D('Article')->where(array('status' => 1, 'cid' => 1))->order('create_time desc')->limit(0, 5)->select();

//平台半年交易折线图
//$reportModel         = new ReportModel($DRAdmin['mt4dbname']);
$sstarttime = strtotime(date("Y-m-d", strtotime('-1 month')) . ' 00:00:00');
$eendtime = time();
if ($DRAdmin['ver'] == 5) {
    //$barwhere['CLOSE_TIME'] = array('neq', '1970-01-01 00:00:00');
    $barwhere = array();
    $linewhere = array();
} else {
    $barwhere['CLOSE_TIME'] = array('neq', '1970-01-01 00:00:00');
    $linewhere['CLOSE_TIME'] = "1970-01-01 00:00:00";
}
$profit = $reportModel->sumRiskProfit($mtlist, $barwhere, 1);
if (count($profit) <= 7) {
    $barnum = 35;
} elseif (count($profit) > 7 && count($profit) <= 20) {
    $barnum = 20;
} elseif (count($profit) > 20 && count($profit) <= 31) {
    $barnum = 8;
}

$uncloseprofit = $reportModel->sumRiskProfit($mtlist, $linewhere, 3);

//$this->uploadfollowcert=M('config', 'mt4svr_')->where(array('LOGINID' => array('in', $loginarray), 'STATUS' => array('in','0,-1')))->count();

//$m_starttime  = D('MtMemberTrades')->where("server_id = " . $DRAdmin['server_id'] . " ")->Field('addtime')->order('id DESC')->find();
//if ($m_starttime) {
//	$this->assign('start', $m_starttime['addtime']);
//}

//胜率分析
//盈 
$pieresult['makemoney'] = $reportModel->getProfitLoss($mtlist, 1, $barwhere);

//$pieresult['makemoney']['name'] = '获利笔数';
//亏
$pieresult['losemoney'] = $reportModel->getProfitLoss($mtlist, '', $barwhere);
$where['CLOSE_TIME'] = array('between', array(date('Y-m-d H:i:s', $starttime), date('Y-m-d H:i:s', $endtime)));

$lineprofit = $profit;
$starttime = date('Y-m-d H:i:s', $starttime);
$endtime = date('Y-m-d H:i:s', $endtime);
$mtlist = implode(',', $mtlist);
?>


<!-- Start Content-->
<div class="container-fluid">

    <!--
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right" style="float:left;">
                                        <form class="form-inline">
                                        	<?php
    $Type = FGetStr('type');
    if ($Type == '') {
        echo '<a href="?" class="btn btn-primary ml-2"><i class="fa fa-check-circle"></i> ', L('个人首页'), '</a>';
        echo '<a href="?type=group" class="btn btn-light ml-1">', L('团队首页'), '</a>';
    } else if ($Type == 'group') {
        echo '<a href="?" class="btn btn-light ml-2">', L('个人首页'), '</a>';
        echo '<a href="?type=group" class="btn btn-primary ml-1"><i class="fa fa-check-circle"></i> ', L('团队首页'), '</a>';
    }
    ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        -->
    <br>

    <div class="row">
        <div class="col-lg-<?php if ($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0) {
            echo '3';
        } else {
            echo '2';
        } ?>">
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0"><?php echo L("MT余额"); ?> $</h5>
                    <h3 class="mt-3 mb-3"><?php echo MillionRound($mttotal, 2); ?></h3>
                    <p class="mb-0 text-muted">
                        <span class="text-success mr-2"><?php echo L("MT账号数"); ?></span>
                        <span class="text-nowrap"><?php echo $mtcount; ?></span>
                    </p>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <div class="col-lg-<?php if ($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0) {
            echo '3';
        } else {
            echo '2';
        } ?>">
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0"><?php echo L("入"), '/', L("出金量"); ?> $</h5>
                    <h3 class="mt-3 mb-3"><?php echo round($depoistamount, 2), ' / -', MillionRound(abs($withdrawamount), 2); ?></h3>
                    <p class="mb-0 text-muted">
                        <span class="text-success mr-2"><?php echo L("净入金"); ?> $</span>
                        <span class="text-nowrap"><?php echo MillionRound($depoistamount + $withdrawamount, 2); ?></span>
                    </p>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <div class="col-lg-2"<?php if ($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0) {
            echo ' style="display:none;"';
        } ?>>
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0"><?php echo L("返佣总额"); ?> $</h5>
                    <h3 class="mt-3 mb-3"><?php echo round($commission_banalce, 2); ?></h3>
                    <p class="mb-0 text-muted" onclick="window.location.href='out_amount.php'" style="cursor:pointer;">
                        <span class="text-success mr-2"><?php echo L("未提现金额"); ?> $</span>
                        <span class="text-nowrap"><?php echo $data['amount']; ?></span>
                    </p>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <div class="col-lg-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0"><?php echo L("交易量"), '/', L("盈亏"); ?> $</h5>
                    <h3 class="mt-3 mb-3"><?php echo MillionRound($totalvolume, 2), ' / ', MillionRound($profitamount, 2); ?></h3>
                    <p class="mb-0 text-muted">
                        <span id="sparkline">Loading...</span><br>
                        <span class="mouseoverregion controls"></span>
                    </p>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <div class="col-lg-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <h5 class="text-muted font-weight-normal mt-0"><?php echo L("持仓量"), '/', L("浮动盈亏"); ?> $</h5>
                    <h3 class="mt-3 mb-3"><?php echo MillionRound($unclosevolume / 100, 2), ' / ', MillionRound($uncloseamount, 2); ?></h3>
                    <p class="mb-0 text-muted">
                        <span id="sparkline1">Loading...</span><br>
                        <span class="mouseoverregion1 controls"></span>
                    </p>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-xl-<?php if ($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0) {
            echo '6';
        } else {
            echo '4';
        } ?>">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mt-2"><?php echo L("通知公告"); ?></h4>

                    <div class="table-responsive">
                        <table class="table table-centered table-hover mb-0">
                            <tbody>
                            <?php
                            if (@in_array('e304', $DRAdmin['_access'])) {
                                //and cid = 1
                                $scrollnotice = $DB->getDTable("select * from t_article where status = 1 and f_key = '' order by sort asc,create_time desc limit 3");
                                foreach ($scrollnotice as $key => $val) {
                                    $DRInfo1 = get_lang_otherset_drow('-_news_-' . $val['id'], $CurrLangName, $DRAdmin['server_id'], 1);

                                    echo '<tr>';
                                    echo '<td><a href="myarticle.php?clause=showinfo&id=', $val['id'], '">', L($DRInfo1['f_title']), '</a> <i class="fa fa-clock-o"></i> ', date('Y-m-d', $val['create_time']), '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div> <!-- end table-responsive-->
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <div class="col-xl-<?php if ($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0) {
            echo '6';
        } else {
            echo '5';
        } ?>">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mt-2"><?php echo L("活动信息"); ?></h4>

                    <div class="table-responsive">
                        <table class="table table-centered table-hover mb-0">
                            <tbody>
                            <?php
                            if (@in_array('e307', $DRAdmin['_access'])) {
                                //and cid = 1
                                $scrollnotice = $DB->getDTable("select * from t_activity_list where server_id = '{$DRAdmin['server_id']}' and `status` > 0 and start_time <= '" . date('Y-m-d H:i:s') . "' order by start_time desc,end_time desc limit 3");
                                foreach ($scrollnotice as $key => $val) {
                                    $DRInfo1 = get_lang_otherset_drow('-_activity_-' . $val['id'], $CurrLangName, $DRAdmin['server_id'], 1);

                                    echo '<tr>';
                                    echo '<td><a href="myactivity.php?clause=showinfo&id=', $val['id'], '">', L($DRInfo1['f_title']), '</a> <i class="fa fa-clock-o"></i> ', date('Y-m-d', strtotime($val['start_time'])), '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div> <!-- end table-responsive-->
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <?php if ($DRAdmin['userType'] == 'direct' && $webConfig['f_directCanRecom'] <= 0) {

        } else { ?>
            <div class="col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title"><?php echo L("推广开户"); ?></h4>
                        <div class="ibox-content">
                            <div class="row" style="text-align:center; display:block;">
                                <div class="col-xs-12">
                                    <h5><span id='text'><img width="96px"
                                                             src="<?php echo trim($data['avatar'], '.'); ?>"></span>
                                        <p class="form-control-static"><?php
                                            echo C('cz_REGISTER_URL');
                                            echo '?fromuser=';
                                            echo $data['id'];
                                            //echo '&l=' . $CurrLangName;
                                            ?></p>
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="ibox-content">
                            <div class="row" style="text-align:center; display:block;">
                                <div class="col-xs-12">
                                    <p class="form-control-static">
                                        <?php
                                        $isopencode = explode(",", C('REGEX_FILEDS'));
                                        if (in_array('register_invent_codes', $isopencode)) {
                                            echo '<b>', L("邀请码"), '</b>&nbsp;<span id="text" style="color:green;font-size:12px">', $invent_code, '</span>';
                                        } else {
                                            echo L("暂未开放");
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col-->
        <?php } ?>

    </div>
    <!-- end row -->


    <div class="row">
        <div class="col-xl-9">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title"><?php echo L("交易盈亏分析"); ?> ($)</h4>
                    <div class="ibox-content">
                        <div class="echarts" id="e-line-chart" style="height:240px;"></div>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

        <div class="col-xl-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title"><?php echo L("胜率分析"); ?></h4>
                    <div class="ibox-content">
                        <div class="echarts" id="e-pie-chart" style="height:240px;"></div>
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->

    </div>
    <!-- end row -->


</div>
<!-- container -->


<?php
require_once('footer.php');
?>

<script src="/assets/js/echarts/echarts-all.js"></script>
<script src="/assets/js/echarts/echarts.js"></script>
<script src="/assets/js/sparkline/jquery.sparkline.min.js"></script>


<script type="text/javascript">
    var lineChart = '<?php echo escapeJsonString(json_encode($lineprofit)); ?>';
    var json_obj = JSON.parse(lineChart);
    var result = json_obj;
    var date = [];
    var data = [];

    for (var i = 0; i < result.length; i++) {
        data.push(result[i].PROFIT);
        date.push(result[i].newtime);
    }


    var smallChart = '<?php echo escapeJsonString(json_encode($uncloseprofit)); ?>';
    var json_obj_s = JSON.parse(smallChart);
    var s_result = json_obj_s;
    var sdata = [];
    var sdate = [];
    for (var i = 0; i < s_result.length; i++) {
        sdata.push(s_result[i].PROFIT);
        sdate.push(s_result[i].newtime);

    }

    $(function () {

        // console.log(date);return false;
        var t = echarts.init(document.getElementById("e-line-chart")),

            option = {
                tooltip: {
                    trigger: 'axis',
                    position: function (pt) {
                        return [pt[0], '10%'];
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: date
                },
                yAxis: {
                    type: 'value',
                    boundaryGap: [0, '100%']
                },
                dataZoom: [{
                    handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                    handleSize: '80%',
                    handleStyle: {
                        color: '#fff',
                        shadowBlur: 3,
                        shadowColor: 'rgba(220,220,220,0.5)',
                        shadowOffsetX: 2,
                        shadowOffsetY: 2
                    }
                }],
                series: [
                    {
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        sampling: 'average',
                        itemStyle: {
                            normal: {
                                color: 'rgb(26,179,168)'
                            }
                        },
                        areaStyle: {
                            normal: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0,
                                    color: 'rgb(26,179,148)'
                                }, {
                                    offset: 1,
                                    color: 'rgb(26,179,148)'
                                }])
                            }
                        },
                        data: data
                    }
                ]
            };


        t.setOption(option),
            window.onresize = t.resize;


    });

    // $(document).ready(function(){
    //     var myChart = document.getElementById('sparkline2');
    //     //自适应宽高
    //     var myChartContainer = function () {
    //         myChart.style.width = document.getElementById("sparklinefff").offsetWidth+ "px";;
    //         myChart.style.height = document.getElementById("sparklinefff").offsetHeight+ "px";
    //     };
    //     console.log(myChart.style.width)

    //     myChartContainer();
    //     var myChart = echarts.init(myChart);

    //     // var t = echarts.init(document.getElementById("sparkline2")),
    //     console
    //     option = {
    //         color: ['#3398DB'],
    //         tooltip : {
    //             trigger: 'axis',
    //             axisPointer : {            // 坐标轴指示器，坐标轴触发有效
    //                 type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
    //             }
    //         },
    //         grid: {
    //             left: '3%',
    //             right: '4%',
    //             bottom: '3%',
    //             containLabel: true
    //         },
    //         xAxis : [
    //             {
    //                 // show : false,
    //                 type : 'category',
    //                 data : date,
    //                 axisTick: {
    //                     alignWithLabel: true
    //                 }
    //             }
    //         ],
    //         yAxis : [
    //             {
    //                 // show : false,
    //                 type : 'value'
    //             }
    //         ],
    //         series : [
    //             {
    //                 name:'盈亏',
    //                 type:'bar',
    //                 barWidth: 8,
    //                 width:'100px',
    //                 height:'40px',
    //                 data:data
    //             }
    //         ]
    //     };

    // t.setOption(option),
    // window.onresize = t.resize;
    // t.setOption(option);
    //         $(window).resize(function() {
    //             //重置容器高宽
    //             t.resize();
    //         });

    // 使用刚指定的配置项和数据显示图表。
    //  myChart.setOption(option);

    //  //浏览器大小改变时重置大小
    //      window.onresize = function () {
    //         myChartContainer();
    //         myChart.resize();
    //     };

    // });


    var barnum = '<?php echo escapeJsonString(json_encode($barnum)); ?>';

    //dataList是从后台传回来的数据，样式如同[1,3,4,5,6,3,2]，dateList也是从后台传回来的，是一个时间的list，用来做提示显示
    $('#sparkline').sparkline(data, {
        type: 'bar',
        width: '42px',
        height: '28px',
        barWidth: barnum,
        barColor: "#1ab394",
        negBarColor: "#c6c6c6"
    });
    $('#sparkline').bind('sparklineRegionChange', function (ev) {

        var sparkline = ev.sparklines[0];
        var region = sparkline.getCurrentRegionFields();
        var value = region[0].value;
        var x = region[0].offset;

        $('.mouseoverregion').text("<?php echo L('时间');?>：" + date[x] + " <?php echo L('盈亏');?>：" + value);

    }).bind('mouseleave', function () {
        $('.mouseoverregion').text('');
    });


    $('#sparkline1').sparkline(sdata, {
        type: 'line',
        width: '42px',
        height: '28px',
        lineWidth: 1,
        barColor: "#17997f",
        negBarColor: "#1ab394"
    });
    $('#sparkline1').bind('sparklineRegionChange', function (ev) {
        var sparkline = ev.sparklines[0];
        var region = sparkline.getCurrentRegionFields();

        var value = region.y;
        var x = region.x;
        console.log(region)

        $('.mouseoverregion1').text("<?php echo L('时间');?>：" + sdate[x] + " <?php echo L('浮动盈亏');?>：" + value);

    }).bind('mouseleave', function () {
        $('.mouseoverregion1').text('');
    });


    $(function () {

        var pieresult = '<?php echo escapeJsonString(json_encode($pieresult)); ?>';
        var json_obj = JSON.parse(pieresult);
        var result = json_obj;
        if (result.losemoney.count == null) {
            var losecount = 0;

        } else {
            var losecount = result.losemoney.count;
        }
        if (result.makemoney.count == null) {
            var makecount = 0;

        } else {
            var makecount = result.makemoney.count;
        }

        var losetotal = result.losemoney.total;
        var maketotal = result.makemoney.total;

        var huoli = '<?php echo L('获利笔数');?>';
        var kuisun = '<?php echo L('亏损笔数');?>';
        var t = echarts.init(document.getElementById("e-pie-chart")),

            option = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{b} : {c}<?php echo L('笔');?><br/><?php echo L('占比');?>: ({d}%)",

                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: [huoli, kuisun]
                },
                series: [
                    {
                        type: 'pie',
                        //selectedMode: 'single',
                        radius: ['50%', '75%'],
                        avoidLabelOverlap: false,
                        label: {
                            normal: {
                                formatter: function (obj) {
                                    return obj.value
                                },
                                show: true,
                                position: 'inner',//文字显示位置,如上图中的1.0,1.1字样
                                textStyle: {
                                    fontSize: '14',
                                    fontWeight: 'normal'
                                }
                            },
                            emphasis: {
                                //show: true,
                                position: 'inner',
                                textStyle: {
                                    fontSize: '14',
                                    fontWeight: 'normal'
                                }
                            }
                        },
                        labelLine: {
                            normal: {
                                show: true
                            }
                        },
                        data: [

                            {
                                value: makecount, name: huoli,
                                content: "<?php echo L('获利笔数'); ?>：" + makecount,
                                itemStyle: {normal: {color: '#1ab394'}}
                            },
                            {
                                value: losecount, name: kuisun,
                                content: "<?php echo L('亏损笔数'); ?>：" + losecount,
                                itemStyle: {normal: {color: '#c6c6c6'}}
                            },

                        ]
                    }
                ]
            };


        t.setOption(option),
            window.onresize = t.resize;


    });
</script>
</body>
</html>