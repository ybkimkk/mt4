<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

$username = FRequestStr('username');
$datascope = FRequestStr('datascope');if(strlen($datascope) <= 0){$datascope = 'my';}
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
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="header-title"><?php echo L('搜索');?></h4>

                                        <div>
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get" autocomplete="off">
                                            	<div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('客户信息');?>：
                                                    <input type="text" class="form-control" value="<?php echo $username;?>" name="username" placeholder="<?php echo L('会员邮箱'); ?>">
                                                </div>
                                                <?php
                                                if($DRAdmin['userType'] != 'direct'){
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
                                                	<?php echo L('MT账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $LOGIN;?>" name="LOGIN">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('订单编号');?>：
                                                    <input type="text" class="form-control" value="<?php echo $TICKET;?>" name="TICKET">
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('订单盈亏');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="PROFIT_s" value="<?php echo $PROFIT_s;?>" placeholder="<?php echo L('最低价格');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="PROFIT_e" value="<?php echo $PROFIT_e;?>" placeholder="<?php echo L('最高价格');?>">
                                                    </div>
                                                </div>
                                                <?php
												$types = $DB->getDTable("select a.*,b.mt4_name,b.mt4_server from t_type a,t_mt4_server b where a.server_id=b.id and b.id=" . $DRAdmin['server_id'] . " and a.status=1");
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
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('交易指令');?>：
                                                    <select name='CMD' id="CMD" class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='0'>BUY</option>
                                                        <option value='1'>SELL</option>
                                                        <option value='2'>BUY LIMIT</option>
                                                        <option value='3'>SELL LIMIT</option>
                                                        <option value='4'>BUY STOP</option>
                                                        <option value='5'>SELL STOP</option>
                                                        <option value='6'>BALANCE</option>
                                                        <option value='7'>CREDIT</option>
                                                     </select>
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('平仓状态');?>：
                                                    <select name='closetime' class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='1'<?php if($closetime == '1'){echo ' selected';}?>><?php echo L('已平仓');?></option>
                										<option value='2'<?php if($closetime == '2'){echo ' selected';}?>><?php echo L('未平仓');?></option>
                                                     </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('开仓时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="OPEN_TIME_s" value="<?php echo $OPEN_TIME_s;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="OPEN_TIME_e" value="<?php echo $OPEN_TIME_e;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('平仓时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="CLOSE_TIME_s" value="<?php echo $CLOSE_TIME_s;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="CLOSE_TIME_e" value="<?php echo $CLOSE_TIME_e;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <?php
                                                if($DRAdmin['userType'] != 'direct'){
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
												?>
                                                <!--
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('返佣类型');?>：
                                                    <select name='trades_type' class='form-control'>
                                                        <option value=''><?php echo L('全部');?></option>
                                                        <option value='1'<?php if($trades_type == '1'){echo ' selected';}?>><?php echo L('普通交易');?></option>
                              							<option value='2'<?php if($trades_type == '2'){echo ' selected';}?>><?php echo L('跟单');?></option>
                                                     </select>
                                                </div>
                                                -->
                                            	<?php
												if($DRAdmin['userType'] != 'direct'){
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
													<?php
													}
												?>
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

            $reportModel = new ReportModel($DRAdmin['mt4dbname']);
            
            $where = mt4_trad();
            $account_id_arr = report_getid_arr();
			
            if ($qtype) {
                $symbollist = $DB->getField("select t_symbol where `type` = '{$qtype}'", true);
                if (empty($symbollist)) {
                    $symbollist = array('0');
                }
				
				$isSearch = 1;
            }

            //if($account_id_arr){
            if (_request('LOGIN') == NULL || _request('searchuser') == 'all') {//特殊条件查询
                $where['LOGIN'] = array("in", $account_id_arr);
				
				$isSearch = 1;
            }
            if (_request('LOGIN')) {
                $where['LOGIN'] = _request('LOGIN');
				
                $res = $DB->getDRow("select * from t_member_mtlogin where loginid = '" . _request('LOGIN') . "' and mtserver = '{$DRAdmin['server_id']}' and status = 1");
                if(!$res){
					FCreateErrorPage(array(
						'title'=>L("提示"),
						'content'=>L("当前账号不存在"),
						'btnStr'=>L('返回'),
						'url'=>FPrevUrl(),
						'isSuccess'=>0,
						'autoRedirectTime'=>0,
					));
                }
				
                $login_one = _request('LOGIN');

                $acount = $DB->getDRow("select BALANCE,EQUITY from " . $DRAdmin['mt4dbname'] . ".mt4_users where LOGIN = '" . _request('LOGIN') . "' and ENABLE = 1");
				
				$isSearch = 1;
            }

            if (_request('risk') == 1) {
                $where['LOGIN'] = array("in", _request('LOGIN'));
				
				$isSearch = 1;
            }
            if (_request('like') == 1) {
                $where['COMMENT'] = array('like', "agent%"); // MT返佣

				$isSearch = 1;
            }
            if (!empty($symbollist)) {
                $where['SYMBOL'] = array('in', $symbollist);
            }
			
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
			
            unset($wheres);
            if ($trades_type == '1') {
                $wheres['_string'] = " not exists (select * from mt4svr_trades b where b.TICKET = " . $DRAdmin['mt4dbname'] . ".mt4_trades.TICKET )";
            } else if ($trades_type == '2') {
                $wheres['_string'] = " exists (select * from mt4svr_trades b where b.TICKET = " . $DRAdmin['mt4dbname'] . ".mt4_trades.TICKET )";
            }
            if ($wheres)
                $wheres = array_merge($wheres, $where);
            else
                $wheres = $where;
				
			$wheresSql = cz_where_to_str($wheres);

            $count = intval($DB->getField("select count(*) as count1 from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$wheresSql}"));

            $totalDatas = $DB->getField("select 'total',COUNT(TICKET) as TNUM,round(sum(PROFIT),2) as PROFIT from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$wheresSql}",true);

            //仅查询交易的手数
            $field = '';
            /*$spread_profit = $reportModel->getField("SPREAD_PROFIT");
            if ($spread_profit != null) {
                $field = ',round(sum(SPREAD_PROFIT),2) SPREAD_PROFIT';
            }*/
			
            if ($wheres['CMD'] == "") {//查询全部指令
                $volumewhere = array_merge($wheres, array('CMD' => array('in', '0,1')));
				
				$volumewhereStr = cz_where_to_str($volumewhere);

                $volumDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(SWAPS),2) as SWAPS {$field} from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$volumewhereStr}");
            	//print_r($volumDatas);
			} elseif ($wheres['CMD'] == array('in', 0) || $wheres['CMD'] == array('in', 1)) {//仅buysell
                $volumewhere = $wheres;
				
				$volumewhereStr = cz_where_to_str($volumewhere);
				
                $volumDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(SWAPS),2) as SWAPS {$field} from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$volumewhereStr}");
            } elseif ($wheres['CMD'] == array('in', '0,1')) {
                $volumewhere = $wheres;
				
				$volumewhereStr = cz_where_to_str($volumewhere);
				
                $volumDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT+COMMISSION+SWAPS),2) as PROFIT,round(sum(PROFIT),2) as TRADEPROFIT,round(sum(COMMISSION),2) as COMMISSION,round(sum(SWAPS),2) as SWAPS {$field} from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$volumewhereStr}");
            }

            //file_put_contents("abc.txt",M()->_sql());

            //出入金汇总
            if (_request('comm_type') == '') {
                if (_request('BALANCE')) {
                    $balancewhere = array_merge($wheres, array('CMD' => '6'));
                    $balanceOutwhere = array_merge($wheres, array('CMD' => '6', 'PROFIT' => array('lt', 0)));
                    $balanceInwhere = array_merge($wheres, array('CMD' => '6', 'PROFIT' => array('gt', 0)));
					
					$isSearch = 1;
                } else {
                    $balancewhere = array_merge($wheres, array('CMD' => '6', 'COMMENT' => array("like", array('Withdraw%', 'Deposit%'), "OR")));
                    $balanceOutwhere = array_merge($wheres, array('CMD' => '6', 'PROFIT' => array('lt', 0), 'COMMENT' => array('like', 'Withdraw%')));
                    $balanceInwhere = array_merge($wheres, array('CMD' => '6', 'PROFIT' => array('gt', 0), 'COMMENT' => array('like', 'Deposit%')));
                }
            } else {
                $balancewhere = array_merge($wheres, array('CMD' => '6'));
                $balanceOutwhere = array_merge($wheres, array('CMD' => '6', 'PROFIT' => array('lt', 0),));
                $balanceInwhere = array_merge($wheres, array('CMD' => '6', 'PROFIT' => array('gt', 0),));
				
				$isSearch = 1;
            }

			$balancewhereStr = cz_where_to_str($balancewhere);
			
            $balanceDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT),2) as PROFIT from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$balancewhereStr}");
            
			//出金汇总
			$balanceOutwhereStr = cz_where_to_str($balanceOutwhere);
            $balanceOutDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT),2) as PROFIT from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$balanceOutwhereStr}");
            
			//入金汇总
			$balanceInwhereStr = cz_where_to_str($balanceInwhere);
            $balanceInDatas = $DB->getField2Arr("select 'vtotal',sum(VOLUME) as VOLUME,COUNT(TICKET) as TNUM,round(sum(PROFIT),2) as PROFIT from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$balanceInwhereStr}");
            
            $field = empty($_GET['_field']) ? 'TICKET' : $_GET['_field'];
            $order = empty($_GET['_order']) ? ' desc' : " {$_GET['_order']}";
			
			$page     = FGetInt('page');if($page <= 0){$page = 1;}
			$listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
			$pageSql = "LIMIT " . (($page - 1) * $listRows) . "," . $listRows;
			
			if(FGetInt('isdownload') > 0){
				$pageSql = '';
			}
			
			$wheresStr = cz_where_to_str($wheres);			
            $list = $DB->getDTable("select * from " . $DRAdmin['mt4dbname'] . ".mt4_trades {$wheresStr} order by {$field} {$order} {$pageSql}");
			
            //print_r(M()->_sql());
			$t = C("DB_PREFIX");
            foreach ($list as $key => $val) {
                $mtuser = getuser($val['LOGIN']);
                $list[$key]['NAME'] = $mtuser['NAME'];
                $list[$key]['result'] = $DB->getDRow("select mb.email,mb.nickname,mb.chineseName from t_member_mtlogin mt inner join {$t}member mb on mt.member_id=mb.id where mt.loginid = '{$val['LOGIN']}' and mt.status = 1");
            }
			
			
			
			
			
			
			
			
