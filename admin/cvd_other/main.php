<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$currFilename = FGetCurrUrl(8);
if(stripos($currFilename,'0.php') !== false){
	$tablename = 't_sale_commission_other0';
	$titlesstr = '(0)';
}else{
	$tablename = 't_sale_commission_other';
	$titlesstr = '';
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('返佣(补)报表') , $titlesstr , getCurrMt4ServerName();?></h4>
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
                                                    <input type="text" class="form-control" value="<?php echo _request('nickname');?>" name="nickname" placeholder="<?php echo L('请输入会员者昵称邮箱'); ?>">
                                                </div>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                	<?php echo L('账号');?>：
                                                    <input type="text" class="form-control" value="<?php echo _request('login');?>" name="login" placeholder="<?php echo L('请输入交易的 MT 账户')?>">
                                                </div>
                                                <?php
												$ssoArr = $DB->getDTable("select * from t_sale_setting_other where STATUS=1",'ID');
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:80px;">
                                                	<?php echo L('佣金规则');?>：
                                                    <select name="settingId" id="settingId" class="form-control">
                                                        <option value=""><?php echo L('全部');?></option>
                                                        <?php
														foreach($ssoArr as $key=>$val){
															echo '<option value="' , $val['ID'] , '">【' , $val['ID'] , '】' , $val['f_title'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <div class="form-group mr-sm-3 mb-sm-2">
                                                	<?php echo L('结算时间');?>：
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="close_time_start" value="<?php echo _request('close_time_start');?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="close_time_end" value="<?php echo _request('close_time_end');?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                            	<?php
												/*$groupsList = $DB->getDTable("select * from t_groups where server_id = {$DRAdmin['server_id']}");
                                                if(count($groupsList) && $DRAdmin['_dataRange'] >= 2){
												?>
                                            	<div class="form-group mr-sm-2 mb-sm-2" style="min-width:120px;">
                                                    <select data-placeholder="<?php echo L('请选择分组');?>" name="GROUP_NAME[]" class="chosen-select" multiple>
                                                        <option value="all_group"><?php echo L('全部');?></option>
                                                        <?php
														foreach($groupsList as $key=>$val){
															echo '<option value="' , $val['group'] , '" hassubinfo="true"';
															if(@in_array($val['group'],$_GET['GROUP_NAME'])){
																echo ' selected';
															}
															echo '>' , $val['group'] , '</option>';
														}
														?>                                              
                                                    </select>
                                                </div>
                                                <?php
												}*/
												?>
                                                <div class="form-group mr-sm-2 mb-sm-2">
                                                        <div>
                                                        <button type="submit" class="btn btn-primary" id="searchuserbtn"><?php echo L('搜索返佣'); ?></button>
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


$where = "where f_serverId = '" . $DRAdmin['server_id'] . "'";

$parentid = $DRAdmin['id'];
$memberid = $DRAdmin['id'];
if ($DRAdmin['_dataRange'] >= 2) {
	$parentid = "admin";
	$memberid = "admin";
}

if (isset($_REQUEST['nickname']) && $_REQUEST['nickname'] != '') {//输入了下级昵称
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

	$where = $where . " and f_uid = " . $member['id'];
	
	$isSearch = 1;
}

if (isset($_REQUEST['login']) && $_REQUEST['login'] != '') {
	$member = $DB->getDRow("select * from t_member_mtlogin where mtserver = '{$DRAdmin['server_id']}' and loginid = '" . $_REQUEST['login'] . "' and status = 1");
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
	_check_member_scope($parentid, $member['member_id']); //检验是否下级

	$where = $where . " and f_uid = " . $member['member_id'];
	
	$isSearch = 1;
}

$settingId = intval($_REQUEST['settingId']);
if($settingId > 0){
	$where = $where . " and f_settingId = " . $settingId;

	$isSearch = 1;
}

if (_request('close_time_start')){
	$where = $where . " and f_addTime >= '" . _request('close_time_start') . " 00:00:00'";
	
	$isSearch = 1;
}
if (_request('close_time_end')){
	$where = $where . " and f_addTime <= '" . _request('close_time_end') . " 23:59:59'";
	
	$isSearch = 1;
}

/*if ($_GET['GROUP_NAME']) {
	$group_arr = implode(',', $_GET['GROUP_NAME']);
	if ($group_arr == 'all_group') {
		$group_arr = $DB->getField("select `GROUP` from t_groups where server_id = '" . $DRAdmin['server_id'] . "'", true);
		$group_arr = implode(',', $group_arr);
	}
	if (strstr($group_arr, '\\')) {
		$group_arr = str_replace('\\', '\\\\', $group_arr);
	}
	$group_arr = explode(',', $group_arr);

	$where = $whereMtUsers . " and `GROUP` in ('" . implode('\',\'', $group_arr) . "')";
	
	$isSearch = 1;
}*/

//-------------------------------------------------------------

if(FGetInt('isdownload') > 0){
	$list = $DB->getDTable("select * from {$tablename} {$where} order by id desc");

	include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('已返佣记录'));

	$titleArr = array(
		L('订单'),
		L('账号'),
		L('英文名'),
		L('邮箱'),
		L('返佣等级'),
		L('交易手数'),
		L('交易种类'),
		L('返佣标准'),
		L('返佣金额'),
		L('返佣时间'),
		L('平仓时间'),
		L('返佣类型'),
		L('计算公式'),
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
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['email'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  L_level_name($rs['LEVEL_NAME']),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['VOLUME']/100,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['SYMBOL'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$fybz = '';
		switch($rs['COMM_TYPE']){
			case 'SCALE':
				$fybz = $rs['LEVEL_FIXED'] . '%';
				break;
			case 'POINT':
				$fybz = '$' . $rs['LEVEL_FIXED'] . '/pip';
				break;
			case 'EQUAL_SCALE':
			case 'UP_SCALE':
				$fybz = $rs['LEVEL_FIXED'] . '%';
				break;
			default:
				$fybz = '$' . $rs['LEVEL_FIXED'];
				break;
		}		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $fybz,PHPExcel_Cell_DataType::TYPE_STRING);
		
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['AMOUNT'],PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  time2mt4zone($rs['BALANCE_TIME'],$DRAdmin['timezone'],'Y-m-d H:i:s'),PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['CLOSE_TIME'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		$fylx = '';
		 if($rs['COMMISSION_PATTERN'] == 0){
			$fylx = L('内佣');
		 }else if ($rs['COMMISSION_PATTERN'] == 1){
			$fylx = L('外佣');
		 }else{
			$fylx = L('平级推荐');
		 }
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $fylx,PHPExcel_Cell_DataType::TYPE_STRING);
		$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j,  $rs['FORMULA'],PHPExcel_Cell_DataType::TYPE_STRING);
		
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
/*echo '<b>' , L('汇总') , '</b>&nbsp; ';
echo L('金额') , '：$' , $totalAmount['vtotal']['TAMOUNT'] , ' &nbsp;&nbsp;';
echo L('交易量') , '：' , $totalAV['TVOLUME']/100 , L('手') , '&nbsp;&nbsp;';
echo L('笔数') , '：' , $totalAmount['vtotal']['TNUM'] , L('条') , '  ' , L('最新返佣时间') , '：<i class="fa fa-clock-o"></i> ' , date('Y-m-d H:i:s',C('CALC_LAST_TIME')-8*3600+$DRAdmin['timezone']*3600);
*/
?>
</div>
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('账号');?></th>
                                                    <th class="no-sort"><?php echo L('佣金规则');?></th>
                                                    <th class="no-sort"><?php echo L('统计手数');?></th>
                                                    <th class="no-sort"><?php echo L('返佣金额');?></th>
                                                    <th class="no-sort"><?php echo L('计算时间');?></th>
                                                    <th class="no-sort"><?php echo L('发放状态');?></th>
													<th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$recordCount = intval($DB->getField("select count(*) from {$tablename} {$where}"));

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
		$sqlRecordStartIndex = $cnPager->FGetSqlRecordStartIndex();

		$query = $DB->query("select a.*,b.nickname,b.realname,b.email from (select * from {$tablename} {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join t_member b on a.f_uid = b.id");
		if($DB->numRows($query) <= 0){
			echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
		}else{
			if($mtserver['ver'] ==  5){
				$count_ = 3;
			}else{
				$count_ = 4;
			}
			
			while($rs = $DB->fetchArray($query)){
				$mtlogin = $DB->getDRow("select * from `t_member_mtlogin` where `member_id` = '{$rs['f_uid']}' and `status` = 1 and `mtserver` = '{$rs['f_serverId']}' and mt_type = 0 order by id desc");
				$loginid = $mtlogin['loginid'];

				echo '<tr>';
				echo '<td>';
				echo 'UID：' , $rs['f_uid'] , '<br>';
				echo 'LOGIN：' , $loginid , '<br>';
				echo $rs['nickname'] , '<br>';
				echo L('邮箱') , '：<span class="lookemail">' , hideStr($rs['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['email'] , '"></i>';
				echo '</td>';
				echo '<td>';
				echo '【' , $rs['f_settingId'] , '】' , $ssoArr[$rs['f_settingId']]['f_title'] , '<br>';
				echo date('Y-m-d',strtotime($ssoArr[$rs['f_settingId']]['TC_DATE_S'])) , ' ~ ' , date('Y-m-d',strtotime($ssoArr[$rs['f_settingId']]['TC_DATE_E'])) , '<br>';
				echo L('达到手数') , ': ' , $ssoArr[$rs['f_settingId']]['LIMIT_MIN_SS'] * 1 , '<br>';
				echo cvd_str_cal_type($ssoArr[$rs['f_settingId']]['CAL_TYPE_AGENT'],$ssoArr[$rs['f_settingId']]['CAL_NUM_AGENT']);
				echo '</td>';
				echo '<td>' , $rs['f_ss'] * 1 , '</td>';
				echo '<td>';
				echo $rs['f_cal'] * 1;
				echo '</td>';
				echo '<td>';
				echo $rs['f_addTime'];
				echo '</td>';
				echo '<td class="center">';
				if($rs['f_isJs']){
					echo L('已发放');
				}else{
					echo L('待发放');
				}
				echo '</td>';
				echo '<td class="center">';
				if($rs['f_isJs'] <= 0){
					echo '<button type="button" val="' , $rs['id'] , '" class="btn btn-success btn-sm jspost">' , L('发放') , '</button> ';
				}
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
        
        
        
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script> 
        
        
        
<script type="text/javascript">  
	init_findinfo();

	$(document).on("click","#downBtns",function(){
  	//$("#downBtns").click(function(){
        var value=$("#commentForm").serialize();
        document.location.href="{:U('Commission/down_view_details',array('act'=>'down'))}?"+value;
    });
  	
	<?php
	$symbols = $DB->getDTable("select * from t_symbol where server_id = '{$DRAdmin['server_id']}'");
	$symbols = json_encode($symbols);
	$symbols = addslashes($symbols);
	?>
	var symbol_json = '<?php echo $symbols; ?>';
	
	
    $(".chosen-select").chosen( {width: "100%"});
    $("#GROUP_NAME").change(function(){
        var group = $("#GROUP_NAME  option:selected").val();
        if(group=='all_group'){
            chose_mult_set_ini('#GROUP_NAME','all_group');
        }
    })

    function chose_mult_set_ini(select, values) {
        var arr = values.split(',');
        var length = arr.length;
        var value = '';
        //console.log(arr);
        newarr = trimSpace(arr);
        $(select).val(newarr);
        $(select).trigger("chosen:updated");  
    }

    function trimSpace(array){
        for(var i = 0 ;i<array.length;i++){
            if(array[i] == "" || typeof(array[i]) == "undefined"){
                array.splice(i,1);
                i= i-1;     
            }
        }
        return array;
    }
	
  	
</script>
<script>
$(document).on("click",".jspost",function(){
	//prompt层
	var id = $(this).attr('val');
	swal({
		title: "<?php echo L('发放'); ?>",
		text: "<?php echo L('发放后，佣金将进入CRM余额'); ?>！",
		showCancelButton: true,
		cancelButtonText: "<?php echo L('取消'); ?>",
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "<?php echo L('确认发放'); ?>",
		closeOnConfirm: false,
		showLoaderOnConfirm: true,
	}, function () {
		var url = "?clause=jspost";
		$.post(url, {id: id}, function (data) {
			if (data.status) {
				swal("<?php echo L('发放成功'); ?>", data.info, "success");
				setTimeout(function () {
					document.location.reload()
				}, 1500);
			} else {
				swal("<?php echo L('发放失败'); ?>", data.info, "warning");
			}
		}, 'json');
	});

});
</script>
        
        
        
    </body>
</html>
