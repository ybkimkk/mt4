<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchOid = FGetStr('searchOid');
$SearchLogin = FGetStr('searchLogin');
$SearchStatus = FGetStr('searchStatus');if(strlen($SearchStatus) <= 0){$SearchStatus = '8';}
$SearchPaycode = FGetStr('searchPaycode');
$SearchSTime = FGetStr('searchSTime');
$SearchETime = FGetStr('searchETime');if(strlen($SearchSTime) && strlen($SearchETime) && $SearchETime < $SearchSTime){$temp_ = $SearchETime;$SearchETime = $SearchSTime;$SearchSTime = $temp_;}
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('入金记录') , getCurrMt4ServerName();?></h4>
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
                                                    <label class="control-label"><?php echo L('订单号');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchOid;?>" name="searchOid" placeholder="<?php echo L('请输入订单号');?>">
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('MT账号');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchLogin;?>" name="searchLogin" placeholder="<?php echo L('请输入入金MT的账户');?>">
                                                </div>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('入金状态');?>：</label>
                                                    <select name='searchStatus' id="searchStatus" class='form-control'>
                                                        <option value='all'<?php if($SearchStatus === 'all'){echo ' selected';}?>><?php echo L('全部'); ?></option>
                                                        <option value='0'<?php if($SearchStatus === '0'){echo ' selected';}?>><?php echo L('未支付'); ?></option>
                                                        <option value='1'<?php if($SearchStatus === '1'){echo ' selected';}?>><?php echo L('已驳回'); ?></option>
                                                        <option value='8'<?php if($SearchStatus === '8'){echo ' selected';}?>><?php echo L('到账未入金'); ?></option>
                                                        <option value='9'<?php if($SearchStatus === '9'){echo ' selected';}?>><?php echo L('已入金'); ?></option>
                                                    </select>
                                                </div>
                                                <?php
												if($DRAdmin['_dataRange'] >= 2){
												?>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('支付方式');?>：</label>
                                                    <select name='searchPaycode' id="searchPaycode" class='form-control'>
                                                        <option value=''><?php echo L('全部'); ?> </option>
                                                        <?php
                                                        $types = $DB->getDTable("select * from t_pay where server_id = '" . $DRAdmin['server_id'] . "'");
														foreach($types as $key=>$val){
															echo '<option value="' , $val['PayCode'] , '"' , $SearchPaycode == $val['PayCode'] ? ' selected' : '' , '>' , $val['PayName'] , ' - ' , $val['description'] , '</option>';
														}
														?>
                                                    </select>
                                                </div>
                                                <?php
												}
												?>
                                                <div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('申请时间');?>：</label>
                                                    <div class="input-daterange input-group">
                                                        <input type="text" class="form-control layer-date" name="searchSTime" value="<?php echo $SearchSTime;?>" placeholder="<?php echo L('开始日期');?>">
                                                        <div class="input-group-prepend">
                                                          <div class="input-group-text"><?php echo L('到');?></div>
                                                        </div>
                                                        <input type="text" class="form-control layer-date" name="searchETime" value="<?php echo $SearchETime;?>" placeholder="<?php echo L('结束日期');?>">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                	<div>
                                                        <button type="submit" class="btn btn-primary"><?php echo L('搜索');?></button>
                                                        <?php
														//echo '<a href="#nolink" class="btn btn-primary" id="downBtns">' , L('下载入金记录') , '</a> ';
														if(chk_in_access('管理员入金')){
															echo '<a href="#nolink" class="btn btn-primary" data-toggle="modal" data-target="#managerDeposit">' , L('管理员入金') , '</a>';
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
	//出金权限
	$where = "where a.server_id = '{$DRAdmin['server_id']}'";
	
	if ($SearchStatus == 8) {
		$where .= " and a.paystatus = 1";
		$where .= " and a.status = 0";
	} else if ($SearchStatus != 'all' && $SearchStatus != '') {
		$where .= " and a.status = '{$SearchStatus}'";
	}
	if ($SearchPaycode) {
		$where .= " and a.type = '{$SearchPaycode}'";
	}
	if ($SearchLogin) {
		$where .= " and mtid = '{$SearchLogin}'";
	}
	if ($SearchSTime) {
		$where .= " and a.create_time >= '" . strtotime($SearchSTime . ' 00:00:00') . "'";
	}
	if ($SearchETime) {
		$where .= " and a.create_time <= '" . strtotime($SearchETime . ' 23:59:59') . "'";
	}
	if ($SearchOid){
		$where .= " and a.payno = '" . $SearchOid . "'";
	}

	$user = $DRAdmin;	
	$agroups = $DB->getDTable("select * from `t_groups` where server_id = '{$DRAdmin['server_id']}' and `type` = 'A'");
	$groups_a_array = array();
	foreach ($agroups as $akey => $aval) {
		$groups_a_array[] = $aval['group'];
	}
	$bgroups = $DB->getDTable("select * from `t_groups` where server_id = '{$DRAdmin['server_id']}' and `type` = 'B'");
	$groups_b_array = array();
	foreach ($bgroups as $akey => $aval) {
		$groups_b_array[] = $aval['group'];
	}
	$groups_total_arr = array();
	if(!$groups_b_array){
		$groups_b_array = array('0');
	}
	if(!$groups_a_array){
		$groups_a_array = array('0');
	}
	if ($DRAdmin['_dataRange'] <= 1) {
		if ($user['abook'] == 'deny') {
			if ($user['bbook'] != 'deny') {
				$where .= " and mt4.GROUP in (" . implode(',',$groups_b_array) . ")";
			} else {
				$where .= " and mt4.GROUP = 0";
			}
		} else {
			if ($user['bbook'] == 'deny') {
				$where .= " and mt4.GROUP in (" . implode(',',$groups_a_array) . ")";
			}
		}
	}
	
	{
		$uid = $DRAdmin['id'];
		if ($DRAdmin['_dataRange'] >= 2) {
			$uid = "admin";
		}
		$member_id_arr = getunderCustomerIds($uid);
		if(!$member_id_arr){
			$member_id_arr = array('0');
		}
		$member_id_arr[] = $DRAdmin['id'];

		$where .= " and a.member_id in(" . implode(',', $member_id_arr) . ")";
	}


	
	if ($DRAdmin['ver'] == 5) {
		$recordCount = intval($DB->getField("select count(*) from (select a.* from `t_inmoney` a inner join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_users mt4 on a.mtid = mt4.Login {$where}) c"));
		
		$successDatas = $DB->getField2Arr("select 'vtotal',sum(a.number) as TDOLLOR,COUNT(a.id) as TNUM,round(sum(a.price),2) as TPRICE from `t_inmoney` a inner join t_member b on a.member_id = b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_users mt4 on a.mtid = mt4.Login",true);
	}else{
		$recordCount = intval($DB->getField("select count(*) from (select a.* from `t_inmoney` a inner join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_users mt4 on a.mtid = mt4.LOGIN {$where}) c"));
		
		$successDatas = $DB->getField2Arr("select 'vtotal',sum(a.number) as TDOLLOR,COUNT(a.id) as TNUM,round(sum(a.price),2) as TPRICE from `t_inmoney` a inner join t_member b on a.member_id = b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_users mt4 on a.mtid = mt4.LOGIN",true);
	}
?>



                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                    

<?php
	echo '<div>';
	echo L('入金汇总') , '： &nbsp; &nbsp;' , L('笔数') , '：' , $recordCount, ' ' , L('笔'), '  &nbsp; &nbsp;';
	echo L('金额') , '： $&nbsp;' , floatval($successDatas['vtotal']['TDOLLOR']) , '&nbsp; &nbsp;';
	echo '/&nbsp;&nbsp; ' , floatval($successDatas['vtotal']['TPRICE']);
	echo '</div>';
?>
                                    
                                    
                                    
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('订单号');?></th>
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('上级信息');?></th>
                                                    <th class="no-sort"><?php echo L('联系方式');?></th>
                                                    <th class="no-sort"><?php echo L('金额');?></th>
                                                    <th class="no-sort"><?php echo L('手续费');?></th>
                                                    <th class="no-sort"><?php echo L('支付方式');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('时间');?></th>
                                                    <th class="no-sort"><?php echo L('描述');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php	
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
	if ($DRAdmin['ver'] == 5) {
		$query = $DB->query("select a.*,b.nickname,b.phone,b.email,mt4.Group,mt4.Name,b.parent_id from `t_inmoney` a inner join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt5_users mt4 on a.mtid = mt4.Login {$where} order by a.create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	}else{
		$query = $DB->query("select a.*,b.nickname,b.phone,b.email,mt4.GROUP,mt4.NAME,b.parent_id from `t_inmoney` a inner join t_member b on a.member_id=b.id left join " . $DRAdmin['mt4dbname'] . ".mt4_users mt4 on a.mtid = mt4.LOGIN {$where} order by a.create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	}
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		//是否部署风控
		$riskList = getMonitorRisk();
		
		$groups = $DB->getDTable("select * from t_groups where `server_id` = '{$DRAdmin['server_id']}'");
		
		while($rs = $DB->fetchArray($query)){
			if ($riskList) {
				if (in_array($rs['mtid'], $riskList)) {
					$rs['risk_user'] = 1;
				}
			}
			$rs['parent_name'] = $DB->getField("select nickname from t_member where id = '{$rs['parent_id']}'");
			$rs['Audit_name'] = $DB->getField("select nickname from t_member where id = '{$rs['adminid']}'");
			
			foreach ($groups as $gkey => $gval) {
				if ($gval['group'] == $rs['GROUP']) {
					$rs['group_type'] = $gval['type'];
				}
			}

			echo '<tr' , $rs['risk_user'] == 1 ? 'style="color:red" title="异常用户入金"' : '' , '>';
			echo '<td>' , $rs['risk_user'] == 1 ? '<font color="red">☆ </font>' : '' , $rs['payno'] , '</td>';
			echo '<td>';
			echo L('MT账号') , '：' , $rs['mtid'] , '<br/>';
			echo L('MT分组') , '：' , $rs['GROUP'];
			echo '</td>';
			if(!can_look_parent_info()){
				echo '<td>-</td>';
			}else{
				echo '<td>' , $rs['parent_name'] , '</td>';
			}
			echo '<td>' , L('英文名') , '：' , $rs['nickname'] , '<br/>' , L('手机号') , '：' , hideStr($rs['phone'],3,4) , '</td>';
			echo '<td>' , '$' , $rs['number'] , '<br/>' , $rs['f_currencyPa'] , abs(round($rs['price']-$rs['fee']*$rs['exchange'],2)) , '</td>';
			echo '<td>' , '$' , $rs['fee'] , '<br/>' , $rs['f_currencyPa'] , round($rs['fee']*$rs['exchange'],2) , '</td>';
			echo '<td>' , getpaytypedesc($rs['type'],$types) , '</td>';
			echo '<td>';
			if($rs['paystatus'] == '1'){
				echo L(getpaystatus($rs['paystatus']));
			}else{
				echo L(getinstatus($rs['status']));
				if($rs['status'] == '1'){
					echo '<br/>';
					echo L('驳回人') , ':' , $rs['Audit_name'];
				}
			}
			echo '</td>';
			echo '<td>';
			echo L('申请时间') , '：' , date('Y-m-d H:i:s',$rs['create_time']) , '<br/>';
			echo L('处理时间') , '：';
			if($rs['visit_time'] != NULL){
				echo date('Y-m-d H:i:s',$rs['visit_time']);
			}else{
				echo L('未处理');
			}
			echo '</td>';
			echo '<td>';
			if($rs['inid'] == 0){
				if(strlen($rs['content'])){
					echo L($rs['content']);
					echo '<br/>';
				}
			}else{
				echo L('入金记录ID') , ': ' , $rs['inid'] , '<br/>';
				echo L('备注') , '：' , L($rs['content']);
				if($rs['content'] == '手动审核'){
					echo '<br/>';
					echo L('审核人') , '：' , $rs['Audit_name'];
				}
				echo '<br/>';
			}
			if($rs['group_type'] == ''){
				//echo '<font color="red">' , L('组未同步') , '</font><br/>';
			}
			if(strlen(trim($rs['certificate'])) > 0){
				$arr_ = getattach($rs["certificate"]);
				foreach($arr_ as $key=>$val){
					echo '<a href="' . $val . '" class="fancybox"><img src="' . $val . '" style="width:50px;"></a><br>';
				}
			}
			if(strlen(trim($rs['serialno'])) > 0){
				echo $rs['serialno'];
			}
			echo '</td>';
			echo '<td>';
			if(($rs['status'] == 0 || $rs['status'] == 8) && $rs['paystatus'] != '-1'){
				if(chk_in_access('入金审核')){
					if($rs['group_type'] == 'A'){
						if($user['abook'] == 'manage'){
							echo '<button type="button" title="A BOOK 用户，需手动审核" val="' , $rs['id'] , '" class="btn btn-success btn-sm visitinmoney">' , L('入金') , '</button> ';
						}else{
							echo '<a class="btn btn-sm" type="button" rel="A" href="#nolink">' , L('无Abook权限') , '</a> ';
						}
					}else{
						echo '<button type="button" val="' , $rs['id'] , '" class="btn btn-success btn-sm visitinmoney">' , L('入金') , '</button> ';
					}
				}
				
				if(chk_in_access('入金驳回审核')){
					if(($rs['group_type'] == 'A' && $user['abook'] == 'manage') || $rs['group_type'] == 'B' || $rs['group_type'] == ''){
						echo '<button type="button" val="' , $rs['id'] , '" class="btn btn-primary btn-sm btn-red-cz resetnoney">' , L('驳回') , '</button> ';
					}
				}
			}
			echo '<a href="?clause=showinfo&id=' , $rs['id'] , '" class="btn' , $rs['status'] == 1 ? ' btn-warning btn-red-cz' : ' btn-primary btn-black-cz' , ' btn-sm">' , L('查看') , '</a>';
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







    <!--入金弹出层-->
    <div class="modal inmodal" id="managerDeposit" tabindex="-1" role="dialog" aria-hidden="true" style="">
        <div class="modal-dialog">
            <div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('我要入金'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                <form  id='inmoneyform' name='inmoneyform' action="{:U('Deposit/managerDepoist')}" method="POST">      
                    <div class="modal-body">

                        <div class="form-group"><label><?php echo L('入金金额'); ?>（<?php echo L('美元'); ?>）：</label> <input type="text"  style="ime-mode:disabled" onblur="clearNoNum(this);"  onkeyup="clearNoNum(this);" id="inmoneyid" name='number' placeholder="<?php echo L('请输入金金额'); ?>" class="form-control"></div>
                        <div class="form-group"><label><?php echo L('入金帐号'); ?>：</label> <input type="text" id='mtid' name='mtid'  class="form-control"></div>
                        <div class="form-group" ><label><?php echo L('确认帐号'); ?>：</label> <input type="text" id='mtlogin2' name="mtlogin2" placeholder="" class="form-control"></div>
                        <div class="form-group"><label><?php echo L('备注'); ?>：</label> <input type="text" id="remark" name='remark' value=''   placeholder="" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white"  id='closeout1' data-dismiss="modal"><?php echo L('取消入金'); ?></button>
                        <button type="button" class="btn btn-primary" id='inmoneybtn' ><?php echo L('提交在线入金'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
	<!--入金弹出层-->






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
        
		<script src="/assets/js/fancybox/jquery.fancybox.js"></script>
        <link href="/assets/js/fancybox/jquery.fancybox.css" rel="stylesheet">
        
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
	$(function () {
		$(document).on("click",".inmoney",function(){
		//$(".inmoney").click(function () {
			$("input[name='id']").val($(this).attr('val'));
		});
		
		$(".fancybox").fancybox({openEffect: "none", closeEffect: "none"});
		 
		 $(document).on("click","#downBtns",function(){
		//$("#downBtns").click(function () {
			var value = $("#commentForm").serialize();
			document.location.href = "{:U('Deposit/down_waitout',array('act'=>'down'))}?" + value;
		});
		
		//驳回
		$(document).on("click",".resetnoney",function(){
		//$(".resetnoney").click(function () {
			//prompt层
			var id = $(this).attr('val');		
			swal({
				title: "<?php echo L('入金驳回'); ?>",
				type: "input",
				showCancelButton: true,
				cancelButtonText: "<?php echo L('取消'); ?>",
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "<?php echo L('确认'); ?>",
				closeOnConfirm: false,
				showLoaderOnConfirm: true,
				inputPlaceholder: "<?php echo L('请输入驳回原因'); ?>"
			}, function (pass) {
				if (pass == '') {
					swal.showInputError("<?php echo L('请输入驳回原因'); ?>!");
					return false
				}
				var url = "?clause=resetinmoney";
				$.post(url, {id: id, content: pass}, function (data) {

					if (data.status) {
						swal("<?php echo L('入金驳回'); ?>", data.info, "success");
						setTimeout(function () {
							document.location.reload()
						}, 1500);
					} else {
						swal("<?php echo L('入金驳回'); ?>", data.info, "warning");
					}
				}, 'json');
			});
		});

		$(document).on("click",".visitinmoney",function(){
		//$(".visitinmoney").click(function () {
			//prompt层
			var id = $(this).attr('val');
			swal({
				title: "<?php echo L('入金审核'); ?>",
				text: "<?php echo L('入金审核过后，将直接入账MT，如果未确认前，请慎重操作'); ?>！",
				showCancelButton: true,
				cancelButtonText: "<?php echo L('取消'); ?>",
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "<?php echo L('确认入金'); ?>",
				closeOnConfirm: false,
				showLoaderOnConfirm: true,
			}, function () {
				var url = "?clause=visitinmoney";
				$.post(url, {id: id}, function (data) {
					if (data.status) {
						swal("<?php echo L('入金成功'); ?>", data.info, "success");
						setTimeout(function () {
							document.location.reload()
						}, 1500);
					} else {
						swal("<?php echo L('入金失败'); ?>", data.info, "warning");
					}
				}, 'json');
			});

		});
	})

	$(document).on("click","#inmoneybtn",function(){
	//$("#inmoneybtn").click(function () {
		$(this).attr('disabled', "disabled");
		var _this=$(this);
		var form = $(this).closest('form');
		var url = "?clause=managerDepoist";
		swal({
			title: "<?php echo L('您确定要入金吗'); ?>？",
			showCancelButton: true,
			cancelButtonText: "<?php echo L('取消'); ?>",
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('确认'); ?>",
			closeOnConfirm: false,
			showLoaderOnConfirm: true,
		}, function () {
			$.post(url, form.serialize(), function (data) {
				if (data.status) {
					swal(data.info, data.info, "success");
					setTimeout(function () {
						document.location.reload();
					}, 1500);
				} else {
					_this.removeAttr('disabled');
					  swal(data.info, data.info, "warning");
		  
				}
			}, 'json')
		}
		);
		return false;
	});

	function toDecimal2(x) {
		var f = parseFloat(x);
		if (isNaN(f)) {
			return false;
		}
		var f = Math.round(x * 100) / 100;
		var s = f.toString();
		var rs = s.indexOf('.');
		if (rs < 0) {
			rs = s.length;
			s += '.';
		}
		while (s.length <= rs + 2) {
			s += '0';
		}
		return s;
	}
	
	function clearNoNum(obj) {
		obj.value = obj.value.replace(/[^\d.]/g, "");//清除"数字"和"."以外的字符
		obj.value = obj.value.replace(/^\./g, "");//验证第一个字符是数字而不是.
		obj.value = obj.value.replace(/\.{2,}/g, ".");//只保留第一个. 清除多余的.
		obj.value = obj.value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
	}
</script>

        
        
        

    </body>
</html>