if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('交易记录'));

	$titleArr = array(
		L('订单'),
		L('账户'),
		L('英文名'),
		L('MT NAME'),
		L('交易指令'),
		L('品种'),
		L('交易手数'),
		L('价格') . '-' . L('开仓'),
		L('价格') . ' ' . L('平仓'),
		L('止损'),
		L('止盈'),
		L('利息'),
		L('手续费'),
		L('交易时间') . '-' . L('开仓'),
		L('交易时间') . '-' . L('平仓'),
		L('盈亏'),
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
	foreach($list as $key=>$rs){
		$i = 'A';
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['TICKET'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['LOGIN'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['result']['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['NAME'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		switch($rs['CMD']){
			case '1':
				$CMD = 'SELL';
				break;
			case '2':
				$CMD = 'BUY LIMIT';
				break;
			case '3':
				$CMD = 'SELL LIMIT';
				break;
			case '4':
				$CMD = 'BUY STOP';
				break;
			case '5':
				$CMD = 'SELL STOP';
				break;
			case '6':
				$CMD = 'BALANCE';
				break;
			case '7':
				$CMD = 'CREDIT';
				break;
			case '0':
				$CMD = 'BUY';
				break;
		}
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $CMD,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['SYMBOL'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		if($rs['CMD'] != '6' && $rs['CMD'] != '7'){
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['VOLUME']/100,PHPExcel_Cell_DataType::TYPE_STRING);
			
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['OPEN_PRICE'],$rs['DIGITS']),PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['CLOSE_PRICE'],$rs['DIGITS']),PHPExcel_Cell_DataType::TYPE_STRING);
			
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['SL'],$rs['DIGITS']),PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['TP'],$rs['DIGITS']),PHPExcel_Cell_DataType::TYPE_STRING);
			
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['SWAPS'],2),PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['COMMISSION'],2),PHPExcel_Cell_DataType::TYPE_STRING);
			
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['OPEN_TIME'],PHPExcel_Cell_DataType::TYPE_STRING);
			if($rs['CLOSE_TIME'] != '1970-01-01 00:00:00'){
				$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['CLOSE_TIME'],PHPExcel_Cell_DataType::TYPE_STRING);
			}else{
				$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  L('持仓中'),PHPExcel_Cell_DataType::TYPE_STRING);
			}
			
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['PROFIT'],3),PHPExcel_Cell_DataType::TYPE_STRING);
		}else{
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['OPEN_TIME'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  round($rs['PROFIT'],3),PHPExcel_Cell_DataType::TYPE_STRING);
		}
				
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['COMMENT'],PHPExcel_Cell_DataType::TYPE_STRING);

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
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
            if ($qtype) {
                $ser['type'] = $qtype;
            }
            $ser['server_id'] = $DRAdmin['server_id'];
            $res = $reportModel->getSymbolList($wheres, $ser);

