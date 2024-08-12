<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');

$SearchGroupName = $_GET['searchGroupName'];
if(!is_array($SearchGroupName)){
	$SearchGroupName = array();
}
$SearchQ = FRequestStr('searchQ');
$SearchUserType = FRequestStr('searchUserType');if(strlen($SearchUserType) <= 0){$SearchUserType = '1';}
$SearchSTime = FRequestStr('searchSTime');
$SearchETime = FRequestStr('searchETime');if(strlen($SearchSTime) && strlen($SearchETime) && $SearchETime < $SearchSTime){$temp_ = $SearchETime;$SearchETime = $SearchSTime;$SearchSTime = $temp_;}
$SearchReject = FRequestInt('searchReject');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('平仓报表') , getCurrMt4ServerName();?></h4>
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
                                            <form id="commentForm" class="form-inline" novalidate="novalidate" action="?" method="get">
                                            	<?php
												if($DRAdmin['userType'] != 'direct'){
													$groupsList = $DB->getDTable("select * from t_groups where server_id = {$DRAdmin['server_id']}");
													if(count($groupsList)){
													?>
													<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
														<select data-placeholder="<?php echo L('请选择组');?>" name="searchGroupName[]" class="chosen-select" multiple>
															<option value=""><?php echo L('全部');?></option>
															<?php
															foreach($groupsList as $key=>$val){
																echo '<option value="' , $val['group'] , '" hassubinfo="true"';
																if(in_array($val['group'],$SearchGroupName)){
																	echo ' selected';
																}
																echo '>' , $val['group'] , '</option>';
															}
															?>                                              
														</select>
													</div>
													<?php
													}
												}
												?>                                                
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <input type="text" class="form-control" minlength="2" value="<?php echo $SearchQ;?>" name="searchQ" id="searchQ" placeholder="<?php echo L('请输入交易账号或者邮箱'); ?>">
                                                 </div>
												 <?php
                                                if($DRAdmin['userType'] != 'direct'){
													?>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                    <select name='searchUserType' class='form-control'>
                                                        <option value='1'<?php if($SearchUserType == '1'){echo ' selected';} ?>><?php echo L('本人');?></option>
                                                        <option value='2'<?php if($SearchUserType == '2'){echo ' selected';} ?>><?php echo L('所有下级');?></option>
                                                        <option value='3'<?php if($SearchUserType == '3'){echo ' selected';} ?>><?php echo L('本人') . '+' . L('所有下级');?></option>
                                                     </select>
                                                </div>
												 <?php
												}
													?>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="searchSTime" value="<?php echo $SearchSTime;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="searchETime" value="<?php echo $SearchETime;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<div class="custom-control custom-checkbox">
                                                        <input type="checkbox"<?php if($SearchReject == 1){echo ' checked';} ?> class="custom-control-input" name="searchReject" id="searchReject" value="1"><label class="custom-control-label" for="searchReject"><?php echo L('剔除');?></label>
                                                    </div>
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
                                    
                                    
<div>
<?php
$isSearch = 0;
if($SearchGroupName || $SearchQ || $SearchUserType || $SearchSTime || $SearchETime){
	$isSearch = 1;
}


$page = FGetInt('page');
$type = 2;

if(FGetInt('isdownload') > 0){
	$page = -999;
}

$member_where = getid_arr($SearchQ, $SearchUserType, $SearchGroupName,$SearchReject);
//print_r($member_where['allMID']);exit;
$result = getPositionList($page, $SearchSTime, $SearchETime, $type, $member_where['loginID'], $member_where['allMID'] );

$list = $result['list'];
$totaldata = $result['totaldata'];

$mtserver = $DB->getDRow("select * from t_mt4_server where id = '{$DRAdmin['server_id']}' and status = 1");
$mtserver_ver = $mtserver['ver'];

