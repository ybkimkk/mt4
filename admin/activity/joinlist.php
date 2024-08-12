<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$DRInfo = $DB->getDRow("select * from `t_activity_list` where id = '{$Id}' and status = 1 and server_id = '{$DRAdmin['server_id']}'");

$DRInfo1 = get_lang_otherset_drow('-_activity_-'.$Id,$CurrLangName,$DRAdmin['server_id'],1);
?>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('报名列表') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="?" class="btn btn-light mb-2"><i class="mdi mdi-back mr-2"></i> <?php echo L('返回');?></a>
                                            </div>
                                        </div>
										<div class="row mb-2">
                                            <div class="col-sm-4">
                                                <?php echo $DRInfo1['f_title'];?>
                                            </div>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('ID');?></th>
                                                    <th class="no-sort"><?php echo L('昵称');?></th>
                                                    <th class="no-sort"><?php echo L('手机');?></th>                                                    
                                                    <th class="no-sort"><?php echo L('邮箱');?></th>                                                    
                                                    <th class="no-sort"><?php echo L('真实姓名');?></th>
                                                    <th class="no-sort"><?php echo L('Group');?></th>
                                                    <th class="no-sort"><?php echo L('MT');?></th>
													<th class="no-sort"><?php echo L('客户类型');?></th>
                                                    <th class="no-sort"><?php echo L('创建时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where f_pid = '$Id'";
	//$where .= " and `status` = 1";
	
	
	if(FGetStr('act') == 'toexcel'){
		include($_SERVER['DOCUMENT_ROOT'] . '/vendor/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();

		$objPHPExcel->setActiveSheetIndex(0)->setTitle(L('报名列表'));
		
		$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $DRInfo1['f_title']);
		
		$titleArr = array(
			'ID',
			L('昵称'),
			L('手机'),
			L('邮箱'),
			L('真实姓名'),
			L('Group'),
			L('MT'),
			L('客户类型'),
			L('创建时间'),
			L('状态'),
		);
		$n = 0;
		for ($i = 'A'; $i != 'Y'; $i++) {
			if($n >= count($titleArr)){
				continue;
			}
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($i . '2', $titleArr[$n]);
			$n++;
		}
		
		
		
		
		$j = 3;
		$i = 'A';
		$query = $DB->query("select a.*,b.*,a.id as aid from (select * from t_activity_join {$where} order by id desc) a left join t_member b on a.f_uid = b.id");
		while($rs = $DB->fetchArray($query)){
			$memlogin = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id = '{$rs['f_uid']}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}' order by mt_type asc");
			if($DRAdmin['ver'] == 5){
				$mt5user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt5_users` where `Login` = '" . current($memlogin) . "'");
				if ($mt5user) {
					$rs['login'] = $mt5user['Login'];
					$rs['group'] = $mt5user['Group'];
				}
			}else{
				$mt4user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt4_users` where `LOGIN` = '" . current($memlogin) . "'");
				if ($mt4user) {
					$rs['login'] = $mt4user['LOGIN'];
					$rs['group'] = $mt4user['GROUP'];
				}
			}

			$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['aid'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['nickname'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['phone'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['email'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['realname'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['group'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L($rs['login']),PHPExcel_Cell_DataType::TYPE_STRING);
			
			switch($rs['userType']){
				case 'agent':
					$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L('代理') . '(' . $rs['level'] . L('级') . ')',PHPExcel_Cell_DataType::TYPE_STRING);
					break;
				case 'direct':
					$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L('直接客户'),PHPExcel_Cell_DataType::TYPE_STRING);
					break;
				case 'member':
					$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, L('员工') . '(' . $rs['level'] . L('级') . ')',PHPExcel_Cell_DataType::TYPE_STRING);
					break;
			}

			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['f_addTime'],PHPExcel_Cell_DataType::TYPE_STRING);
			$i++;$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($i . $j, $rs['f_status'],PHPExcel_Cell_DataType::TYPE_STRING);
			
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
		
		echo '<script type="text/javascript">window.parent.layer.closeAll();</script>';
		echo '<script type="text/javascript">window.location.href="' , $saveFilenameAbs , '";</script>';
				
		exit;
	}
	
	
	
	
	
	
	$recordCount = intval($DB->getField("select count(*) from t_activity_join {$where}"));
	
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
	$sql = "select a.*,b.*,a.id as aid from (select * from t_activity_join {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}) a left join t_member b on a.f_uid = b.id";
	$query = $DB->query($sql);
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			$memlogin = $DB->getField2Arr("select id,loginid from t_member_mtlogin where member_id = '{$rs['f_uid']}' and `status` = 1 and `mtserver` = '{$DRAdmin['server_id']}' order by mt_type asc");
			if($DRAdmin['ver'] == 5){
				$mt5user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt5_users` where `Login` = '" . current($memlogin) . "'");
				if ($mt5user) {
					$rs['login'] = $mt5user['Login'];
					$rs['group'] = $mt5user['Group'];
				}
			}else{
				$mt4user = $DB->getDRow("select * from " . $DRAdmin['mt4dbname'] . ".`mt4_users` where `LOGIN` = '" . current($memlogin) . "'");
				if ($mt4user) {
					$rs['login'] = $mt4user['LOGIN'];
					$rs['group'] = $mt4user['GROUP'];
				}
			}
			
			echo '<tr>';
			echo '<td>' , $rs['aid'] , '</td>';
			echo '<td>' , $rs['nickname'] , '</td>';
			echo '<td>';
			if(strlen($rs['phone'])){
				echo '<br/><span class="lookphone">' , hideStr($rs['phone'],3,4) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" phone="' , $rs['phone'] , '"></i>';
			}
			echo '</td>';
			echo '<td>';
			echo '<span class="lookemail">' , hideStr($rs['email'],2,3) , '</span> &nbsp;&nbsp;<i class="fa fa-eye findinfo" email="' , $rs['email'] , '"></i>';
			echo '</td>';
			echo '<td>' , $rs['realname'] , '</td>';
			echo '<td>' , $rs['group'] , '</td>';
			echo '<td>' , $rs['login'] , '</td>';
			echo '<td>';
			switch($rs['userType']){
				case 'agent':
					echo L('代理') , '(' , $rs['level'] , L('级') , ')';
					break;
				case 'direct':
					echo L('直接客户');
					break;
				case 'member':
					echo L('员工') , '(' , $rs['level'] , L('级') , ')';
					break;
			}
			echo '</td>';
			echo '<td>' , $rs['f_addTime'] , '</td>';
			echo '<td>';
			switch($rs['f_status']){
				case '待审核':
					echo '<font color="#FF00AE">' . L($rs['f_status']) . '<font>';
					break;
				case '审核通过':
					echo '<font color="green">' . L($rs['f_status']) . '<font>';

					if($rs['f_cancelStatus'] == '待审核'){

					}else if($rs['f_cancelStatus'] == '审核通过'){

					}else if($rs['f_cancelStatus'] == '已拒绝'){

					}
					break;
				case '已拒绝':
					echo '<font color="#ff0000">' . L($rs['f_status']) . '<font>';
					break;
			}
			echo '</td>';

			echo '<td>';
			//echo '<a class="btn btn-primary btn-xs" type="button" onclick="setstate(this)" rel="' , $rs['aid'] , '" href="#nolink">' , L('状态') , '</a> ';
			
			echo '<a class="btn ' , $rs['f_status'] == '待审核' ? 'btn-primary' : 'btn-light' , ' btn-xs check_user" type="button" href="?clause=setjoinstate&id=' , $rs['aid'] , '&state=1">' , L('审核') , '</a> ';
			echo '<a class="btn ' , $rs['f_status'] == '待审核' ? 'btn-danger btn-red-cz' : 'btn-light' , ' btn-xs refuse" type="button" href="?clause=setjoinstate&id=' , $rs['aid'] , '&state=0">' , L('拒绝') , '</a> ';
			
			echo '<a class="btn ' , $rs['f_status'] == '待审核' ? 'btn-light btn-red-cz' : 'btn-light' , ' btn-xs" type="button" href="?clause=deljoin&id=' , $rs['aid'] , '" onclick="return confirm_del()">' , L('删除') , '</a> ';
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




	<!--客户分配弹出层-->
	<div class="modal inmodal" id="fenpeiModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content animated bounceInRight">
                  <div class="modal-header">
                    <h4 class="modal-title"><?php echo L('报名详情'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
				<form id='allocForm' name='allocForm'>
					<div class="modal-body">
						<label><?php echo L('昵称'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" id="input_nickname">
						</div>
                        <label><?php echo L('邮箱'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" id="input_email">
						</div>
                        <label><?php echo L('真实姓名'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" id="input_realname">
						</div>
                        <label><?php echo L('Group'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" id="input_group">
						</div>
                        <label><?php echo L('MT'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" id="input_login">
						</div>
                        <label><?php echo L('客户类型'); ?>：</label>
						<div class="input-group m-b">
							<input type="text" class="form-control" disabled="disabled" id="input_userTypeStr">
						</div>
                        <label><?php echo L('状态'); ?>：</label>
						<div class="input-group m-b">
							<select class="form-control m-b" id="input_status" name="f_status" onchange="change_input_status()">
								<option value="待审核"><?php echo L('待审核'); ?></option>
								<option value="审核通过"><?php echo L('审核通过'); ?></option>
                                <option value="已拒绝"><?php echo L('已拒绝'); ?></option>
							</select>
						</div>
						<div id="canJoinAgainDiv" style="display: none;">
							<div class="form-group"><label><?php echo L('再次申请'); ?>：</label>
                                <select class="form-control m-b" id="input_canJoinAgain" name="f_canJoinAgain">
                                    <option value="1"><?php echo L('允许'); ?></option>
                                    <option value="0"><?php echo L('不允许'); ?></option>
                                </select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<input type='hidden' name='mem_id' id="mem_id" />
						<input type="hidden" name="parent_id" id="parent_id">
						<button type="button" class="btn btn-white" id='closeout3' data-dismiss="modal"><?php echo L('关闭'); ?></button>
						<button type="button" class="btn btn-primary savejoinstate"  ><?php echo L('确认'); ?></button>
					</div>
			</form>
            </div>
		</div>
	</div>
	<!--弹出层-->






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
			$(document).on("click",".savealloc",function(){
			//$(".savealloc").click(function() {
				var url = "?clause=savejoinstate";
				var _this = $(this);
				var form = $(this).closest('form');
				var parent_id = $("#parent_id").val();
				if(parent_id == '') {
					alert("<?php echo L('请输入归属的上级账户'); ?>");
					return;
				}
				$(this).attr('disabled', "disabled");
				$.post(url, form.serialize(), function(data) {
					layer.msg(data.info);
					if(data.status == 1) {
						document.allocForm.reset();
						$(".close").click();
						setTimeout(function(){document.location.reload();},800);
					}
					_this.removeAttr("disabled");
				}, 'json');
			});
		
			function change_input_status(){
				var input_status = $('#input_status').val();
				if(input_status == '已拒绝'){
					$('#canJoinAgainDiv').show();
				}else{
					$('#canJoinAgainDiv').hide();
				}
			}
		
			function setstate(this_) {
				$(this).attr('disabled', "disabled");
				var _this = $(this_);
				var form = $(this_).closest('form');
				var url = "?clause=getjoinin";
				var ID = $(this_).attr('rel');
				$.post(url, "id=" + ID, function(data) {
					if(data.status == 1) {
						$("#input_nickname").val(data.data.nickname);
						$("#input_email").val(data.data.email);
						$("#input_realname").val(data.data.realname);
						$("#input_group").val(data.data.group);
						$("#input_login").val(data.data.login);
						$("#input_userTypeStr").val(data.data.userTypeStr);
						$("#input_status").val(data.data.f_status);
						$("#input_canJoinAgain").val(data.data.f_canJoinAgain);
						
						if(data.data.f_status == '已拒绝'){
							$('#canJoinAgainDiv').show();
						}else{
							$('#canJoinAgainDiv').hide();
						}
						
						$('#fenpeiModal').modal('toggle');
					} else {
						alert(data.info);
					}
					_this.removeAttr("disabled");
				}, 'json')
			};
		
			function confirm_del(){
			  if(confirm("<?php echo L('您确定要删除吗?'); ?>")){
				 return true;
				 }else{
				 return false;
			  }
		   }
		   
		   init_findinfo();
        </script>

    </body>
</html>