?>



                        <div class="row">
                            <div class="col-12">
                                <div id="accordion" class="custom-accordion mb-4">

                                    <div class="card mb-0">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="m-0">
                                                <a class="custom-accordion-title d-block pt-2 pb-2" data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                    <?php echo L('品种统计');?> <span class="float-right"><i class="mdi mdi-chevron-down accordion-arrow"></i></span>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">

                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('品种组');?></th>
                                                    <th class="no-sort"><?php echo L('订单数量');?></th>
                                                    <th class="no-sort"><?php echo L('交易手数');?></th>
                                                    <th class="no-sort"><?php echo L('订单盈亏');?></th>
                                                    <th class="no-sort"><?php echo L('手续费');?></th>
                                                    <th class="no-sort"><?php echo L('利息');?></th>
                                                    <th class="no-sort"><?php echo L('交易总盈亏');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                              <?php
											  foreach($res as $key=>$val){
											  ?>
                                              <tr>
                                                <td><?php echo $val['type_name'];?></td>
                                                <td><?php echo $val['cc'];?></td>
                                                <td><?php echo $val['Volume'] / 100;?></td>
                                                <td><?php echo $val['Profit'];?></td>
                                                <td><?php echo $val['Commission'];?></td>
                                                <td><?php echo $val['Swaps'];?></td>
                                                <td><?php echo $val['Swaps']+$val['Profit']+$val['Commission'];?></td>
                                              </tr>
                                              <?php
											  }
											  ?>
                                            </tbody>
                                        </table>

                                            </div>
                                        </div>
                                    </div> <!-- end card-->
                                   
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                    
<div>
<?php
echo '<div>';
  if($login_one){
	 echo L('账户余额') , '：' , $acount['BALANCE'] , '&nbsp; &nbsp;' , L('账户净值') , '：' , round($acount['EQUITY'],2) , ' <br/><br/>';
  }
  echo L('交易汇总') , '： &nbsp; &nbsp;' , L('交易手数') , '：' , $volumDatas['vtotal']['VOLUME']/100 , ' ' , L('手') , '  &nbsp; &nbsp;' , L('交易笔数') , '：', $volumDatas['vtotal']['TNUM'], ' ' , L('笔') , '  &nbsp; &nbsp;';
	//if(chk_in_access('查看交易外佣')){
		 echo L('手续费') , '：$ ' , $volumDatas['vtotal']['COMMISSION'] , ' &nbsp; &nbsp;';
	//}
	//if(chk_in_access('查看交易利息')){
		 echo L('利息') , '：$ ' , $volumDatas['vtotal']['SWAPS'];
		 echo '&nbsp;&nbsp;';
	//}
	//if(chk_in_access('查看订单盈亏（仅获利）')){
		echo '<i class="fa fa-info-circle" >' , L('交易订单盈亏') , '：</i>';
		if($volumDatas['vtotal']['TRADEPROFIT'] < 0){
			echo '<font color="red" >$ ' , $volumDatas['vtotal']['TRADEPROFIT'] , '</font>';
		}else{
		  echo '<font color="green" >';
		  if($volumDatas['vtotal']['TRADEPROFIT'] > 100000000){
		  	echo '>1' , L('亿美金');
		  }else{
		  	echo '$ ' , $volumDatas['vtotal']['TRADEPROFIT'];
		  }
		  echo '</font>';
		}
		echo '&nbsp;&nbsp;';
	//}
	
	//if(chk_in_access('查看盈亏汇总（获利+利息+外佣）')){
		echo '<i class="fa fa-info-circle" >' , L('交易总盈亏') , '：</i>';
		if($volumDatas['vtotal']['PROFIT'] < 0){    
			echo '<font color="red">$ ' , $volumDatas['vtotal']['PROFIT'] , '</font>';
		}else{
			echo '<font color="green">';
			  if($volumDatas['vtotal']['PROFIT'] > 100000000){
			  	echo '>1' , L('亿美金');
			  }else{
			  	echo '$ ' , $volumDatas['vtotal']['PROFIT'];
			  }
			echo '</font>';
		}

		//if(chk_in_access('入场点差')){
			echo '&nbsp; &nbsp;' , L('点差汇总') , '：';
			if($volumDatas['vtotal']['SPREAD_PROFIT'] == ''){
				echo '0';
			}else{
				echo $volumDatas['vtotal']['SPREAD_PROFIT'];
			}
		//}

		echo '<br /><br/>';
	//}

	//if(chk_in_access('查看出入金汇总')){
		echo L('出入金汇总') , '： &nbsp; &nbsp;' , L('出入金笔数') , '：' , $balanceDatas['vtotal']['TNUM'] , ' ' , L('笔');
		echo '&nbsp;&nbsp;';
		echo L('入金总额') , '：<font color="green">$' , $balanceInDatas['vtotal']['PROFIT'] , '</font>';
		echo '&nbsp;&nbsp;';
		echo L('出金总额') , '：<font color="red">$' , $balanceOutDatas['vtotal']['PROFIT'] , '</font>';
		echo '&nbsp;&nbsp;';
	  echo L('出入金差额') , '：';
	  if($balanceInDatas['vtotal']['PROFIT'] + $balanceOutDatas['vtotal']['PROFIT'] < 0){         
		echo '<font color="red">$' , round($balanceInDatas['vtotal']['PROFIT'] + $balanceOutDatas['vtotal']['PROFIT'],2) , '</font>';
	  }else{
		echo '<font color="green">';
		  if($balanceInDatas['vtotal']['PROFIT'] + $balanceOutDatas['vtotal']['PROFIT'] > 100000000){ 
		  	echo '>1' , L('亿美金');
		  }else{
		  	echo '$ ' , round($balanceInDatas['vtotal']['PROFIT'] + $balanceOutDatas['vtotal']['PROFIT'],2);
		  }
		echo '</font>';
	  }
	//}
 
	echo '</div>';
