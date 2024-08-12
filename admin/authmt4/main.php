<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchNickname = FGetStr('searchNickname');
$SearchStatus = FGetStr('searchStatus');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('MT开户待审核') , getCurrMt4ServerName();?></h4>
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
                                                    <label class="control-label"><?php echo L('关键词');?>：</label>
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchNickname;?>" name="searchNickname" placeholder="<?php echo L('请输入昵称，邮箱，手机关键词'); ?>">
                                                </div>
												<div class="form-group mr-sm-2">
                                                    <label class="control-label"><?php echo L('审核状态');?>：</label>
                                                    <div>
                                                        <select name="searchStatus" class="form-control">
                                                            <option value=''<?php if(strlen($SearchStatus) <= 0){echo ' selected="selected"';}?>><?php echo L('全部'); ?></option>
                                                            <option value='1'<?php if($SearchStatus === '1'){echo ' selected="selected"';}?>><?php echo L('已审核'); ?></option>
                                                            <option value='0'<?php if($SearchStatus === '0'){echo ' selected="selected"';}?>><?php echo L('未审核'); ?></option>
                                                            <option value='-1'<?php if($SearchStatus === '-1'){echo ' selected="selected"';}?>><?php echo L('拒绝'); ?></option>
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
                                                    <th class="no-sort"><?php echo L('申请人');?></th>
                                                    <th class="no-sort"><?php echo L('邮箱');?></th>
                                                    <th class="no-sort"><?php echo L('MT账号');?></th>
                                                    <th class="no-sort"><?php echo L('组');?></th>
                                                    <th class="no-sort"><?php echo L('杠杆');?></th>
                                                    <th class="no-sort"><?php echo L('申请时间');?></th>
                                                    <th class="no-sort"><?php echo L('处理时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
        $user = $DRAdmin;

        $agroups = $DB->getDTable("select * from `t_groups` where `server_id` = '{$DRAdmin['server_id']}' and `type` = 'A'");
        $groups_a_array = array();
        foreach ($agroups as $akey => $aval) {
            $groups_a_array[] = $aval['group'];
        }
		if(!$groups_a_array){
			$groups_a_array = array('0');
		}
		
        $bgroups = $DB->getDTable("select * from `t_groups` where `server_id` = '{$DRAdmin['server_id']}' and `type` = 'B'");
        $groups_b_array = array();
        foreach ($bgroups as $akey => $aval) {
            $groups_b_array[] = $aval['group'];
        }
		if(!$groups_b_array){
			$groups_b_array = array('0');
		}

		$where = "where 1 = 1";
        if (strlen($SearchNickname) > 0) {
			$where .= " and (`name` like '%" . $SearchNickname . "%' or `email` like '%" . $SearchNickname . "%' or `phone` like '%" . $SearchNickname . "%')";
        }
        if (strlen($SearchStatus) > 0) {
			$where .= " and `status` = '{$SearchStatus}'";
        }
		
        $groups_total_arr = array();
        $groups_total_arr = array();
        /*{
            if ($user['abook'] == 'deny') {
                if ($user['bbook'] != 'deny') {
					$where .= " and `group` in (" . implode(',',$groups_b_array) . ")";
                } else {
					$where .= " and `group` = '0'";
                }
            } else {
                if ($user['bbook'] == 'deny') {
					$where .= " and `group` in (" . implode(',',$groups_a_array) . ")";
                }
            }
        }*/
		
		$where .= " and `mt4_server_id` = '" . $DRAdmin['server_id'] . "'";

		/*{
			$parentid = $DRAdmin['id'];
			if ($DRAdmin['_dataRange'] >= 2) {
				$parentid = "admin";
			}
			$member_id_arr = getunderCustomerIds($parentid);
			if(!$member_id_arr){
				$member_id_arr = array('0');
			}
			$member_id_arr[] = $DRAdmin['id'];
	
			$where .= " and `member_id` in (" . implode(',',$member_id_arr) . ")";
		}*/
		
		$groups = $DB->getDTable("select * from `t_groups` where `server_id` = '{$DRAdmin['server_id']}'");

	$recordCount = intval($DB->getField("select count(*) from `t_mt4_apply` {$where}"));
	
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
	$query = $DB->query("select * from `t_mt4_apply` {$where} order by create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
            foreach ($groups as $gkey => $gval) {
                if ($gval['group'] == $rs['group']) {
                    $rs['group_type'] = $gval['type'];
                }
            }
			
			echo '<tr>';
			echo '<td>' , $rs['name'] , '</td>';
			echo '<td>' , $rs['email'] , '</td>';
			echo '<td>' , $rs['login'] , '</td>';
			echo '<td>' , $rs['group'] , '</td>';
			echo '<td>' , $rs['leverage'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
			echo '<td>' , strlen($rs['check_time']) > 0 ? date('Y-m-d H:i:s',$rs['check_time']) : '' , '</td>';
			echo '<td>';
			switch($rs['status']){
				case 1:
					echo '<span class="badge badge-success">' , L('已开户') , '</span>';
					break;
				case 0:
					echo '<span class="badge badge-default">' , L('未审核') , '</span>';
					break;
				default:
					echo '<span class="badge badge-danger">' , L('拒绝') , '</span>';
					break;
			}
			echo '</td>';
			echo '<td>';
			if($rs['status'] == 0){
				if($rs['group_type'] == 'A'){
					if($user['abook'] == 'manage'){
						echo '<a class="btn btn-primary btn-xs check_user" type="button"  href="?clause=showinfo&id=' , $rs['member_id'] , '&rid=' , $rs['id'] , '" >' , L('审核') , '</a> ';
						echo '<a class="btn btn-danger btn-red-cz btn-xs refuse" type="button" href="#nolink" val="' , $rs['id'] , '">' , L('拒绝') , '</a> ';
					}else{
						echo '<span class="btn  btn-xs" type="button" href="#nolink" >' , L('无Abook权限') , '</span>';
					}
				}else if($rs['group_type'] == 'B' || strlen($rs['group_type']) <= 0){
					echo '<a class="btn btn-primary btn-xs check_user" type="button"  href="?clause=showinfo&id=' , $rs['member_id'] , '&rid=' , $rs['id'] , '">' , L('审核') , '</a> ';
					echo '<a class="btn btn-danger btn-red-cz btn-xs refuse" type="button" href="#nolink" val="' , $rs['id'] , '">' , L('拒绝') , '</a> ';
				}
			}else if($rs['status'] == 1){
				echo '<a class="btn btn-primary btn-red-cz btn-xs resend" type="button"  href="#nolink" rel="' , $rs['id'] , '">' , L('重发开户信息') , '</a>';
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
                    
  
	    
	     <!--弹出层-->
	    <div class="modal inmodal" id="refuseModal" tabindex="-1" role="dialog" aria-hidden="true">
	        <div class="modal-dialog">
	            <div class="modal-content animated bounceInRight">
                      <div class="modal-header">
                        <h4 class="modal-title"><?php echo L('拒绝'); ?></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        	<span aria-hidden="true">&times;</span>
                        </button>
                      </div>
	                 <form  id='refuseform' name='refuseform'>
		            	<input type="hidden" name="sid" ID="sid" value="" />
		                <div class="modal-body">
		                     <label><?php echo L('拒绝理由'); ?>：</label>
		                	<div class="input-group m-b">
		                        <input type="text" class="form-control" name="reply" id="reply"> 
		                    </div>
		                </div>
		                <div class="modal-footer">
		                    <button type="button" class="btn btn-white" id='closerefuse' data-dismiss="modal"><?php echo L('关闭'); ?></button>
		                    <button type="button" class="btn btn-primary" id='refusemt4' ><?php echo L('确认'); ?></button>
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
	 $(document).on("click","#refusemt4",function(){
     //$("#refusemt4").click(function() {
        $(this).attr('disabled', "disabled");
      	var _this=$(this);
        var form = $(this).closest('form');
        var url = "?clause=refusemt4";

        $.post(url, form.serialize(), function(data) {
            layer.msg(data.info);
            if (data.status) {
                document.refuseform.reset();
                $("#closerefuse").click();
                setTimeout(function () {
					document.location.reload();
				}, 700);
            }
            _this.removeAttr("disabled");
        }, 'json')
    });
    
     
	 $(document).on("click",".refuse",function(){
	//$(".refuse").click(function() {
        $(this).attr('disabled', "disabled");
      	var _this=$(this);
      	document.refuseform.reset();
        var form = $(this).closest('form');
		var ID =  $(this).attr('val');
		$("#sid").val(ID);
		$('#refuseModal').modal('toggle');
    });
    
    $(document).on("click",".resend",function(){
	//$('.resend').click(function() {
		var ID = $(this).attr('rel');
		
		var _this = $(this);
		layer.confirm('<?php echo L('您确定要重发开户信息吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
			_this.attr('disabled', 'disabled');
			$.post('?clause=resendMt4mail&id=' + ID, function (data) {
				if (data.status == 1) {
					layer.alert(data.info);
				} else if (data.status == 0) {
					layer.alert(data.info);
				}

				_this.attr('disabled', false);
				layer.close(index);
			}, 'json');
		}, function(index){
			layer.close(index);
		});
	});
</script>
        
        
        
        
        

    </body>
</html>
