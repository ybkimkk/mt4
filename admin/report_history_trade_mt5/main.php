<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_buyModel.class.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/Mt5_positionsModel.class.php');



$username = FRequestStr('username');
$datascope = FRequestStr('datascope');
$Order = FRequestStr('Order');
$LOGIN = FRequestStr('LOGIN');
$TICKET = FRequestStr('TICKET');
$PROFIT_s = FRequestStr('PROFIT_s');
$PROFIT_e = FRequestStr('PROFIT_e');
$qtype = FRequestStr('qtype');
$closetime = FRequestStr('closetime');
$OPEN_TIME_s = FRequestStr('OPEN_TIME_s');
$OPEN_TIME_e = FRequestStr('OPEN_TIME_e');if(strlen($OPEN_TIME_s) && strlen($OPEN_TIME_e) && $OPEN_TIME_e < $OPEN_TIME_s){$temp_ = $OPEN_TIME_e;$OPEN_TIME_e = $OPEN_TIME_s;$OPEN_TIME_s = $temp_;}
$CLOSE_TIME_s = FRequestStr('CLOSE_TIME_s');
$CLOSE_TIME_e = FRequestStr('CLOSE_TIME_e');if(strlen($CLOSE_TIME_s) && strlen($CLOSE_TIME_e) && $CLOSE_TIME_e < $CLOSE_TIME_s){$temp_ = $CLOSE_TIME_e;$CLOSE_TIME_e = $CLOSE_TIME_s;$CLOSE_TIME_s = $temp_;}
$comm_type = FRequestStr('comm_type');
$trades_type = FRequestStr('trades_type');
$GROUP_NAME = $_GET['GROUP_NAME'];if(!is_array($GROUP_NAME)){$GROUP_NAME = array();}
$reject = FRequestStr('reject');