if(FGetInt('isdownload') > 0){
	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('挂单报表'));

	$titleArr = array(
		L('账号'),
		L('英文名'),
		L('等级'),
		L('上级') . '-' . L('英文名'),
		L('上级') . '-' . L('邮箱'),
		L('账户余额'),
		L('平仓量'),
		L('盈亏'),
		L('净值'),
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
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['LOGIN'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		
		$lv = $rs['level'] . L('级') . ',';
		if($rs['userType'] == 'agent'){
			$lv .= L('代理商');
		}else if($rs['userType'] == 'direct'){
			$lv .= L('直接客户');
		}else if($rs['userType'] == 'member'){
			$lv .= L('员工');
		}
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $lv,PHPExcel_Cell_DataType::TYPE_STRING);
		
		if(!can_look_parent_info()){
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  '',PHPExcel_Cell_DataType::TYPE_STRING);
		}else{
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['parent_name'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['parent_email'],PHPExcel_Cell_DataType::TYPE_STRING);
		}

		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['BALANCE'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['VOLUME'] / 100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['PROFIT'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['EQUITY'],PHPExcel_Cell_DataType::TYPE_STRING);

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

//---------------------------

echo L("汇总统计") , '：';
echo L('账户余额') , '：';
if($totaldata['BALANCE'] <= 0){       
    echo '<font color="red">$' , round($totaldata['BALANCE'],2) , '</font>';
}else{
	echo '<font color="green">';
	echo '$' , round($totaldata['BALANCE'],2);
	echo '</font>';
}
echo '&nbsp; &nbsp;' , L('总平仓') , '：' , $totaldata['VOLUME']/100 , L('手');
echo '&nbsp; &nbsp;' , L('总盈亏') , '：';
if($totaldata['PROFIT'] <= 0){
	echo '<font color="red">$' , round($totaldata['PROFIT'],2) , '</font>';
}else{
	echo '<font color="green">';
	echo '$' , round($totaldata['PROFIT'],2);
	echo '</font>';
}
?> 
</div>
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('账户信息');?></th>
                                                    <th class="no-sort"><?php echo L('等级');?></th>
                                                    <?php
                                                    if($DRAdmin['userType'] != 'direct' || $webConfig['f_directCanRecom'] > 0){ 
														echo '<th class="no-sort">' , L('上级信息') , '</th>';
													}
													?>
                                                    <th class="no-sort"><?php echo L('账户余额');?></th>
                                                    <th class="no-sort"><?php echo L('平仓量');?></th>
                                                    <th class="no-sort"><?php echo L('盈亏');?></th>
                                                    <th class="no-sort"><?php echo L('净值');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$recordCount = intval($result['count']);
		
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
			foreach($list as $key=>$rs){
				echo '<tr>';
				echo '<td>';
					echo L('昵称') , '：' , $rs['nickname'] , '';
					echo '<br/>';
					echo L('MT账号') , '：' , $rs['LOGIN'];
					echo '<br/>';
					echo L('MT名称') , '：' , $rs['NAME'];
				echo '</td>';
				if($DRAdmin['userType'] != 'direct' || $webConfig['f_directCanRecom'] > 0){
					echo '<td>';
						echo $rs['level'] , L('级') , ',';
						if($rs['userType'] == 'agent'){
							echo L('代理商');
						}else if($rs['userType'] == 'direct'){
							echo L('直接客户');
						}else if($rs['userType'] == 'member'){
							echo L('员工');
						}
					echo '</td>';
				}
				echo '<td>';
					if(!can_look_parent_info()){
						echo '-';
					}else{
						echo $rs['parent_name'];
						if($rs['parent_email'] != ''){
							echo '<br/>' , $rs['parent_email'];
						}
					}
				echo '</td>';
				echo '<td>';
				echo '<label class="number-text">' , $rs['BALANCE'] , '</label>';
				echo '</td>';
				echo '<td>';
				echo '<label class="number-text">' , $rs['VOLUME'] / 100 , L('手') , '</label>';
				echo '</td>';
				echo '<td>';
				if($mtserver_ver == 5){
					echo '<label onclick="javascript:window.location.href=\'report_history_trade.php?czv=mt5&report_type=1&closetime=1&LOGIN=' , $rs['LOGIN'] , '&CMD=0,1&datascope=my&CLOSE_TIME_s=' , $SearchSTime , '&CLOSE_TIME_e=' , $SearchETime , '\'" style="cursor:pointer;" class="text-' , $rs['PROFIT'] > 0 ? 'green' : 'danger' , ' number-text">$' , $rs['PROFIT'] , '</label>';
				}else{
					echo '<label onclick="javascript:window.location.href=\'report_history_trade.php?closetime=1&LOGIN=' , $rs['LOGIN'] , '&CMD=0,1&datascope=my&CLOSE_TIME_s=' , $SearchSTime , '&CLOSE_TIME_e=' , $SearchETime , '\'" style="cursor:pointer;" class="text-' , $rs['PROFIT'] > 0 ? 'green' : 'danger' , ' number-text">$' , $rs['PROFIT'] , '</label>';
				}
				echo '</td>';
				echo '<td>';
				echo '<label class="number-text">' , $rs['EQUITY'] , '</label>';
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
        
    </body>
</html>