?> 
</div>
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('订单') , '/' , L('账户') , '<br>' , 'TICKET/LOGIN';?></th>
                                                    <th class="no-sort"><?php echo L('英文名') , '/' , 'MT' , '<br>' , 'ENAME/MT NAME';?></th>
                                                    <!--
                                                    <th class="no-sort"><?php echo L('买卖信号') , '<br/>IN/OUT';?></th>
                                                    -->
                                                    <th class="no-sort"><?php echo L('交易指令') , '/' , L('品种') , '<br>' , 'CMD/SYMBOL';?></th>
                                                    <th class="no-sort"><?php echo L('交易手数') , '<br>VOLUME';?></th>
                                                    <th class="no-sort"><?php echo L('价格') , '<br>PRICE';?></th>
                                                    <th class="no-sort"><?php echo L('止损') , '/' , L('止盈') , '<br>' , 'SL/TP';?></th>
                                                    <th class="no-sort"><?php echo L('利息') , '/' , L('手续费') , '($)<br>SWAP/COMMISSION';?></th>
                                                    <th class="no-sort"><?php echo L('交易时间') , '<br>TIME';?></th>
                                                    <th class="no-sort"><?php echo L('盈亏') , '($)<br>PROFIT';?></th>
                                                    <!--
                                                    <th class="no-sort"><?php echo L('点差') , '($)<br>SPREAD_PROFIT';?></th>
                                                    -->
                                                    <th class="no-sort"><?php echo L('注释') , '<br>COMMENT';?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$recordCount = intval($count);
		
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
			$count_ = 4;
			
			foreach($list as $key=>$rs){
				echo '<tr>';
				echo '<td>';
					echo '<a href="?clause=view_trade&TICKET=' , $rs['TICKET'] , '">' , L('订单') , '：' , $rs['TICKET'] , '<br/>';
                    echo L('账号') , '：' , $rs['LOGIN'] , '</a>';
				echo '</td>';
				echo '<td>';
					echo L('英文名'), '：' , $rs['result']['nickname'] , '<br/>MT：' , $rs['NAME'];
				echo '</td>';
				/*echo '<td>';
					if($rs['OPEN_PRICE'] > 0){
                    	echo L('进场');
					}else{
                        echo L('出场');
					}
				echo '</td>';*/
				echo '<td>';
				switch($rs['CMD']){
					case '1':
						echo 'SELL';
						break;
					case '2':
						echo 'BUY LIMIT';
						break;
					case '3':
						echo 'SELL LIMIT';
						break;
					case '4':
						echo 'BUY STOP';
						break;
					case '5':
						echo 'SELL STOP';
						break;
					case '6':
						echo 'BALANCE';
						break;
					case '7':
						echo 'CREDIT';
						break;
					case '0':
						echo 'BUY';
						break;
				}
				echo '<br/>';								
				if($rs['CMD'] != '6' && $rs['CMD'] != '7'){
					echo $rs['SYMBOL'];
				}
				echo '</td>';				
				
				if($rs['CMD'] != '6' && $rs['CMD'] != '7'){
					echo '<td>' , $rs['VOLUME']/100 , ' ' , L('手') , '</td>';
					echo '<td>';
						echo L('开仓') , '：' , round($rs['OPEN_PRICE'],$rs['DIGITS']);
						echo '<br>';
						echo L('平仓') , '：' , round($rs['CLOSE_PRICE'],$rs['DIGITS']);
					echo '</td>';
					echo '<td>';
						echo L('止损') , '：' , round($rs['SL'],$rs['DIGITS']) , '<br/>';
						echo L('止盈') , '：' , round($rs['TP'],$rs['DIGITS']);
					echo '</td>';
					echo '<td>';
						echo L('利息') , '：' , round($rs['SWAPS'],2) , '<br/>';
						echo L('手续费') , '：' , round($rs['COMMISSION'],2);
					echo '</td>';
					echo '<td>';
						echo L('开仓') , '：' , $rs['OPEN_TIME'] , $server['ver'] , '<br/>';
						if($rs['CLOSE_TIME'] != '1970-01-01 00:00:00'){
						  echo L('平仓') , '：' , $rs['CLOSE_TIME'];
						}else{
						  echo L('平仓') , '： <font color="green">' , L('持仓中') , '...</font>';
						}
					echo '</td>';
					echo '<td>';
					  echo '<a href="?clause=view_trade&TICKET=' , $rs['TICKET'] , '">';
					  if($rs['PROFIT'] < 0){
						echo '<font color="red">' , round($rs['PROFIT'],3) , '</font>';
					  }else{
						echo '<font color="green">' , round($rs['PROFIT'],3) , '</font>';
					  }
					  echo '</a>';
					echo '</td>';
				}else{
					echo '<td colspan="' , $count_ , '" align="center">-</td>';
					echo '<td>' , $rs['OPEN_TIME'] , '</td>';
					echo '<td>';
						echo '<font color="green"> <a href="?clause=view_trade&TICKET=' , $rs['TICKET'] , '">' , round($rs['PROFIT'],3) , '</a></font>';
					echo '</td>';
				}
				
				/*echo '<td>';
				  if($rs['SPREAD_PROFIT'] == '0'){
					echo '0';
				  }else{
					echo round($rs['SPREAD_PROFIT'],3);
				  }
				echo '</td>';*/
				
				echo '<td>' , $rs['COMMENT'] , '</td>';

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
        
    </body>
</html>
