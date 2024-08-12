<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

$SearchNickname = FGetStr('searchNickname');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('客户变动明细') , getCurrMt4ServerName();?></h4>
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
                                                    <input type="text"  class="form-control" minlength="2" value="<?php echo $SearchNickname;?>" name="searchNickname" placeholder="<?php echo L('用户名、账号关键词');?>">
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
                                                    <th class="no-sort"><?php echo L('客户信息');?></th>
                                                    <th class="no-sort"><?php echo L('原上级信息');?></th>
                                                    <th class="no-sort"><?php echo L('现上级信息');?></th>
                                                    <th class="no-sort"><?php echo L('原客户类型');?></th>
                                                    <th class="no-sort"><?php echo L('现客户类型');?></th>
                                                    <th class="no-sort"><?php echo L('操作者');?></th>
                                                    <th class="no-sort"><?php echo L('操作时间');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where server_id = '{$DRAdmin['server_id']}'";
	if (strlen($SearchNickname) > 0) {
		$where .= " and (`nickname` like '%" . $SearchNickname . "%' or loginid = '" . $SearchNickname . "')";
	}
	
	$recordCount = intval($DB->getField("select count(*) from `t_member_change` {$where}"));
	
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
	$query = $DB->query("select * from `t_member_change` {$where} order by update_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			if($rs['old_parentid'] > 0){
            	$rs['old'] = $DB->getDRow("select id,nickname,email,phone from `t_member` where id = '{$rs['old_parentid']}'");
			}
			if($rs['now_parentid'] > 0){
            	$rs['now'] = $DB->getDRow("select id,nickname,email,phone from `t_member` where id = '{$rs['now_parentid']}'");
			}
			if($rs['adminid'] > 0){
            	$rs['admin'] = $DB->getDRow("select nickname from `t_member` where id = '{$rs['adminid']}'");
			}
			
			echo '<tr>';
			echo '<td>' , $rs['nickname'] , '<br>' , L('MT账号') , ': ' , strlen($rs['loginid']) <= 0 ? L('无') : $rs['loginid'] , '</td>';
			echo '<td>';
			if($rs['old_parentid'] <= 0){
				echo L('无上级');
			}else{
				echo $rs['old']['nickname'];
				if(strlen($rs['old']['email']) > 0){
					echo '<br>';
					echo '<span class="lookemail">';
					echo hideStr($rs['old']['email'],2,3);
					echo '</span> &nbsp;&nbsp;';
					echo '<i class="fa fa-eye findinfo" val="' , $rs['old']['id'] , '" email="' , $rs['old']['email'] , '"></i>';
				}
			}
			echo '</td>';
			echo '<td>';
			if($rs['now_parentid'] <= 0){
				echo L('无上级');
			}else{
				echo $rs['now']['nickname'];
				if(strlen($rs['now']['email']) > 0){
					echo '<br>';
					echo '<span class="lookemail">';
					echo hideStr($rs['now']['email'],2,3);
					echo '</span> &nbsp;&nbsp;';
					echo '<i class="fa fa-eye findinfo" val="' , $rs['now']['id'] , '" email="' , $rs['now']['email'] , '"></i>';
				}
			}
			echo '</td>';
			echo '<td>';
			switch($rs['old_userType']){
				case 'direct':
					echo L('直接客户');
					break;
				case 'agent':
					echo $rs['old_level'];
					echo L('级代理');
					break;
				case 'member':
					echo $rs['old_level'];
					echo L('级员工');
					break;
			}
			echo '</td>';
			echo '<td>';
			switch($rs['now_userType']){
				case 'direct':
					echo L('直接客户');
					break;
				case 'agent':
					echo $rs['now_level'];
					echo L('级代理');
					break;
				case 'member':
					echo $rs['now_level'];
					echo L('级员工');
					break;
			}
			echo '</td>';
			echo '<td>' , $rs['admin']['nickname'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['update_time']) , '</td>';
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

			
			init_findinfo();
        </script>

    </body>
</html>
