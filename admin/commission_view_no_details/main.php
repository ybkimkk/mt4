<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('未返佣记录') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title"><?php echo L('搜索');?></h4>

                                        <div>
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get" autocomplete="off">
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('查询范围');?>：
                                                    <select name='qscope' id='qscope' class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='self'<?php if(_request('qscope') == 'self'){echo ' selected';} ?>><?php echo L('仅自己');?></option>
                                                        <option value='under'<?php if(_request('qscope') == 'under'){echo ' selected';} ?>><?php echo L('下级客户');?></option>
                                                     </select>
                                                </div>
                                            	<div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('客户信息');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('nickname');?>" name="nickname" placeholder="<?php echo L('请输入会员者昵称邮箱'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('login');?>" name="login" placeholder="<?php echo L('请输入交易的 MT 账户')?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('订单编号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('ticket');?>" name="ticket" placeholder="<?php echo L('请输入未返佣的订单号'); ?>">
                                                </div>
                                                <?php
												$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易种类');?>：
                                                    <select name="qtype" id="qtype" class="form-control">
                                                        <option value=""><?php echo L('全部种类');?></option>
                                                        <?php
														foreach($types as $key=>$val){
															echo '<option value="' , $val['id'] , '">' , $val['type_name'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易品种');?>：
                                                    <select name="qsymbol" id="qsymbol" class="form-control">
                                                        <option value=""><?php echo L('全部品种');?></option>                                        
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('开仓时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="open_time_start" value="<?php echo _request('open_time_start');?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="open_time_end" value="<?php echo _request('open_time_end');?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('平仓时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="close_time_start" value="<?php echo _request('close_time_start');?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="close_time_end" value="<?php echo _request('close_time_end');?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                        <div>
                                                        <button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索未返佣'); ?></button>
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



<?php
$isSearch = 0;



$parentid = $DRAdmin['id'];
$memberid = $DRAdmin['id'];
if ($DRAdmin['_dataRange'] >= 2) {
	$parentid = "admin";
	$memberid = "admin";
}

if ($_REQUEST['nickname'] != '') {//输入了下级昵称
	$nickname = urldecode($_REQUEST['nickname']);
	$member = $DB->getDRow("select id from t_member where server_id = '{$DRAdmin['server_id']}' and status = 1 and (nickname = '" . $nickname . "' or email = '" . $nickname . "')");
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
	_check_member_scope($parentid, $member['id']); //检验是否下级
	$memberid = $member['id'];
	
	$isSearch = 1;
}

if ($_REQUEST['qscope'] == 'under') {
	$memberidarray = getunderCustomerIds($memberid);
	
	$isSearch = 1;
} else if ($_REQUEST['qscope'] == 'self') {
	$memberidarray = array($DRAdmin['id']);
	
	$isSearch = 1;
} else {
	$memberidarray = getunderCustomerIds($memberid);
	if ($memberidarray)
		$memberidarray = array_merge($memberidarray, array((int) $memberid));
	else
		$memberidarray = array($DRAdmin['id']);
}

//print_r($memberidarray);
//$arr = $DB->getField2Arr("select id as ids ,id from t_member where id in (" . implode(',',$memberidarray) . ") and server_id = '{$DRAdmin['server_id']}' and status = 1 and (parent_id<>0 or (parent_id=0 and userType='agent') || (userType='member' and level<>0))");
$arr = array();
foreach($memberidarray as $key=>$val){
	$arr[$val] = $val;
}

$account_id_arr = getunderMT4Ids($arr);
if(!$account_id_arr){
	$account_id_arr = array('0');
}
$where['LOGIN'] = array('in', $account_id_arr);
if (isset($_REQUEST['ticket']) && $_REQUEST['ticket'] != '') {
	$where['TICKET'] = $_REQUEST['ticket'];
	
	$isSearch = 1;
}
if ($_REQUEST['login'] != '') {
	$memberlogin = $DB->getDRow("select member_id from t_member_mtlogin where loginid = '" . $_REQUEST['login'] . "' and status = 1");
	$member = $DB->getDRow("select * from t_member where id = '{$memberlogin['member_id']}' and status = 1");
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
	_check_member_scope($parentid, $member['id']); //检验是否下级
	if ($member['parent_id'] != 0 || ($member['parent_id'] == 0 && $member['userType'] == 'agent') || ($member['level'] != 0 && $member['userType'] == 'member')) {
		$where['LOGIN'] = $_REQUEST['login'];
	} else {
		FCreateErrorPage(array(
			'title'=>L("提示"),
			'content'=>L("当前账号无上级且为直客"),
			'btnStr'=>L('返回'),
			'url'=>FPrevUrl(),
			'isSuccess'=>0,
			'autoRedirectTime'=>0,
		));
	}
	
	$isSearch = 1;
}

if (_request('open_time_start') && _request('open_time_end')) {
	$where['OPEN_TIME'] = array(array('EGT', _request('open_time_start')), array('ELT', _request('open_time_end') . ' 23:59:59'));
	
	$isSearch = 1;
} elseif ($_REQUEST['open_time_start']) {
	$where['OPEN_TIME'] = array(EGT, _request('open_time_start'));
	
	$isSearch = 1;
} elseif ($_REQUEST['open_time_end']) {
	$where['OPEN_TIME'] = array(ELT, _request('open_time_end') . ' 23:59:59');
	
	$isSearch = 1;
}

$last_time = C('CALC_LAST_TIME') - 8 * 3600 + $DRAdmin['timezone'] * 3600 - 15 * 60; //最新返佣时间 同步前15分钟
$last_time = date('Y-m-d H:i:s', $last_time);
$where['CLOSE_TIME'] = array(array('GT', '1970-01-01 00:00:00'), array('ELT', $last_time));
if (_request('close_time_start') && _request('close_time_end')) {
	$where['CLOSE_TIME'] = array(array('EGT', _request('close_time_start')), array('ELT', _request('close_time_end') . ' 23:59:59'));
	
	$isSearch = 1;
} elseif ($_REQUEST['close_time_start']) {
	$where['CLOSE_TIME'] = array(EGT, _request('close_time_start'));
	
	$isSearch = 1;
} elseif ($_REQUEST['close_time_end']) {
	$where['CLOSE_TIME'] = array(ELT, _request('close_time_end') . ' 23:59:59');
	
	$isSearch = 1;
}


if($DRAdmin['ver'] == 5){
	if($where['OPEN_TIME']){
		$where['Time'] = $where['OPEN_TIME'];
		unset($where['OPEN_TIME']);
	}
	if($where['CLOSE_TIME']){
		$where['Time'] = $where['CLOSE_TIME'];
		unset($where['CLOSE_TIME']);
	}
}

//交易种类
if (_request('qtype')) {
	if (_request('qsymbol') == '') {
		$symbollist = $DB->getField("select symbol from t_symbol where `type` = '" . _request('qtype') . "'", true);
		if (empty($symbollist)) {
			$symbollist = array('0');
		}
		$where['SYMBOL'] = array('in', "'" . implode("','",$symbollist) . "'");
	} else {
		$where['SYMBOL'] = _request('qsymbol');
	}
	
	$isSearch = 1;
}

if($DRAdmin['ver'] == 5){
	$where['Action'] = array('in', '0,1');
	$where['Entry'] = 1;
	$where['_string'] = " not exists (select * from t_sale_commission b where b.TICKET = " . $DRAdmin['mt4dbname'] . ".mt5_deals.PositionID)";
	
	$whereStr = cz_where_to_str($where);
	$totalAmount = $DB->getField2Arr("select 'vtotal',count(PositionID) as TNUM,sum(Volume) as TVOLUME from " . $DRAdmin['mt4dbname'] . ".mt5_deals {$whereStr}");
}else{
	$where['CMD'] = array('in', '0,1');
	$where['_string'] = " not exists (select * from t_sale_commission b where b.TICKET = " . $DRAdmin['mt4dbname'] . ".mt4_trades.TICKET)";
	
	$whereStr = cz_where_to_str($where);
	$totalAmount = $DB->getField2Arr("select 'vtotal',count(TICKET) as TNUM,sum(VOLUME) as TVOLUME from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$whereStr}");
}

$page     = FGetInt('page');if($page <= 0){$page = 1;}
$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;

if($DRAdmin['ver'] == 5){
	$field = empty($_GET['_field']) ? 'PositionID' : $_GET['_field'];
}else{
	$field = empty($_GET['_field']) ? 'TICKET' : $_GET['_field'];
}
$order = empty($_GET['_order']) ? ' desc' : " {$_GET['_order']}";

$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");

//echo $whereStr;

if(FGetInt('isdownload') > 0){
	$pageSql = '';
}

if ($mtserver['ver'] == 5) {
	$whereStr = cz_where_to_str($where);
	$list = $DB->getDTable("select Symbol as SYMBOL,Time as CLOSE_TIME,Login as LOGIN,Price as CLOSE_PRICE,PositionID as TICKET,Volume as VOLUME,Digits as DIGITS from " . $DRAdmin['mt4dbname'] . ".mt5_deals {$whereStr} order by {$field} {$order} {$pageSql}");
} else {
	$whereStr = cz_where_to_str($where);
	$list = $DB->getDTable("select SYMBOL,CLOSE_TIME,OPEN_TIME,LOGIN,TICKET,VOLUME,OPEN_PRICE,CLOSE_PRICE,DIGITS from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$whereStr} order by {$field} {$order} {$pageSql}");

	//echo "select SYMBOL,CLOSE_TIME,OPEN_TIME,LOGIN,TICKET,VOLUME,OPEN_PRICE,CLOSE_PRICE,DIGITS from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$whereStr} order by {$field} {$order} {$pageSql}";
}


foreach ($list as $key => $val) {
	$memberid = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$val['LOGIN']}' and status = 1");
	$list[$key]['res'] = $DB->getDRow("select email,nickname,id,parent_id from t_member where id = '{$memberid}'");
	$list[$key]['parent'] = $DB->getDRow("select id,email,nickname,level,userType from t_member where id = '" . $list[$key]['res']['parent_id'] . "'");
	
	if ($mtserver['ver'] == 5) {
		$list[$key]['OPEN_PRICE'] = $DB->getField("select Price from " . $DRAdmin['mt4dbname'] . ".mt5_deals where PositionID = '{$val['TICKET']}' and Entry = 0 and Action in (0,1)");
	}
}

$symbols = $DB->getDTable("select * from t_symbol where server_id = '{$DRAdmin['server_id']}'");
$symbols = json_encode($symbols);
$symbols = addslashes($symbols);







if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('未返佣记录'));

	$titleArr = array(
		L('订单'),
		L('账号'),
		L('英文名'),
		L('邮箱'),
		L('交易手数'),
		L('交易种类'),
		L('开仓价格'),
		L('平仓价格'),
		L('平仓时间'),
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
	foreach($list as $key=>$rs){
		$i = 'A';
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['TICKET'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['LOGIN'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['res']['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['res']['email'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['VOLUME']/100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['SYMBOL'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['OPEN_PRICE'],$rs['DIGITS']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['CLOSE_PRICE'],$rs['DIGITS']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['CLOSE_TIME'],PHPExcel_Cell_DataType::TYPE_STRING);

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


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                    
<div>
<?php
	echo '<b>' , L('汇总') , '</b>&nbsp; ';
	//echo L('金额') , '：$' , $totalAmount['vtotal']['TAMOUNT'] , ' &nbsp;&nbsp;';
	echo L('交易量') , '：' , $totalAmount['vtotal']['TVOLUME']/100 , L('手') , '&nbsp;&nbsp;';
	echo L('笔数') , '：' , $totalAmount['vtotal']['TNUM'] , L('条');
?>
</div>
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('订单情况');?></th>
                                                    <th class="no-sort"><?php echo L('所属用户');?></th>
                                                    <th class="no-sort"><?php echo L('上级信息');?></th>
                                                    <th class="no-sort"><?php echo L('交易情况');?></th>
                                                    <th class="no-sort"><?php echo L('价格');?></th>
                                                    <th class="no-sort"><?php echo L('平台时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$recordCount = intval($totalAmount['vtotal']['TNUM']);
		
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
		$cnPager = new CPager($pageConfig);

		if(count($list) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			if($mtserver['ver'] ==  5){
				$count_ = 3;
			}else{
				$count_ = 4;
			}
			
			foreach($list as $key=>$rs){
				echo '<tr>';
				echo '<td>' , L('订单号') , ':' , $rs['TICKET'] , '<br>';
				echo MT , ' ' , L('账号') , '：' , $rs['LOGIN'];
				echo '</td>';
				echo '<td>' , L('英文名') , '：' , $rs['res']['nickname'] , '<br>';
                echo L('邮箱') , '：<span class="lookemail">' , hideStr($rs['res']['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['res']['email'] , '"></i>';
				echo '</td>';
				echo '<td>';
					if(!can_look_parent_info()){
						echo '-';
					}else{
						if($rs['res']['parent_id'] == '0'){
							echo '无';
						}else{
							echo $rs['parent']['level'] , ' ' , L('级') , ' ';
							if($rs['parent']['userType'] == 'agent'){
								echo L('代理');
							}else if($rs['parent']['userType'] == 'direct'){
								echo L('直客');
							}else if($rs['parent']['userType'] == 'member'){
								echo L('员工');
							}
							echo '<br/>';
							echo L('英文名') , '：' , $rs['parent']['nickname'] , '<br/>';
							echo L('邮箱') , '：<span class="lookemail">' , hideStr($rs['parent']['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" val="' , $rs['parent']['id'] , '"></i>';
						}
					}
				echo '</td>';
				echo '<td>' , L('手数') , '：' , $rs['VOLUME']/100 , '<br/>';
				echo L('品种') , '：' , $rs['SYMBOL'];
				echo '</td>';
				echo '<td>';
				echo L('开仓') , '：' , round($rs['OPEN_PRICE'],$rs['DIGITS']);
				echo '<br/>';
				echo L('平仓') , '：' , round($rs['CLOSE_PRICE'],$rs['DIGITS']);
				echo '</td>';
				echo '<td class="center">';
				  if($mtserver['ver']!=5){
						echo L('开仓') , '：' , $rs['OPEN_TIME'];
						echo '<br>';
				  }
				  echo L('平仓') , '：' , $rs['CLOSE_TIME'];
				echo '</td>';
				echo '<td>';
					echo L('订单同步延时未返佣');
				echo '</td>';
				echo '</tr>';
			}
		}
?>
                                            </tbody>
                                        </table>

<?php
echo $cnPager->FGetPageList();
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
        
        
        
        
<script type="text/javascript">	
   	$('#qtype').val('<?php echo _request('qtype');?>');
    //$('#GROUP_NAME').val('{$Think.request.GROUP_NAME}');

	$(document).on("click","#downBtns",function(){
  	//$("#downBtns").click(function(){
        var value=$("#commentForm").serialize();
        document.location.href="{:U('Commission/down_view_no_details',array('act'=>'down'))}?"+value;
    });
  	
	var symbol_json = '<?php echo $symbols; ?>';
	$('#qtype').change(function(){
		var type =  $(this).val();
		changesymbole(type);
	})
	
	
	function changesymbole(type){
		var json_obj = eval('(' + symbol_json + ')');
		$('#qsymbol').empty();
		$("#qsymbol").append("<option value=''><?php echo L('全部品种'); ?></option>"); 
		for(var o in json_obj){ 
			if(json_obj[o].type == type){
				$("#qsymbol").append("<option value='"+json_obj[o].symbol+"'>"+json_obj[o].symbol+"</option>"); 
			}
			//console.log(json_obj[o]);
		}
	}
	changesymbole('<?php echo _request('qtype');?>');
	$("#qsymbol").val('<?php echo _request('qsymbol');?>');
	
	init_findinfo();
  	
</script>
        
        

        
        
        
        
    </body>
</html>
