 <?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchLogin = FGetStr('searchLogin');
$SearchStatus = FGetStr('searchStatus');
$SearchBackStatus = FGetStr('searchBackStatus');
$SearchZyeStatus = FGetStr('searchZyeStatus');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('赠金审核') , getCurrMt4ServerName();?></h4>
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
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('MT账号');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchLogin;?>" name="searchLogin" placeholder="<?php echo L('请输入赠金MT的账户'); ?>">
                                                </div>
												<div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('赠金状态');?>：</label>
                                                    <div>
                                                        <select name="searchStatus" class="form-control">
                                                        	<option value=''>-=<?php echo L('所有'); ?>=-</option>
                                                            <option value='1'<?php if($SearchStatus == '1'){echo ' selected="selected"';}?>><?php echo L('已处理'); ?></option>
                                                            <option value='-5'<?php if($SearchStatus === '-5'){echo ' selected="selected"';}?>><?php echo L('未处理'); ?></option>
                                                            <option value='-1'<?php if($SearchStatus === '-1'){echo ' selected="selected"';}?>><?php echo L('已驳回'); ?></option>
                                                        </select>
                                                    </div>
												</div>
												<div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('转余额状态');?>：</label>
                                                    <div>
                                                        <select name="searchZyeStatus" class="form-control">
                                                        	<option value=''>-=<?php echo L('所有'); ?>=-</option>
                                                            <option value='1'<?php if($SearchZyeStatus == '1'){echo ' selected="selected"';}?>><?php echo L('不必转余额'); ?></option>
                                                            <option value='2'<?php if($SearchZyeStatus === '2'){echo ' selected="selected"';}?>><?php echo L('待检测'); ?></option>
                                                            <option value='3'<?php if($SearchZyeStatus === '3'){echo ' selected="selected"';}?>><?php echo L('未达标'); ?></option>
                                                            <option value='4'<?php if($SearchZyeStatus === '4'){echo ' selected="selected"';}?>><?php echo L('待转余额'); ?></option>
                                                            <option value='5'<?php if($SearchZyeStatus === '5'){echo ' selected="selected"';}?>><?php echo L('已转余额'); ?></option>
                                                            <option value='6'<?php if($SearchZyeStatus === '6'){echo ' selected="selected"';}?>><?php echo L('驳回'); ?></option>
                                                        </select>
                                                    </div>
												</div>
												<div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('扣回状态');?>：</label>
                                                    <div>
                                                        <select name="searchBackStatus" class="form-control">
                                                        	<option value=''>-=<?php echo L('所有'); ?>=-</option>
                                                            <option value='1'<?php if($SearchBackStatus == '1'){echo ' selected="selected"';}?>><?php echo L('不必扣回'); ?></option>
                                                            <option value='2'<?php if($SearchBackStatus === '2'){echo ' selected="selected"';}?>><?php echo L('待扣回'); ?></option>
                                                            <option value='3'<?php if($SearchBackStatus === '3'){echo ' selected="selected"';}?>><?php echo L('已扣回'); ?></option>
                                                        </select>
                                                    </div>
												</div>
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
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
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('赠金规则') , '/' , L('申请时间');?></th>
                                                    <th class="no-sort"><?php echo L('入金金额');?> (USD)</th>
                                                    <th class="no-sort"><?php echo L('赠金金额');?></th>
                                                    <th class="no-sort">①<?php echo L('赠金');?></th>
                                                    <th class="no-sort">②<?php echo L('转余额');?></th>
                                                    <th class="no-sort">③<?php echo L('扣回');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where a.ServerId = '{$DRAdmin['server_id']}'";
	if($SearchLogin){
		$where .= " and a.MtLogin = '{$SearchLogin}'";
	}
	if($SearchStatus == '-5'){
		$where .= " and a.Status = '0'";
	}else if ($SearchStatus != '') {
		$where .= " and a.Status = '{$SearchStatus}'";
	}
		
	switch($SearchZyeStatus){
		case '1':
			$where .= " and a.f_zye_endTime <= 0";
			break;
		case '2':
			$where .= " and a.f_zye_endTime > 0 and a.f_zye_endBackTime <= 0 and a.f_zye_endTimeChkEd <= 0";
			break;
		case '3':
			$where .= " and a.f_zye_endTime > 0 and a.f_zye_endBackTime <= 0 and a.f_zye_endTimeChkEd > 0 and f_zye_endTimeChkState = -1";
			break;
		case '4':
			$where .= " and a.f_zye_endTime > 0 and a.f_zye_endBackTime <= 0 and a.f_zye_endTimeChkEd > 0 and f_zye_endTimeChkState > 0";
			break;
		case '5':
			$where .= " and a.f_zye_endTime > 0 and a.f_zye_endBackTime > 0";
			break;
		case '6':
			$where .= " and a.f_zye_endTime > 0 and a.f_zye_endBackTime <= 0 and a.f_zye_endTimeChkEd > 0 and f_zye_endTimeChkState = -2";
			break;
	}

	switch($SearchBackStatus){
		case '1':
			$where .= " and a.f_endTime <= 0";
			break;
		case '2':
			$where .= " and a.f_endTime > 0 and a.f_endBackTime <= 0";
			break;
		case '3':
			$where .= " and a.f_endTime > 0 and a.f_endBackTime > 0";
			break;
	}

	$recordCount = intval($DB->getField("select count(*) from `t_credit_record` a {$where}"));
	
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
	$query = $DB->query("select a.*,b.Name,b.Scale,b.Condition,b.Result SetResult,b.Type from `t_credit_record` a left join t_credit_setting b on a.CreditId=b.Id {$where} order by a.id desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){			
			echo '<tr>';
			echo '<td>' , $rs['MtLogin'] , '</td>';
			echo '<td>';
			if($rs['Type'] == 'REG' || $rs['Object'] == 'reg'){
				echo '<span class="label label-success">' , L('开户') , '</span>';
			}else{
				if($rs['Type'] == 'BALANCE_FIRST'){
					echo '<span class="label label-info">' , L('首次入金') , '</span>';
				}else if($rs['Type'] == 'BALANCE_PER'){
					echo '<span class="label label-success">' , L('单笔入金') , '</span>';
				}
				
				echo number_format($rs['Condition'],2,".",",");
				echo ' USD';
				echo '(' , $rs['SetResult'];
				if($rs['Scale'] == 'Scale'){
					echo '%';
				}else if($rs['Scale'] == 'Fixed'){
					echo 'USD';
				}
				echo ')<br>';
				echo $rs['f_fromAbout'];
			}
			echo '<br>' , date('Y-m-d H:i:s',$rs['CreateTime']);
			echo '</td>';
			echo '<td>' , $rs['InMoney'] , '</td>';
			echo '<td>' , $rs['Result'] , '</td>';
			echo '<td>';
			switch($rs['Status']){
				case 0:
					echo '<span class="badge badge-warning">' , L('未处理') , '</span>';
					break;
				case 1:
					echo '<span class="badge badge-default">' , L('已处理') , '</span>';
					break;
				case -1:
					echo '<span class="badge badge-danger">' , L('已驳回') , '</span>';
					break;
			}
			echo '<br>';
			if(strlen($rs['Ticket']) > 0){
				echo 'Ticket: ' . $rs['Ticket'] . '<br>';
			}
			if(strlen($rs['Memo']) > 0){
				echo '<font color="red">' . $rs['Memo'] . '</font><br>';
			}
			if($rs['CheckTime'] > 0){
				echo date('Y-m-d H:i:s',$rs['CheckTime']) , '<br>';
			}
			if($rs['Status'] == 0){
				echo '<button type="button" val="' , $rs['Id'] , '" data-toggle="modal" data-target="#myModal" class="btn btn-success btn-sm visitcredit">' , L('审核') , '</button> ';
				echo '<button type="button" val="' , $rs['Id'] , '" data-toggle="modal" data-target="#myModal" class="btn btn-primary btn-sm resetcredit">' , L('驳回') , '</button> ';
			}
			echo '<a href="?clause=showinfo&id=' , $rs['Id'] , '" class="btn btn-primary btn-sm">' , L('查看') , '</a>';
			echo '</td>';
						
			echo '<td>';
			if($rs['f_zye_endTime'] > 0){
				if($rs['f_zye_endBackTime'] > 0){
					echo '<span class="badge badge-success">' , L('已转余额') , '</span><br>';
					echo date('Y-m-d H:i:s',$rs['f_zye_endBackTime']) , '<br>';
					echo 'Ticket: ' . $rs['f_zye_endBackTicket'] . '<br>';
					echo '<a href="?clause=zyeinfo&id=' , $rs['Id'] , '" class="btn btn-primary btn-sm">' , L('查看') , '</a>';
				}else{
					if($rs['f_zye_endTimeChkEd'] > 0){
						if($rs['f_zye_endTimeChkState'] > 0){
							echo '<span class="badge badge-danger">' , L('待转余额') , '</span><br>';

							echo L('检测') . '：';
							echo date('Y-m-d H:i:s',$rs['f_zye_lastChkTime']) , '<br>';
							echo L('统计手数') . '：';
							echo $rs['f_zye_lot'] , '<br>';
							
							$zye_lot_info = unserialize($rs['f_zye_lot_info']);
							if($zye_lot_info){
								echo L('要求手数') . '：';
								echo $zye_lot_info['rclot'] , '<br>';
							}

							echo '<a href="?clause=zyeinfo&id=' , $rs['Id'] , '" class="btn btn-success btn-sm">' , L('审核') , '</a> ';
							//echo '<a href="?clause=savezye&id=' , $rs['Id'] , '" onclick="return confirm(\'' , L('确定审核通过吗') , '？\')" class="btn btn-success btn-sm">' , L('审核') , '</a> ';
							//echo '<a href="?clause=unzye&id=' , $rs['Id'] , '" onclick="return confirm(\'' , L('确定驳回吗') , '？\')" class="btn btn-primary btn-sm">' , L('驳回') , '</a> ';
						}else if($rs['f_zye_endTimeChkState'] == -1){
							echo '<span class="badge badge-dark">' , L('未达标') , '</span><br>';
							echo '<a href="?clause=zyeinfo&id=' , $rs['Id'] , '" class="btn btn-success btn-sm">' , L('查看') , '</a> ';
						}else if($rs['f_zye_endTimeChkState'] == -2){
							echo '<span class="badge badge-dark">' , L('驳回') , '</span><br>';
							echo '<a href="?clause=zyeinfo&id=' , $rs['Id'] , '" class="btn btn-success btn-sm">' , L('查看') , '</a> ';
						}
					}else{
						echo '<span class="badge badge-warning">' , L('待检测') , '</span> ';
						echo date('Y-m-d H:i:s',$rs['f_zye_endTime']) , '<br>';
						if($rs['f_zye_lastChkTime'] > 0){
							echo L('检测') . '：';
							echo date('Y-m-d H:i:s',$rs['f_zye_lastChkTime']) , '<br>';
							echo L('统计手数') . '：';
							echo $rs['f_zye_lot'] , '<br>';
							
							$zye_lot_info = unserialize($rs['f_zye_lot_info']);
							if($zye_lot_info){
								echo L('要求手数') . '：';
								echo $zye_lot_info['rclot'] , '<br>';
							}
							
							echo '<a href="?clause=zyeinfo&id=' , $rs['Id'] , '" class="btn btn-success btn-sm">' , L('查看') , '</a> ';
						}
					}
				}
			}else{
				echo '(' , L('不必转余额') , ')';
			}
			echo '</td>';

			echo '<td>';
			if($rs['f_endTime'] > 0){
				if($rs['f_endBackTime'] > 0){
					echo '<span class="badge badge-danger">' , L('已扣回') , '</span><br>';
					echo 'Ticket: ' . $rs['f_endBackTicket'] . '<br>';
					echo date('Y-m-d H:i:s',$rs['f_endBackTime']) , '<br>';
				}else{
					echo '<span class="badge badge-warning">' , L('待扣回') , '</span> ';
					echo date('Y-m-d H:i:s',$rs['f_endTime']) , '<br>';
					echo '<a href="?clause=saveback&id=' , $rs['Id'] , '" onclick="return confirm(\'' , L('确定立即扣回吗') , '？\')" class="btn btn-secondary btn-sm">' , L('立即扣回') , '</a> ';
				}
			}else{
				echo '(' , L('不必扣回') , ')';
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
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script> 
        
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
        
        
        
        
        
<script>    
$(function() {
	//驳回
	$(document).on("click",".resetcredit",function(){
	//$(".resetcredit").click(function() {
		//prompt层
		var id = $(this).attr('val');
		swal({
			title: "<?php echo L('赠金驳回'); ?>",
			type: "input",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('确认'); ?>",
			closeOnConfirm: false,   
			showLoaderOnConfirm: true,
			inputPlaceholder: "<?php echo L('请输入驳回原因'); ?>"
		}, function (pass) {
			if(pass==''){
				swal.showInputError("<?php echo L('请输入驳回原因'); ?>!");
				return false
			}
			  var url = "?clause=back";
			 $.post(url, {id: id, content: pass}, function(data) {
				
				if (data.status) {
					swal("<?php echo L('赠金驳回'); ?>", data.info, "success");
					setTimeout(function() {
					 document.location.reload()
					}, 1500);
				}else{
					swal("<?php echo L('赠金驳回'); ?>", data.info, "warning");
				}
			}, 'json');
		});
	});
	
	$(document).on("click",".visitcredit",function(){
  //$(".visitcredit").click(function() {
		//prompt层
		  var id = $(this).attr('val');
		  swal({
			title: "<?php echo L('赠金审核'); ?>",
			text: "<?php echo L('赠金审核过后'); ?>，<?php echo L('将直接入账MT'); ?>，<?php echo L('如果未确认前'); ?>，<?php echo L('请慎重操作'); ?>！",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('确认赠金审核'); ?>",
			closeOnConfirm: false,   
			showLoaderOnConfirm: true,
		}, function () {
			 var url = "?clause=check";
			 $.post(url, {id: id}, function(data) {
				 if (data.status) {
					 swal("<?php echo L('赠金入金成功'); ?>", data.info, "success");
					 setTimeout(function() {
					 document.location.reload()
					 }, 1500);
				 }else{
						swal("<?php echo L('赠金失败'); ?>", data.info, "warning");
				 }
			 }, 'json');
		});
   
	});
})
</script>
        
        
        
        
        

    </body>
</html>
