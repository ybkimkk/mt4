<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_positionsModel.class.php');

//-----------------------------------------

$isSearch = 1;

$username = _request('username');
//输入账号条件
$memberid = $DRAdmin['id'];
if ($username) {
	$member = $DB->getDRow("select id from t_member where server_id = '{$DRAdmin['server_id']}' and status = 1 and (nickname = '" . $username . "' or email = '" . $username . "'  or chineseName='" . $username . "')");
	if (!$member) {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("当前账号不存在"),
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
	$member = $DB->getDRow("select id,nickname,email from t_member where id = '{$memberlogin['member_id']}' and status = 1");
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
	$_REQUEST['username'] = $member['email'];
	$memberid = $memberlogin['member_id'];
}
if (_request('qtype')) {
	$symbollist = $DB->getField("select symbol from t_symbol where `type` = '" . _request('qtype') . "'", true);
	if (empty($symbollist)) {
		$symbollist = array('0');
	}
}

if ($_GET['GROUP_NAME']) {
	$group = $_GET['GROUP_NAME'];
}

	//非管理员，只能看到自己和伞下
	if($DRAdmin['_dataRange'] <= 1){
		$parentid = $DRAdmin['id'];
	}else{
		$parentid = 'admin';
	}

$member_id_arr = array();
$member_id_arr = getunderCustomerIds($parentid);
if ($memberid != $DRAdmin['id']) {//输入了查询条件账户
	$isin = in_array($memberid, $member_id_arr);
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
	$qusername = $username;
	$member_id_arr = getunderCustomerIds($memberid);
	array_unshift($member_id_arr, $memberid);
} else {
	if ($member_id_arr)
		array_unshift($member_id_arr, $DRAdmin['id']);
	else
		$member_id_arr = array($DRAdmin['id']);
}

$account_id_arr = getunderMT4Ids($member_id_arr);

if ($group) {
	if ($group == 'all_group') {
		$arr_group = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'", true);
		if(!$arr_group){
			$arr_group = array('0');
		}
		if(!is_array($arr_group)){
			$arr_group = array($arr_group);
		}
		$userswhere = " and `GROUP` in ('" . implode("','",$arr_group) . "')";
	} else {
		if(!is_array($group)){
			$group = array($group);
		}
		$userswhere = " and `GROUP` in ('" . implode("','",$group) . "')";
	}
	
	if($DRAdmin['ver'] == 5){
		$users_login_arr = $DB->getField("select LOGIN from " . $DRAdmin['mt4dbname'] . ".mt5_users where `Login` = '{$group['loginid']}' {$userswhere}", true);
	}else{
		$users_login_arr = $DB->getField("select LOGIN from " . $DRAdmin['mt4dbname'] . ".mt4_users where `LOGIN` = '{$group['loginid']}' {$userswhere}", true);
	}
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

//分离时间段，按天查询
$count = 10;

$startday = _request('startday');
$endday = _request('endday');
if ($startday) {
	if(stripos($startday,':') !== false){
		$starttime = strtotime($startday);
	}else{
		$starttime = strtotime($startday . ' 00:00:00');
	}
} else {
	$starttime = strtotime(date('Y-m-d', time() - $count * 24 * 60 * 60)) . " 00:00:00";
	$startday = date('Y-m-d', $starttime) . " 00:00:00";
	//_request('startday', $startday);//这个设置用于下载时取值用
}
if ($endday) {
	if(stripos($startday,':') !== false){
		$endtime = strtotime($endday);
	}else{
		$endtime = strtotime($endday . ' 23:59:59');
	}
} else {
	$endtime = strtotime(date('Y-m-d', time()) . " 23:59:59");
	$endday = date('Y-m-d', $endtime) . " 23:59:59";
	//_request('endday', $endday);
}
$lefttime = $endtime - strtotime(date('Y-m-d', $starttime));
$yourday = (int) ($lefttime / (3600 * 24));
if ($yourday > 180) {
	FCreateErrorPage(array(
		'title'=>L("提示"),
		'content'=>L("查询时间不能大于180天"),
		'btnStr'=>L('返回'),
		'url'=>FPrevUrl(),
		'isSuccess'=>0,
		'autoRedirectTime'=>0,
	));
}

$statictradelist = array();
$mtserver = $DB->getDRow("select ver from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
for ($i = 0; $i <= $yourday; $i++) {
	$starthis = 0;
	$endhis = 0;
	$staticday = date("Y-m-d", $endtime - $i * 24 * 60 * 60);
	if ($i == 0) {
		$starthis = date('H:i:s', $endtime);
	}
	if ($i == $yourday) {
		$endhis = date('H:i:s', $starttime);
	}
	$list = getStaticByDay($staticday, $account_id_arr, $member_id_arr, $symbollist, $mtserver, $starthis, $endhis);
	$statictradelist[$i] = $list;
}


$data = getTotalData($account_id_arr, $member_id_arr, $startday, $endday);
$staticdata = totaltradedata($statictradelist);
if ($DRAdmin['_dataRange'] >= 2) {
	if ($DRAdmin['id'] == $memberid) {
		$parentid = '';
	} else {
		$parentid = $memberid ? $memberid : '';
	}
} else {
	$parentid = $memberid ? $memberid : $DRAdmin['id'];
}

array_unshift($statictradelist, $staticdata);
$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
$groups = $DB->getDTable("select * from t_groups where server_id = '" . $DRAdmin['server_id'] . "'");











if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('统计报表'));
	
	$titleArr = array(
		L('日期'),
		L('客户数'),
		L('交易手数'),
		L('交易笔数'),
		L('总入金'),
		L('总入金') . ' ' . L('笔数'),
		L('总出金'),
		L('总出金') . ' ' . L('笔数'),
		L('出入金差额'),
		L('总盈亏'),
		L('点差'),
		L('总返佣'),
	);
	$n = 0;
	for ($i = 'A'; $i != 'Y'; $i++) {
		if($n >= count($titleArr)){
			continue;
		}
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($i . '1', $titleArr[$n]);
		$n++;
	}
	
	$j = 2;
	foreach($statictradelist as $key=>$rs){
		$i = 'A';
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['dayweek'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['totalmember'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['totalvolumes']/100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['totalCount'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['totalInBalance'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['totalInbalanceCount'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['totalOutBalance'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['totalOutbalanceCount'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['equityBalance'],2),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['totalProfit'],2),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['spreadProfitCount'],2),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['commission_banalce'],2),PHPExcel_Cell_DataType::TYPE_STRING);
		
		$j++;
	}

	$saveFilename = date('Ymd-His-') . FRndStr(6) . '.xls';
	$uploadFolder = 'excel/' . date('Y/m/d/');
	if(!is_dir($uploadFolder)){
		mkdir($uploadFolder,0777,true);
	}
	$saveFilenameAbs = $uploadFolder . $saveFilename;

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');//Excel5为xls格式，Excel2007为xlsx格式
	$objWriter->save($saveFilenameAbs);
	
	echo '<script type="text/javascript">window.parent.layer.closeAll();window.location.href="' , $saveFilenameAbs , '"</script>';

	exit;
}
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('统计报表') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        
                        
                        
                        <div class="row">
                            <div class="col-lg-2">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <h5 class="text-muted font-weight-normal mt-0"><?php echo L("总客户数");?></h5>
                                        <h3 class="mt-3"><?php echo $data['membernum'];?></h3>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->

                            <div class="col-lg-2">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <h5 class="text-muted font-weight-normal mt-0"><?php echo L("总MT账户数");?></h5>
                                        <h3 class="mt-3"><?php echo $data['mt4num'];?></h3>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                            
                            <div class="col-lg-2">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <h5 class="text-muted font-weight-normal mt-0"><?php echo L("未平仓手数") , ' / ' , L("笔数");?></h5>
                                        <h3 class="mt-3"><?php echo $data['unclosevolume'] / 100 , ' / ' , $data['unclosecount'];?></h3>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                            
                            <div class="col-lg-2">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <h5 class="text-muted font-weight-normal mt-0"><?php echo L("未平仓") , '（USD）';?></h5>
                                        <h3 class="mt-3"><?php echo $data['uncloseamount'];?></h3>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                            
                            <div class="col-lg-2">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <h5 class="text-muted font-weight-normal mt-0"><?php echo L("总净值") , '（USD）';?></h5>
                                        <h3 class="mt-3"><?php echo $data['equity'];?></h3>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                            
                            <div class="col-lg-2">
                                <div class="card widget-flat">
                                    <div class="card-body">
                                        <h5 class="text-muted font-weight-normal mt-0"><?php echo L("总余额") , '（USD）';?></h5>
                                        <h3 class="mt-3"><?php echo $data['balance'];?></h3>
                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                            
                        </div>
                        <!-- end row -->
                        
                        

                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title"><?php echo L('搜索');?></h4>

                                        <div>
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get" autocomplete="off">
                                            	<div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('用户信息');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('username');?>" name="username" placeholder="<?php echo L('用户英文名、邮箱'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	MT <?php echo L('账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('loginid');?>" name="loginid" placeholder="MT <?php echo L('账号'); ?>">
                                                </div>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易种类');?>：
                                                    <select name="qtype" id="qtype" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														foreach($types as $key=>$val){
															echo '<option value="' , $val['id'] , '"' , _request('qtype') == $val['id'] ? ' selected' : '' , '>' , $val['type_name'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="startday" value="<?php echo $startday;?>" placeholder="<?php echo L('查询开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="endday" value="<?php echo $endday;?>" placeholder="<?php echo L('查询结束日期');?>">
                                                    </div>
                                                </div>
                                            	<?php
												$groupsList = $DB->getDTable("select * from t_groups where server_id = {$DRAdmin['server_id']}");
                                                if(count($groupsList)){
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:120px;">
                                                    <select data-placeholder="<?php echo L('请选择分组');?>" name="GROUP_NAME[]" class="chosen-select" multiple>
                                                        <option value="all_group"><?php echo L('全部');?></option>
                                                        <?php
														foreach($groupsList as $key=>$val){
															echo '<option value="' , $val['group'] , '" hassubinfo="true"';
															if(in_array($val['group'],$_GET['GROUP_NAME'])){
																echo ' selected';
															}
															echo '>' , $val['group'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <?php
												}
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<div class="custom-control custom-checkbox">
                                                        <input type="checkbox"<?php if($_GET['reject'] == 1){echo ' checked';} ?> class="custom-control-input" name="reject" id="reject" value="1"><label class="custom-control-label" for="reject"><?php echo L('剔除组');?></label>
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('剔除MT账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $_GET['T_LOGIN'];?>" name="T_LOGIN" placeholder="<?php echo L('多个MT帐号请用英文逗号隔开'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                        <div>
                                                        <button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索'); ?></button>
														<?php
															if(chk_in_access('报表导出')){
																echo '<a class="btn btn-primary" href="#nolink" onclick="go_download()">' , L('报表导出') , '</a> ';
                                                            }
                                                        ?>
                                                    </div>                                                                             
                                                </div>
                                            </form>
                                        </div> <!-- end row -->

                                    </div> <!-- end card-body-->
                                </div> <!-- end card-->
                            </div> <!-- end col-->
                        </div> 
                        <!-- end row-->



                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('日期');?></th>
                                                    <th class="no-sort"><?php echo L('客户数');?></th>
                                                    <th class="no-sort"><?php echo L('交易手数') , '/' , L('笔数');?></th>
                                                    <th class="no-sort"><?php echo L('总入金') , '/' , L('笔数');?></th>
                                                    <th class="no-sort"><?php echo L('总出金') , '/' , L('笔数');?></th>
                                                    <th class="no-sort"><?php echo L('出入金差额');?></th>
                                                    <th class="no-sort"><?php echo L('总盈亏');?></th>
                                                    <th class="no-sort"><?php echo L('点差');?></th>
                                                    <th class="no-sort"><?php echo L('总返佣');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		/*$recordCount = intval($count);
		
		$page = FGetInt('page');
		$pagersize = 20;
		$pageConfig = array(
			'recordCount'=>$recordCount,
			'pagesize'=>$pagersize,
			'pageCurrIndex'=>$page,
			'pageMainLinks'=>5,
			'tplRecordCount'=>L('_RECORDS_条记录，第_PAGE_/_PAGES_页'),
			'showRecordCount'=>true,
			'showPrevPage'=>true,
			'showNextPage'=>true,
		);
		$cnPager = new CPager($pageConfig);*/

		if(count($statictradelist) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			foreach($statictradelist as $key=>$rs){
				if($key == 0){
					echo '<tr>';
					echo '<td>' , $rs['dayweek'] , '</td>';
					echo '<td>';
						if($rs['totalmember'] > 0){
							//<a href="{:U('/Member/index',array('start'=>$startday,'end'=>$endday,'username'=>$qusername,'qtype'=>$_REQUEST['qtype'],'parentid'=>$parentid))}">
							echo $rs['totalmember'] , L('个');
							//</a>
						}else{
							echo $rs['totalmember'] , L('个');
						}
					echo '</td>';
					echo '<td>';
						if($rs['totalvolumes'] > 0){
							if($mtserver['ver']==5){
								//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo $rs['totalvolumes']/100 , L('手') , ' / ' , $rs['totalCount'] , L('笔');
							   //</a>
							}else{
								//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo $rs['totalvolumes']/100 , L('手') , ' / ' , $rs['totalCount'] , L('笔');
							   //</a>
							}
						}else{
							echo $rs['totalvolumes']/100 , L('手') , ' - ' , $rs['totalCount'] , L('笔');
						}
					echo '</td>';
					echo '<td>';
							if($rs['totalInBalance'] > 0){
								if($mtserver['ver']==5){
									//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'6','PROFIT_s'=>'0.01','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'1','username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalInBalance'] , ' / ' , $rs['totalInbalanceCount'] , L('笔');
									//</a>
								}else{
									//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'6','PROFIT_s'=>'0.01','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'1','username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalInBalance'] , ' / ' , $rs['totalInbalanceCount'] , L('笔');
									//</a>
								}
							}else{
								echo '$' , $rs['totalInBalance'] , ' - ' , $rs['totalInbalanceCount'] , L('笔');
							}
					echo '</td>';				
					echo '<td>';
							if($rs['totalOutBalance'] != 0){
								if($mtserver['ver']==5){
									//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'6','PROFIT_e'=>'-0.01','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'-1','username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalOutBalance'] , ' / ' , $rs['totalOutbalanceCount'] , L('笔');
									//</a>
								}else{
									//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'6','PROFIT_e'=>'-0.01','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'-1','username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalOutBalance'] , ' / ' , $rs['totalOutbalanceCount'] , L('笔');
									//</a>
								}
							}else{
								echo '$' , $rs['totalOutBalance'] , ' - ' , $rs['totalOutbalanceCount'] , L('笔');
							}
					echo '</td>';
					echo '<td>' , round($rs['equityBalance'],2) , '</td>';
					echo '<td>';
						if($rs['totalProfit'] != 0){
							if($mtserver['ver']==5){
								//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo '$' , round($rs['totalProfit'],2);
								//</a>
							}else{
								//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo '$' , round($rs['totalProfit'],2);
								//</a>
							}
						}else{
						   echo '$' , round($rs['totalProfit'],2);
						}
					echo '</td>';
					echo '<td>';
						//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$startday,'CLOSE_TIME_e'=>$endday,'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
						echo '$' , round($rs['spreadProfitCount'],2);
						//</a>
					echo '</td>';
					echo '<td>';
						if($rs['commission_banalce'] != 0){
							//<a href="{:U('/Commission/view_details',array('time_start'=>$startday,'time_end'=>$endday,'qtype'=>$_REQUEST['qtype'],'nickname'=>$_REQUEST['username'],'LOGIN'=>$_REQUEST['loginid']))}">
							echo '$' , round($rs['commission_banalce'],2);
							//</a>
						}else{
							echo '$' , round($rs['commission_banalce'],2);
						}
					echo '</td>';
	
					echo '</tr>';
				}else{
					echo '<tr>';
					echo '<td>' , $rs['dayweek'] , '</td>';
					echo '<td>';
						if($rs['totalmember'] > 0){
							//<a href="{:U('/Member/index',array('start'=>$rs['day'].$rs['starthis'],'end'=>$rs['day'].$rs['endhis'],'username'=>$qusername,'qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'parentid'=>$parentid))}">
							echo $rs['totalmember'] , L('个');
							//</a>
						}else{
							echo $rs['totalmember'] , L('个');
						}
					echo '</td>';
					echo '<td>';
						if($rs['totalvolumes'] > 0){
							if($mtserver['ver']==5){
								//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo $rs['totalvolumes']/100 , L('手') , ' / ' , $rs['totalCount'] , L('笔');
							   //</a>
							}else{
								//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo $rs['totalvolumes']/100 , L('手') , ' / ' , $rs['totalCount'] , L('笔');
							   //</a>
							}
						}else{
							echo $rs['totalvolumes']/100 , L('手') , ' - ' , $rs['totalCount'] , L('笔');
						}
					echo '</td>';
					echo '<td>';
							if($rs['totalInBalance'] > 0){
								if($mtserver['ver']==5){
									//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'6','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'1','username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalInBalance'] , ' / ' , $rs['totalInbalanceCount'] , L('笔');
									//</a>
								}else{
									//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'6' ,'PROFIT_s'=>'0.01','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'1','username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalInBalance'] , ' / ' , $rs['totalInbalanceCount'] , L('笔');
									//</a>
								}
							}else{
								echo '$' , $rs['totalInBalance'] , ' - ' , $rs['totalInbalanceCount'] , L('笔');
							}
					echo '</td>';				
					echo '<td>';
							if($rs['totalOutBalance'] != 0){
								if($mtserver['ver']==5){
									//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'6','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'-1','username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalOutBalance'] , ' / ' , $rs['totalOutbalanceCount'] , L('笔');
									//</a>
								}else{
									//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'6','PROFIT_e'=>'-0.01','qtype'=>$_REQUEST['qtype'],'BALANCE'=>'-1','username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
									echo '$' , $rs['totalOutBalance'] , ' / ' , $rs['totalOutbalanceCount'] , L('笔');
									//</a>
								}
							}else{
								echo '$' , $rs['totalOutBalance'] , ' - ' , $rs['totalOutbalanceCount'] , L('笔');
							}
					echo '</td>';
					echo '<td>' , round($rs['equityBalance'],2) , '</td>';
					echo '<td>';
						if($rs['totalProfit'] != 0){
							if($mtserver['ver']==5){
								//<a href="{:U('/Report/history_trade/report_type/1',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>urlencode($_REQUEST['GROUP_NAME']),'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo '$' , round($rs['totalProfit'],2);
								//</a>
							}else{
								//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
								echo '$' , round($rs['totalProfit'],2);
								//</a>
							}
						}else{
						   echo '$' , round($rs['totalProfit'],2);
						}
					echo '</td>';
					echo '<td>';
						//<a href="{:U('/Report/history_trade',array('LOGIN'=>$rs['LOGIN'],'CLOSE_TIME_s'=>$rs['day'].$rs['starthis'],'CLOSE_TIME_e'=>$rs['day'].$rs['endhis'],'CMD'=>'0,1','qtype'=>$_REQUEST['qtype'],'username'=>$_REQUEST['username'],'GROUP_NAME'=>$_REQUEST['GROUP_NAME'],'T_LOGIN'=>$_REQUEST['T_LOGIN'],'reject'=>$_REQUEST['reject'],'searchuser'=>'all'))}">
						echo '$' , round($rs['spreadProfitCount'],2);
						//</a>
					echo '</td>';
					echo '<td>';
						if($rs['commission_banalce'] != 0){
							//<a href="{:U('/Commission/view_details',array('time_start'=>$rs['day'].$rs['starthis'],'time_end'=>$rs['day'].$rs['endhis'],'qtype'=>$_REQUEST['qtype'],'nickname'=>$_REQUEST['username'],'LOGIN'=>$_REQUEST['loginid']))}">
							echo '$' , round($rs['commission_banalce'],2);
							//</a>
						}else{
							echo '$' , round($rs['commission_banalce'],2);
						}
					echo '</td>';
	
					echo '</tr>';
				}
			}
		}
?>
                                            </tbody>
                                        </table>

<?php
//echo $cnPager->FGetPageList();
?>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                    </div> <!-- container -->
                    
                 
                 
<script>
lang_go_download_isSearch = '<?php echo $isSearch;?>';
</script>
                 
                    


		<?php
        require_once('footer.php');
        ?>

        <!-- third party js -->
        <script src="/assets/js/vendor/jquery.dataTables.min.js"></script>
        <script src="/assets/js/vendor/dataTables.bootstrap4.js"></script>
        <script src="/assets/js/vendor/dataTables.responsive.min.js"></script>
        <script src="/assets/js/vendor/responsive.bootstrap4.min.js"></script>
        <script src="/assets/js/vendor/dataTables.buttons.min.js"></script>
        <script src="/assets/js/vendor/buttons.bootstrap4.min.js"></script>
        <script src="/assets/js/vendor/buttons.html5.min.js"></script>
        <script src="/assets/js/vendor/buttons.flash.min.js"></script>
        <script src="/assets/js/vendor/buttons.print.min.js"></script>
        <script src="/assets/js/vendor/dataTables.keyTable.min.js"></script>
        <script src="/assets/js/vendor/dataTables.select.min.js"></script>
        <!-- third party js ends -->
        
        <script src="/assets/js/layer/layer.js"></script>

		<script src="/assets/js/datapicker/js/bootstrap-datepicker.min.js"></script>
        <link href="/assets/js/datapicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <?php
        if($CurrLangName == 'zh-cn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.zh-CN.min.js"></script>';
			$DatepickerLangName = 'zh-CN';
		}else if($CurrLangName == 'zh-vn'){
			echo '<script src="/assets/js/datapicker/locales/bootstrap-datepicker.vi.min.js"></script>';
			$DatepickerLangName = 'vi';
		}
		?>
        <script>
			$("#commentForm .layer-date").datepicker({
				<?php
				if(strlen($DatepickerLangName)){
					echo 'language: "' , $DatepickerLangName , '",';
				}
				?>
				keyboardNavigation: !1,
				forceParse: 1,
				autoclose: !0,
				clearBtn: !0,
				format: 'yyyy-mm-dd'
			});
		</script>

        <script>
			$(document).ready(function() {
				"use strict";

				$("#basic-datatable").DataTable({
					paging:false,//是否允许表格分页
					info:false,//控制是否显示表格左下角的信息
					lengthChange: false,//是否允许用户改变表格每页显示的记录数
					searching: false,//是否允许Datatables开启本地搜索
					ordering: false,//是否允许Datatables开启排序
					aoColumnDefs: [{ 
						bSortable: false, 
						aTargets: ["no-sort"] 
					}]
				});

			});
        </script>

        <link href="/assets/js/chosen/chosen.css" rel="stylesheet"/>
        <script src="/assets/js/chosen/chosen.jquery.js"></script>   
        <script>
			$(".chosen-select").chosen( {width: "100%"});
        </script>
        
    </body>
</html>