$report_type = FRequestStr('report_type');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('交易记录') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 
                        
                        
                        <div class="row">
                            <div class="col-12">
                                <ul class="nav nav-tabs">
                                   <li class="nav-item"><a href="?" aria-expanded="true" class="nav-link<?php if($report_type == '' || $report_type == 0){echo ' active';}?>"><?php echo L('订单记录');?></a></li>
                                   <li class="nav-item"><a href="?report_type=1" aria-expanded="false" class="nav-link<?php if($report_type == '1'){echo ' active';}?>"><?php echo L('交易记录');?></a></li>
                                   <li class="nav-item"><a href="?report_type=2" aria-expanded="false" class="nav-link<?php if($report_type == '2'){echo ' active';}?>"><?php echo L('持仓记录');?></a></li>
                                </ul>
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title"><?php echo L('搜索');?></h4>

                                        <div>
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get" autocomplete="off">
                                            	<input type='hidden' name='report_type' value="<?php echo $report_type;?>" />
                                            	<div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('客户信息');?>：
                                                    <input type="text" class="form-control" value="<?php echo $username;?>" name="username" placeholder="<?php echo L('会员邮箱'); ?>">
                                                </div>
                                                <?php
                                                if($report_type==1){
												?>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('数据范围');?>：
                                                    <select name='datascope' class='form-control'>
                                                        <option value='my'<?php if($datascope == 'my'){echo ' selected';} ?>><?php echo L('本人');?></option>
                                                        <option value='next'<?php if($datascope == 'next'){echo ' selected';} ?>><?php echo L('直接下级');?></option>
                                                        <option value='nextall'<?php if($datascope == 'nextall'){echo ' selected';} ?>><?php echo L('所有下级');?></option>
                                                        <option value='all'<?php if($datascope == 'all'){echo ' selected';} ?>><?php echo L('下级') . '+' . L('本人');?></option>
                                                     </select>
                                                </div>
                                                <?php
												}
												?>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('成交号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $Order;?>" name="Order">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('MT账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $LOGIN;?>" name="LOGIN">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('订单编号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $TICKET;?>" name="TICKET">
                                                </div>
                                                <?php
												$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
												if($report_type==1 || $report_type ==2){
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('交易种类');?>：
                                                    <select name="qtype" id="qtype" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														foreach($types as $key=>$val){
															echo '<option value="' , $val['id'] , '">' , $val['type_name'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('订单盈亏');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control" name="PROFIT_s" value="<?php echo $PROFIT_s;?>" placeholder="<?php echo L('最低价格');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control" name="PROFIT_e" value="<?php echo $PROFIT_e;?>" placeholder="<?php echo L('最高价格');?>">
                                                    </div>
                                                </div>
                                                <?php
												}
												if($report_type==1){
												?>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('返佣类型');?>：
                                                    <select name='comm_type' class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='1'<?php if($comm_type == '1'){echo ' selected';}?>>CRM<?php echo L('返佣');?></option>
                              							<option value='2'<?php if($comm_type == '2'){echo ' selected';}?>>MT<?php echo L('返佣');?></option>
                                                     </select>
                                                </div>
                                                <?php
												}
												if($report_type==1){
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="CLOSE_TIME_s" value="<?php echo $CLOSE_TIME_s;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="CLOSE_TIME_e" value="<?php echo $CLOSE_TIME_e;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('交易指令');?>：
                                                    <select name='CMD' id="CMD" class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='0'>BUY</option>
                                                        <option value='1'>SELL</option>
                                                        <option value='2'>BONUS</option>
                                                        <option value='3'>COMMISSION</option>
                                                        <option value='4'>CHARGE</option>
                                                        <option value='5'>CORRECTION</option>
                                                        <option value='6'>BALANCE</option>
                                                        <option value='7'>CREDIT</option>
                                                        <option value='8'>COMMISSION_DAILY</option>
                                                        <option value='9'>COMMISSION_MONTHLY</option>
                                                        <option value='10'>AGENT_DAILY</option>
                                                        <option value='11'>AGENT_MONTHLY</option>
                                                        <option value='12'>INTERESTRATE</option>
                                                        <option value='13'>BUY_CANCELED</option>
                                                        <option value='14'>SELL_CANCELED</option>
                                                        <option value='15'>DIVIDEND</option>
                                                        <option value='16'>DIVIDEND_FRANKED</option>
                                                        <option value='17'>TAX</option>
                                                     </select>
                                                </div>
                                                <?php
												}
												if($report_type==1){
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('开仓价格');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control" name="price_s" value="<?php echo $price_s;?>" placeholder="<?php echo L('最低价格');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control" name="price_e" value="<?php echo $price_e;?>" placeholder="<?php echo L('最高价格');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('趋势');?>：
                                                    <select name='Entry' id="Entry" class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='0'>IN</option>
                                                        <option value='1'>OUT</option>
                                                        <option value='2'>INOUT</option>
                                                        <option value='3'>OUY_BY</option>
                                                     </select>
                                                </div>
                                                <?php
												}
												if($report_type==0 || $report_type==''){
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="orderTime_s" value="<?php echo $orderTime_s;?>" placeholder="">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="orderTime_e" value="<?php echo $orderTime_e;?>" placeholder="">
                                                    </div>
                                                </div>
                                                <?php
												}
												?>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('订单种类');?>：
                                                    <select name='order_type' id="order_type" class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='0'>BUY</option>
                                                        <option value='1'>SELL</option>
                                                        <option value='2'>BUY_LIMIL</option>
                                                        <option value='3'>SELL_LIMIL</option>
                                                        <option value='4'>BUY_STOP</option>
                                                        <option value='5'>SELL_STOP</option>
                                                        <option value='6'>BUY_STOP_LIMIT</option>
                                                        <option value='7'>SELL_STOP_LIMIT</option>
                                                        <option value='8'>CLOSE_BY</option>
                                                     </select>
                                                </div>
                                                <?php
                                                if($report_type==2){
												?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('持仓时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="chi_time_s" value="<?php echo $chi_time_s;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="chi_time_e" value="<?php echo $chi_time_e;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('更新时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="geng_time_s" value="<?php echo $geng_time_s;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="geng_time_e" value="<?php echo $geng_time_e;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
												<?php
												}
												?>
                                                
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
															if(in_array($val['group'],$GROUP_NAME)){
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
                                                        <input type="checkbox"<?php if($reject == 1){echo ' checked';} ?> class="custom-control-input" name="reject" id="reject" value="1"><label class="custom-control-label" for="reject"><?php echo L('剔除组');?></label>
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('剔除MT账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $T_LOGIN;?>" name="T_LOGIN" placeholder="<?php echo L('多个MT帐号请用英文逗号隔开'); ?>">
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



<?php
$isSearch = 0;

            if ($report_type == '0' || !$report_type) {
				$page     = !empty($page) ? $page : 1;
				$listRows            = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
				$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;
				
				if(FGetInt('isdownload') > 0){
					$pageSql = '';
				}
				
				if (_request('Order')){
					$map['Order'] = _request('Order'); //订单号查询
					
					$isSearch = 1;
				}
				if (_request('LOGIN') && _request('searchuser') != 'all'){
					//账号查询
					$map['Login'] = _request('LOGIN');
					
					$isSearch = 1;
				}
				if (_request('orderTime_s') && _request('orderTime_e')) {  //订单表时间查询
					$map['TimeSetup'] = array(array('egt', strtotime(_request('orderTime_s'))), array('elt', strtotime(_request('orderTime_e'))));
					
					$isSearch = 1;
				} elseif ($_REQUEST['orderTime_s']) {
					$map['TimeSetup'] = array('egt', strtotime(_request('orderTime_s')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['orderTime_e']) {
					$map['TimeSetup'] = array('elt', strtotime(_request('orderTime_e')));
					
					$isSearch = 1;
				}
				if (_request("order_type") != ''){
					$map['Type'] = array('in', _request('order_type')); //订单类型查询
					
					$isSearch = 1;
				}
			
				$account_id_arr = getid_arr();
			
				//if($account_id_arr){
				if (_request('LOGIN') == '') {//特殊条件查询
					$map['Login'] = array("in", $account_id_arr['loginID']);
				}
				$whereSqlStr = cz_where_to_str($map);
				$count_order = $DB->getField("select count(*) as count1 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_orders " . $whereSqlStr);
			
				$list_order = $DB->getDTable("select * from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_orders " . $whereSqlStr . " order by `Order` desc " . $pageSql);
				
				
				
				
				
				
if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('交易记录'));

	$titleArr = array(
		L('设置时间'),
		L('账号'),
		L('姓名'),
		L('上级信息'),
		L('成交号'),
		L('交易品种'),
		L('交易量'),
		L('价位'),
		L('止损'),
		L('止盈'),
		L('状态'),
		L('注释'),
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
	foreach($list_order as $key=>$rs){
		$i = 'A';

		$member_id = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$rs['Login']}' and status = 1 and mtserver = '" . $DRAdmin['serverid'] . "'");

		$member = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member_id}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");

		$parent = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member['parent_id']}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
		$rs['nickname'] = $member['nickname'];
		$rs['parent_name'] = $parent['nickname'];

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['TimeSetup'],PHPExcel_Cell_DataType::TYPE_STRING);

		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Login'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
		if(!can_look_parent_info()){
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
		}else{
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['parent_name'],PHPExcel_Cell_DataType::TYPE_STRING);
		}
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Order'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Symbol'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['VolumeInitial'] / 100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['PriceOrder'],$rs['Digits']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['PriceSL'],$rs['Digits']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['PriceTP'],$rs['Digits']),PHPExcel_Cell_DataType::TYPE_STRING);
		
		$State = '';
		switch($rs['State']){
			case '6':
				$State = 'EXPIRED';
				break;
			case '0':
				$State = 'STARTED';
				break;
			case '1':
				$State = 'PLACED';
				break;
			case '2':
				$State = 'CANCELED';
				break;
			case '3':
				$State = 'PARTIAL';
				break;
			case '4':
				$State = 'FILLED';
				break;
			case '5':
				$State = 'REJECTED';
				break;
			case '7':
				$State = 'REQUEST_ADD';
				break;
			case '8':
				$State = 'REQUEST_MODIFY';
				break;
			case '9':
				$State = 'REQUEST_CANCEL';
				break;
		}
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $State,PHPExcel_Cell_DataType::TYPE_STRING);
		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Comment'],PHPExcel_Cell_DataType::TYPE_STRING);

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
                                <h5><?php echo L('订单记录');?> </h5>
                                <div>
                                <?php
                                echo L('订单总数') , '：' , $count_order;
                                ?> 
                                </div>
                                <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                    <thead>
                                        <tr>
                                            <th><?php echo L('设置时间'); ?></th>
                                            <th><?php echo L('账号'); ?></th>
                                            <th><?php echo L('姓名'); ?></th>
                                            <th><?php echo L('上级信息'); ?></th>
                                            <th><?php echo L('成交号'); ?></th>
                                            <th><?php echo L('交易品种'); ?></th>
                                            <th><?php echo L('交易量'); ?></th>
                                            <th><?php echo L('价位'); ?></th>
                                            <th>S/L(<?php echo L('止损'); ?>)</th>
                                            <th>T/P(<?php echo L('止盈'); ?>)</th>
                                            <th><?php echo L('状态'); ?></th>
                                            <th><?php echo L('注释'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
                                    $recordCount = intval($count_order);
                                    
                                    $page = FGetInt('page');
                                    $pagersize = $listRows;
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
                                    
                                    if(count($list_order) <= 0){
                                        echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
                                    }else{
                                        foreach($list_order as $key=>$vo){
											
											$member_id = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$vo['Login']}' and status = 1 and mtserver = '" . $DRAdmin['serverid'] . "'");
									
											$member = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member_id}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
									
											$parent = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member['parent_id']}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
											$vo['nickname'] = $member['nickname'];
											$vo['parent_name'] = $parent['nickname'];
											
											
											
                                            echo '<tr>';
                                    
                                            echo '<td>' , $vo['TimeSetup'] , '</td>';
                                            echo '<td>' , $vo['Login'] , '</td>';
                                            echo '<td>' , $vo['nickname'] , '</td>';
											if(!can_look_parent_info()){
												echo '<td>-</td>';
											}else{
												echo '<td>' , $vo['parent_name'] , '</td>';
											}
                                            echo '<td>' , $vo['Order'] , '</td>';
                                            echo '<td>' , $vo['Symbol'] , '</td>';
                                            echo '<td>' , $vo['VolumeInitial']/100 , '</td>';
                                            echo '<td>' , round($vo['PriceOrder'],$vo['Digits']) , '</td>';
                                            echo '<td>' , round($vo['PriceSL'],$vo['Digits']) , '</td>';
                                            echo '<td>' , round($vo['PriceTP'],$vo['Digits']) , '</td>';
                                            echo '<td>';
                                            switch($vo['State']){
                                                case '6':
                                                    echo 'EXPIRED';
                                                    break;
                                                case '0':
                                                    echo 'STARTED';
                                                    break;
                                                case '1':
                                                    echo 'PLACED';
                                                    break;
                                                case '2':
                                                    echo 'CANCELED';
                                                    break;
                                                case '3':
                                                    echo 'PARTIAL';
                                                    break;
                                                case '4':
                                                    echo 'FILLED';
                                                    break;
                                                case '5':
                                                    echo 'REJECTED';
                                                    break;
                                                case '7':
                                                    echo 'REQUEST_ADD';
                                                    break;
                                                case '8':
                                                    echo 'REQUEST_MODIFY';
                                                    break;
                                                case '9':
                                                    echo 'REQUEST_CANCEL';
                                                    break;
                                            }
                                            echo '</td>';
                                            echo '<td>' , $vo['Comment'] , '</td>';
                                    
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
				<?php
				
            }
			
			
			
			
			
			
			
			
			
			
			
            if ($report_type == '1') {
				$page     = !empty($page) ? $page : 1;
				$listRows            = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
				$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;
				
				if(FGetInt('isdownload') > 0){
					$pageSql = '';
				}
				
				$mt5_buyModel = new Mt5_buyModel($DRAdmin['mt4dbname']);
				if (_request('Order')){
					$may['TICKET'] = _request('Order'); //处理号查询
					
					$isSearch = 1;
				}
				if (_request('CMD') != ''){
					//类型
					$may['CMD'] = array('in', _request('CMD'));
					
					$isSearch = 1;
				}
				if (_request('Entry') != ''){
					//趋势
					$may['Entry'] = _request('Entry');
					
					$isSearch = 1;
				}
				if (_request('CMD') != '' && _request('searchuser') == 'all'){
					//类型
					$may['CMD'] = array('in', _request('CMD'));
					
					$isSearch = 1;
				}
				if (_request('LOGIN') && _request('searchuser') != 'all'){
					//账号查询
					$may['LOGIN'] = _request('LOGIN');
					
					$isSearch = 1;
				}
				if (_request('SYMBOL')){
					$may['SYMBOL'] = _request('SYMBOL');
					
					$isSearch = 1;
				}
				if ($_REQUEST['closetime'] == 1) {
					$may['CLOSE_TIME'] = array('gt', '1970-01-01 00:00:00');
					
					$isSearch = 1;
				} else if ($_REQUEST['closetime'] == 2) {
					$may['CLOSE_TIME'] = array('eq', '1970-01-01 00:00:00');
					
					$isSearch = 1;
				}
			
				if (strstr(_request('CLOSE_TIME_s'), '+')) {
					$stime = str_replace('+', ' ', _request('CLOSE_TIME_s'));
				} else {
					$stime = _request('CLOSE_TIME_s');
			
				}
				if (strstr(_request('CLOSE_TIME_e'), '+')) {
					$etime = str_replace('+', ' ', _request('CLOSE_TIME_e'));
				} else {
					$etime = _request('CLOSE_TIME_e');
				}
			
				if ($stime && $etime) {  //时间查询
					$may['CLOSE_TIME'] = array(array('egt', $stime), array('elt', $etime . ' 23:59:59'));
					
					$isSearch = 1;
				} elseif ($stime) {
					$may['CLOSE_TIME'] = array('egt', $stime);
					
					$isSearch = 1;
				} elseif ($etime) {
					$may['CLOSE_TIME'] = array('elt', $etime . ' 23:59:59');
					
					$isSearch = 1;
				}
				if (_request('PROFIT_s') && _request('PROFIT_e')) { //盈亏查询
					$may['PROFIT'] = array(array('egt', _request('PROFIT_s')), array('elt', _request('PROFIT_e')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['PROFIT_s']) {
					$may['PROFIT'] = array('egt', _request('PROFIT_s'));
					
					$isSearch = 1;
				} elseif ($_REQUEST['PROFIT_e']) {
					$may['PROFIT'] = array('elt', _request('PROFIT_e'));
					
					$isSearch = 1;
				}
			
				if (_request('price_s') && _request('price_e')) { //开仓价格查询
					$may['PricePosition'] = array(array('egt', _request('price_s')), array('elt', _request('price_e')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['price_s']) {
					$may['PricePosition'] = array('egt', _request('PROFIT_s'));
					
					$isSearch = 1;
				} elseif ($_REQUEST['price_e']) {
					$may['PricePosition'] = array('elt', _request('price_e'));
					
					$isSearch = 1;
				}
			
				if (_request('comm_type') == '1') {  //返佣查询
					$may['COMMENT'] = array('like', 'Commission maxib%'); // CRM返佣
					
					$isSearch = 1;
				} else if (_request('comm_type') == '2') {
					$may['COMMENT'] = array('like', 'agent%'); // MT返佣
					
					$isSearch = 1;
				}
				if ($_REQUEST['BALANCE'] == '1') {
					$may['COMMENT'] = array('like', 'Deposit%'); // 入金注释
					
					$isSearch = 1;
				} else if ($_REQUEST['BALANCE'] == '-1') {
					$may['COMMENT'] = array('like', 'Withdraw%'); // 出金注释
					
					$isSearch = 1;
				} else if ($_REQUEST['BALANCE'] == '2') {
					$may['_string'] = "COMMENT like 'Withdraw%' or COMMENT like 'Deposit%'"; // 出金注释
					// $where['PROFIT'] = array('lt','0');
					
					$isSearch = 1;
				}
				if (_request('like') == 1) {
					$may['COMMENT'] = array('like', "agent%"); // MT返佣
					
					$isSearch = 1;
				}
			
				$account_id_arr = getid_arr();
				if (_request('qtype')) {
					$symbolwhere['type'] = _request('qtype');
					$symbollist = D('symbol')->where($symbolwhere)->getField('symbol', true);
					if (empty($symbollist)) {
						$symbollist = array('0');
					}
					
					$isSearch = 1;
				}
				//if($account_id_arr){
				if (_request('LOGIN') == '' || _request('searchuser') == 'all') {//特殊条件查询
					$may['LOGIN'] = array("in", $account_id_arr['loginID']);
				}
				if (!empty($symbollist)) {
					$may['SYMBOL'] = array('in', "'" . implode("','",$symbollist) . "'");
					
					$isSearch = 1;
				}
						
				
				//仅查询交易的手数
				if ($may['CMD'] == "") {//查询全部指令
					$buywhere = array_merge($may, array('Action' => array('in', '0,1')));
					$whereSqlStr = cz_where_to_str($buywhere);
					$volumBuy = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT('PositionID') as TNUM,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(PROFIT+COMMISSION+Storage),2) as PROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(Storage),2) as STORAGE from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr, true);
				} elseif ($may['CMD'] == array('in', 0) || $may['CMD'] == array('in', 1)) {//仅buy sell
					$buywhere = $may;
					$whereSqlStr = cz_where_to_str($buywhere);
					$volumBuy = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT('PositionID') as TNUM,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(PROFIT+COMMISSION+Storage),2) as PROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(Storage),2) as STORAGE from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr, true);
					
					$isSearch = 1;
				} elseif ($may['CMD'] == array('in', '0,1')) {
					$buywhere = $may;
					$whereSqlStr = cz_where_to_str($buywhere);
					$volumBuy = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT('PositionID') as TNUM,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(PROFIT+COMMISSION+Storage),2) as PROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(Storage),2) as STORAGE from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr, true);
					
					$isSearch = 1;
				}
			
				//统计出入金
				if (_request('comm_type') == '') {
					if (_request('BALANCE')) {
						$outwhere = array_merge($may, array('Action' => '2', 'PROFIT' => array('lt', '0')));
						$inwhere = array_merge($may, array('Action' => '2', 'PROFIT' => array('gt', '0')));
						$balwhere = array_merge($may, array('Action' => '2'));
						
						$isSearch = 1;
					} else {
						$balwhere = array_merge($may, array('Action' => '2', 'COMMENT' => array("like", array('Withdraw%', 'Deposit%'), "OR")));
						$outwhere = array_merge($may, array('Action' => '2', 'PROFIT' => array('lt', '0'), 'COMMENT' => array('like', 'Withdraw%')));
						$inwhere = array_merge($may, array('Action' => '2', 'PROFIT' => array('gt', '0'), 'COMMENT' => array('like', 'Deposit%')));
					}
				} else {
					$balwhere = array_merge($may, array('Action' => '2'));
					$outwhere = array_merge($may, array('Action' => '2', 'PROFIT' => array('lt', 0),));
					$inwhere = array_merge($may, array('Action' => '2', 'PROFIT' => array('gt', 0),));
					
					$isSearch = 1;
				}
			
				//m查询出金汇总
				$whereSqlStr = cz_where_to_str($outwhere);
				$outDatas = $DB->getField2Arr("select 'vtotal',round(sum(PROFIT),2) as PROFIT,count(*) count from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr, true);
				//查询入金汇总
				$whereSqlStr = cz_where_to_str($inwhere);
				$inDatas = $DB->getField2Arr("select 'vtotal',round(sum(PROFIT),2) as PROFIT,count(*) count from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr, true);

				$whereSqlStr = cz_where_to_str($may);
				$count_buy = intval($DB->getField("select count(*) as count20210831 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr));
				$list_buy = $DB->getDTable("select * from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr . " order by `PositionID` desc " . $pageSql);





if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('交易记录'));

	$titleArr = array(
		L('平台时间'),
		L('账号'),
		L('姓名'),
		L('上级信息'),
		L('成交号'),
		L('订单'),
		L('交易品种'),
		L('类型'),
		L('趋向'),
		L('交易量'),
		L('价位'),
		L('手续费'),
		L('库存费'),
		L('利润'),
		L('注释'),
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
	foreach($list_buy as $key=>$rs){
		$i = 'A';

		$member_id = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$rs['Login']}' and status = 1 and mtserver = '" . $DRAdmin['serverid'] . "'");

		$member = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member_id}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");

		$parent = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member['parent_id']}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
		$rs['nickname'] = $member['nickname'];
		$rs['parent_name'] = $parent['nickname'];

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Time'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Login'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);

		if(!can_look_parent_info()){
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
		}else{
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['parent_name'],PHPExcel_Cell_DataType::TYPE_STRING);
		}

		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['PositionID'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Order'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Symbol'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$Action = '';
		switch($rs['Action']){
			case '6':
				$Action = 'BALANCE';
				break;
			case '0':
				$Action = 'BUY';
				break;
			case '1':
				$Action = 'SELL';
				break;
			case '2':
				$Action = 'BONUS';
				break;
			case '3':
				$Action = 'COMMISSION';
				break;
			case '4':
				$Action = 'CHARGE';
				break;
			case '5':
				$Action = 'CORRECTION';
				break;
			case '7':
				$Action = 'CREDIT';
				break;
			case '8':
				$Action = 'COMMISSION_DAILY';
				break;
			case '9':
				$Action = 'COMMISSION_MONTHLY';
				break;
			case '10':
				$Action = 'AGENT_DAILY';
				break;
			case '11':
				$Action = 'AGENT_MONTHLY';
				break;
			case '12':
				$Action = 'INTERESTRATE';
				break;
			case '13':
				$Action = 'BUY_CANCELED';
				break;
			case '14':
				$Action = 'SELL_CANCELED';
				break;
			case '15':
				$Action = 'DIVIDEND';
				break;
			case '16':
				$Action = 'DIVIDEND_FRANKED';
				break;
			case '17':
				$Action = 'TAX';
				break; 
		}
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $Action,PHPExcel_Cell_DataType::TYPE_STRING);
		
		$Entry = '';
		switch($rs['Entry']){
			case 0:
				$Entry = 'IN';
				break;
			case 1:
				$Entry = 'OUT';
				break;
			case 2:
				$Entry = 'INOUT';
				break;
			case 3:
				$Entry = 'OUT_BY';
				break;
		}
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $Entry,PHPExcel_Cell_DataType::TYPE_STRING);
		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Volume']/100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['Price'],$rs['Digits']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Commission'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Storage'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['Profit'],3),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Comment'],PHPExcel_Cell_DataType::TYPE_STRING);
		
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







				//出入金汇总
				$whereSqlStr = cz_where_to_str($balwhere);
				$balDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT('PositionID') as TNUM,round(sum(PROFIT),2) as PROFIT from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_deals " . $whereSqlStr, true);
				// $balDatas['vtotal']['TNUM'] = $outDatas['vtotal']['count'] + $inDatas['vtotal']['count'];
				// $balDatas['vtotal']['PROFIT'] = $outDatas['vtotal']['PROFIT'] + $inDatas['vtotal']['PROFIT'];
				// $this->balDatas =  $balDatas;
				//}


				?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><?php echo L('交易记录');?> </h5>
                                <div>
                                <?php
									echo '<div>';
									echo L('交易汇总') , '： &nbsp; &nbsp;' , L('交易手数') , '：' , $volumBuy['vtotal']['VOLUME']/100 , ' ' , L('手') , '  &nbsp; &nbsp;' , L('交易笔数') , '：' , $volumBuy['vtotal']['TNUM'] , ' ' , L('笔') , '  &nbsp; &nbsp;';
									echo L('手续费') , '：$ ' , $volumBuy['vtotal']['COMMISSION'] , ' &nbsp; &nbsp;';
									echo L('库存费') , '：$ ' , $volumBuy['vtotal']['STORAGE'] , ' &nbsp; &nbsp;';
									echo '<i class="fa fa-info-circle" title="' , L('仅统计订单获利') , '">' , L('交易订单利润') , '：</i>';
									if($volumBuy['vtotal']['TRADEPROFIT'] < 0){
										echo '<font color="red" title="' , L('仅统计订单获利') , '">$ ' , $volumBuy['vtotal']['TRADEPROFIT'] , '</font>';
									}else{
										echo '<font color="green" title="' , L('仅统计订单获利') , '">';
											if($volumBuy['vtotal']['TRADEPROFIT'] > 100000000){
												echo '>1' , L('亿美金');
											}else{
												echo '$ ' , $volumBuy['vtotal']['TRADEPROFIT'];
											}
										echo '</font>';
									}
                                    echo '&nbsp;&nbsp;';
									echo '<i class="fa fa-info-circle" title="' , L('统计订单获利') , '+' , L('手续费') , '+' , L('库存费') , '">' , L('交易总盈亏') , '：</i>';
									if($volumBuy['vtotal']['PROFIT'] < 0){
										echo '<font color="red" title="' , L('统计订单获利') , '+' , L('手续费') , '+' , L('库存费') , '">$ ' , $volumBuy['vtotal']['PROFIT'] , '</font>';
									}else{
										echo '<font color="green" title="' , L('统计订单获利') , '+' , L('手续费') , '+' , L('库存费') , '">';
										if($volumBuy['vtotal']['PROFIT'] > 100000000){
											echo '>1' , L('亿美金');
										}else{
											echo '$ ' , $volumBuy['vtotal']['PROFIT'];
										}
										echo '</font>';
									}
									echo '<br/><br/>';
									echo L('出入金汇总') , '： &nbsp; &nbsp;' , L('出入金笔数') , '：' , $balDatas['vtotal']['TNUM'] , ' ' , L('笔');
									echo '&nbsp;&nbsp;';
									echo L('入金总额') , '：<font color="green">$' , $inDatas['vtotal']['PROFIT'] , '</font>';
									echo '&nbsp;&nbsp;';
									echo L('出金总额') , '：<font color="red">$' , $outDatas['vtotal']['PROFIT'] , '</font>';
									echo '&nbsp;&nbsp;';
									echo L('出入金差额') , '：';
									if($inDatas['vtotal']['PROFIT'] + $outDatas['vtotal']['PROFIT'] < 0){
										echo '<font color="red">$' , $inDatas['vtotal']['PROFIT'] + $outDatas['vtotal']['PROFIT'] , '</font>';
									}else{
										echo '<font color="green">';
										if($inDatas['vtotal']['PROFIT'] + $outDatas['vtotal']['PROFIT'] > 100000000){
											echo '>1' , L('亿美金');
										}else{
											echo '$ ' , $inDatas['vtotal']['PROFIT'] + $outDatas['vtotal']['PROFIT'];
										}
										echo '</font>';
									}
									echo '</div>';
                                ?> 
                                </div>
                                <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                    <thead>
                                        <tr>
                                            <th><?php echo L('平台时间'); ?></th>
                                            <th><?php echo L('账号'); ?></th>
                                            <th><?php echo L('姓名'); ?></th>
                                            <th><?php echo L('上级信息'); ?></th>
                                            <th><?php echo L('成交号'); ?></th>
                                            <th><?php echo L('订单'); ?></th>
                                            <th><?php echo L('交易品种'); ?></th>
                                            <th><?php echo L('类型'); ?></th>
                                            <th><?php echo L('趋向'); ?></th>
                                            <th><?php echo L('交易量'); ?></th>
                                            <th><?php echo L('价位'); ?></th>
                                            <th><?php echo L('手续费'); ?></th>
                                            <th><?php echo L('库存费'); ?></th>
                                            <th><?php echo L('利润'); ?></th>
                                            <th><?php echo L('注释'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
                                    $recordCount = intval($count_buy);
                                    
                                    $page = FGetInt('page');
                                    $pagersize = $listRows;
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
                                    
                                    if(count($list_buy) <= 0){
                                        echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
                                    }else{
                                        foreach($list_buy as $key=>$vo){
											//print_r($vo);exit;
											
											$member_id = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$vo['Login']}' and status = 1 and mtserver = '" . $DRAdmin['serverid'] . "'");
									
											$member = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member_id}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
									
											$parent = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member['parent_id']}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
											$vo['nickname'] = $member['nickname'];
											$vo['parent_name'] = $parent['nickname'];

                                            echo '<tr>';											
											
											echo '<td>' , $vo['Time'] , '</td>';
											echo '<td>' , $vo['Login'] , '</td>';
											echo '<td>' , $vo['nickname'] , '</td>';
											if(!can_look_parent_info()){
												echo '<td>-</td>';
											}else{
												echo '<td>' , $vo['parent_name'] , '</td>';
											}
											echo '<td>' , $vo['PositionID'] , '</td>';
											echo '<td>' , $vo['Order'] , '</td>';
											echo '<td>' , $vo['Symbol'] , '</td>';
											echo '<td>';
											switch($vo['Action']){
												case '6':
													echo 'BALANCE';
													break;
												case '0':
													echo 'BUY';
													break;
												case '1':
													echo 'SELL';
													break;
												case '2':
													echo 'BONUS';
													break;
												case '3':
													echo 'COMMISSION';
													break;
												case '4':
													echo 'CHARGE';
													break;
												case '5':
													echo 'CORRECTION';
													break;
												case '7':
													echo 'CREDIT';
													break;
												case '8':
													echo 'COMMISSION_DAILY';
													break;
												case '9':
													echo 'COMMISSION_MONTHLY';
													break;
												case '10':
													echo 'AGENT_DAILY';
													break;
												case '11':
													echo 'AGENT_MONTHLY';
													break;
												case '12':
													echo 'INTERESTRATE';
													break;
												case '13':
													echo 'BUY_CANCELED';
													break;
												case '14':
													echo 'SELL_CANCELED';
													break;
												case '15':
													echo 'DIVIDEND';
													break;
												case '16':
													echo 'DIVIDEND_FRANKED';
													break;
												case '17':
													echo 'TAX';
													break; 
											}
											echo '</td>';
											echo '<td>';
											switch($vo['Entry']){
												case 0:
													echo 'IN';
													break;
												case 1:
													echo 'OUT';
													break;
												case 2:
													echo 'INOUT';
													break;
												case 3:
													echo 'OUT_BY';
													break;
											}
											echo '</td>';
											echo '<td>' , $vo['Volume']/100 , L('手') , '</td>';
											echo '<td>' , round($vo['Price'],$vo['Digits']) , '</td>';
											echo '<td>' , $vo['Commission'] , '</td>';
											echo '<td>' , $vo['Storage'] , '</td>';
											echo '<td>' , round($vo['Profit'],3) , '</td>';
											echo '<td>' , $vo['Comment'] , '</td>';

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
				<?php
            }
			
			
			
			
			
			
			
			
			
			
			
			
			
			
            if ($report_type == '2') {
				$page     = !empty($page) ? $page : 1;
				$listRows            = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
				$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;
				
				if(FGetInt('isdownload') > 0){
					$pageSql = '';
				}
				
				$mt5_positionsModel = new Mt5_positionsModel($DRAdmin['mt4dbname']);
				if (_request('Order')){
					$msg['Position'] = _request('Order'); //订单号查询
					
					$isSearch = 1;
				}
				if (_request('LOGIN') && _request('searchuser') != 'all'){
					//账号查询
					$msg['Login'] = _request('LOGIN');
					
					$isSearch = 1;
				}
			
				if (_request('PROFIT_s') && _request('PROFIT_e')) { //盈亏查询
					$msg['Profit'] = array(array('egt', _request('PROFIT_s')), array('elt', _request('PROFIT_e')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['PROFIT_s']) {
					$msg['Profit'] = array('egt', _request('PROFIT_s'));
					
					$isSearch = 1;
				} elseif ($_REQUEST['PROFIT_e']) {
					$msg['Profit'] = array('elt', _request('PROFIT_e'));
					
					$isSearch = 1;
				}
				if (_request('chi_time_s') && _request('chi_time_e')) { //持仓表持仓时间查询
					$msg['TimeCreate'] = array(array('egt', strtotime(_request('chi_time_s'))), array('elt', strtotime(_request('chi_time_e'))));
					
					$isSearch = 1;
				} elseif ($_REQUEST['chi_time_s']) {
					$msg['TimeCreate'] = array('egt', strtotime(_request('chi_time_s')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['chi_time_e']) {
					$msg['TimeCreate'] = array('elt', strtotime(_request('chi_time_e')));
					
					$isSearch = 1;
				}
				if (_request('geng_time_s') && _request('geng_time_e')) { //持仓表更仓时间查询
					$msg['TimeUpdate'] = array(array('egt', strtotime(_request('geng_time_s'))), array('elt', strtotime(_request('geng_time_e') . ' 23:59:59')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['geng_time_s']) {
					$msg['TimeUpdate'] = array('egt', strtotime(_request('geng_time_s')));
					
					$isSearch = 1;
				} elseif ($_REQUEST['geng_time_e']) {
					$msg['TimeUpdate'] = array('elt', strtotime(_request('geng_time_e') . ' 23:59:59'));
					
					$isSearch = 1;
				}
				$account_id_arr = getid_arr();
				if (_request('qtype')) {
					$symbolwhere['type'] = _request('qtype');
					$symbollist = D('symbol')->where($symbolwhere)->getField('Symbol', true);
					if (empty($symbollist)) {
						$symbollist = array('0');
					}
					
					$isSearch = 1;
				}
				//if($account_id_arr){
				if (_request('LOGIN') == '') {//特殊条件查询
					$msg['Login'] = array("in", $account_id_arr['loginID']);
				}
				if (!empty($symbollist)) {
					$msg['Symbol'] = array('in', $symbollist);
					
					$isSearch = 1;
				}

				$posiwhere = array_merge($msg, array('Action' => array('in', '0,1'))); //mt5持仓报表查询手数 笔数 手续费 利润
				$whereSqlStr = cz_where_to_str($posiwhere);
				$volumPosi = $DB->getField2Arr("select 'vtotal',sum(Volume) as VOLUME,COUNT(Position) as TNUM,round(sum(Profit),2) as TRADEPROFIT from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $whereSqlStr, true);
			
				$whereSqlStr = cz_where_to_str($msg);
				$count_positions = intval($DB->getField("select count(*) as count20210831 from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $whereSqlStr));
				$list_positions = $DB->getDTable("select * from " . $GLOBALS['deposit_mt4dbname'] . ".mt5_positions " . $whereSqlStr . " order by `Position` desc " . $pageSql);









if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('交易记录'));

	$titleArr = array(
		L('创建时间'),
		L('账号'),
		L('姓名'),
		L('上级信息'),
		L('成交号'),
		L('类型'),
		L('交易量'),
		L('交易品种'),
		L('价位'),
		L('更新时间'),
		L('价位'),
		L('库存费'),
		L('利润'),
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
	foreach($list_positions as $key=>$rs){
		$i = 'A';

		$member_id = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$rs['Login']}' and status = 1 and mtserver = '" . $DRAdmin['serverid'] . "'");

		$member = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member_id}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");

		$parent = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member['parent_id']}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
		$rs['nickname'] = $member['nickname'];
		$rs['parent_name'] = $parent['nickname'];

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['TimeCreate'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Login'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);

		if(!can_look_parent_info()){
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
		}else{
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['parent_name'],PHPExcel_Cell_DataType::TYPE_STRING);
		}

		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Position'],PHPExcel_Cell_DataType::TYPE_STRING);

		 $Action = '';
		 if($rs['Action'] == '0'){
			 $Action = 'BUY';
		 }else if($rs['Action'] == '1'){
			 $Action = 'SELL';
		 }
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $Action,PHPExcel_Cell_DataType::TYPE_STRING);
		 
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Volume']/100,PHPExcel_Cell_DataType::TYPE_STRING);
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Symbol'],PHPExcel_Cell_DataType::TYPE_STRING);
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['PriceOpen'],$rs['Digits']),PHPExcel_Cell_DataType::TYPE_STRING);
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['TimeUpdate'],PHPExcel_Cell_DataType::TYPE_STRING);
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['PriceCurrent'],$rs['Digits']),PHPExcel_Cell_DataType::TYPE_STRING);
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['Storage'],PHPExcel_Cell_DataType::TYPE_STRING);
		 $i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, round($rs['Profit'],3),PHPExcel_Cell_DataType::TYPE_STRING);


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
                                <h5><?php echo L('持仓记录');?> </h5>
                                <div>
                                <?php
									echo '<div>';
									echo L('持仓汇总') , '： &nbsp; &nbsp;' , L('交易手数') , '：' , $volumPosi['vtotal']['VOLUME']/100 , ' ' , L('手') , '  &nbsp; &nbsp;' , L('交易笔数') , '：' , $volumPosi['vtotal']['TNUM'] , ' ' , L('笔') , '  &nbsp; &nbsp;';
									echo '<i class="fa fa-info-circle" title="' , L('仅统计订单获利') , '">' , L('持仓盈亏') , '：</i>';
									if($volumPosi['vtotal']['TRADEPROFIT'] < 0){
										echo '<font color="red" title="' , L('仅统计订单获利') , '">$ ' , $volumPosi['vtotal']['TRADEPROFIT'] , '</font>';
									}else{
										echo '<font color="green" title="' , L('仅统计订单获利') , '">';
										if($volumPosi['vtotal']['TRADEPROFIT'] > 100000000){
											echo '>1' , L('亿美金');
										}else{
											echo '$ ' , $volumPosi['vtotal']['TRADEPROFIT'];
										}
										echo '</font>';
									}
									echo '</div>';
                                ?> 
                                </div>
                                <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                    <thead>
                                        <tr>
                                            <th><?php echo L('创建时间'); ?></th>
                                            <th><?php echo L('账号'); ?></th>
                                            <th><?php echo L('姓名'); ?></th>
                                            <th><?php echo L('上级信息'); ?></th>
                                            <th><?php echo L('成交号'); ?></th>
                                            <th><?php echo L('类型'); ?></th>
                                            <th><?php echo L('交易量'); ?></th>
                                            <th><?php echo L('交易品种'); ?></th>
                                            <th><?php echo L('价位'); ?></th>
                                            <th><?php echo L('更新时间'); ?></th>
                                            <th><?php echo L('价位'); ?></th>
                                            <th><?php echo L('库存费'); ?></th>
                                            <th><?php echo L('利润'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
									<?php
                                    $recordCount = intval($count_positions);
                                    
                                    $page = FGetInt('page');
                                    $pagersize = $listRows;
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
                                    
                                    if(count($list_positions) <= 0){
                                        echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
                                    }else{
                                        foreach($list_positions as $key=>$vo){
											//print_r($vo);exit;
											
											$member_id = $DB->getField("select member_id from t_member_mtlogin where loginid = '{$vo['Login']}' and status = 1 and mtserver = '" . $DRAdmin['serverid'] . "'");
									
											$member = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member_id}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
									
											$parent = $DB->getDRow("select parent_id,nickname,email,chineseName from t_member where id = '{$member['parent_id']}' and status = 1 and server_id = '" . $DRAdmin['serverid'] . "'");
											$vo['nickname'] = $member['nickname'];
											$vo['parent_name'] = $parent['nickname'];

                                            echo '<tr>';											
											
												 echo '<td>' , $vo['TimeCreate'] , '</td>';
												 echo '<td>' , $vo['Login'] , '</td>';
												 echo '<td>' , $vo['nickname'] , '</td>';
												if(!can_look_parent_info()){
													echo '<td>-</td>';
												}else{
													 echo '<td>' , $vo['parent_name'] , '</td>';
												}
												 echo '<td>' , $vo['Position'] , '</td>';
												 echo '<td>';
												 if($vo['Action'] == '0'){
													 echo 'BUY';
												 }else if($vo['Action'] == '1'){
													 echo 'SELL';
												 }
												 echo '</td>';
												 echo '<td>' , $vo['Volume']/100 , ' ' , L('手') , '</td>';
												 echo '<td>' , $vo['Symbol'] , '</td>';
												 echo '<td>' , round($vo['PriceOpen'],$vo['Digits']) , '</td>';
												 echo '<td>' , $vo['TimeUpdate'] , '</td>';
												 echo '<td>' , round($vo['PriceCurrent'],$vo['Digits']) , '</td>';
												 echo '<td>' , $vo['Storage'] , '</td>';
												 echo '<td>' , round($vo['Profit'],3) , '</td>';

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
				<?php
            }
			

	


			
			
			
?>



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
