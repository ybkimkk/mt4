<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">
                        
                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('出金记录') , getCurrMt4ServerName();?></h4>
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
                                                <a href="?clause=mtlist" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('出金申请');?></a>
                                            </div>
                                        </div>

                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('MT帐号');?></th>
                                                    <th class="no-sort"><?php echo L('所属人姓名');?></th>
                                                    <th class="no-sort"><?php echo L('邮箱');?></th>
                                                    <th class="no-sort"><?php echo L('金额');?> (USD)</th>
                                                    <th class="no-sort"><?php echo L('转账方式');?></th>
                                                    <th class="no-sort"><?php echo L('申请时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	//if ($_REQUEST['loginid'] != NULL) {
	//    $map['mtid'] = $_REQUEST['loginid'];
	//}
	
	//出金权限
	$where = "where a.member_id = '{$DRAdmin['id']}' and a.server_id = '{$DRAdmin['server_id']}'";
	$recordCount = intval($DB->getField("select count(*) from `t_outmoney` a {$where}"));
	
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
	$query = $DB->query("select a.*,b.nickname,b.phone,b.email from `t_outmoney` a inner join t_member b on a.member_id = b.id {$where} order by a.create_time desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , $rs['mtid'] , '</td>';
			echo '<td>' , $rs['nickname'] , '</td>';
			echo '<td>' , $rs['email'] , '</td>';
			echo '<td>' , $rs['number'] , '</td>';
			echo '<td>' , getouttype($rs['type'],$rs['forwordmtlogin']) , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
			echo '<td>';
			echo getoutstatus($rs['status']);
			if($rs['status'] == '0'){
				echo '<a href="#nolink" rel="' , $rs['id'] , '" class="btn btn-primary btn-xs ml-sm-1" onclick="delete_(this)">' , L('取消申请') , '</a>';
			}
			if(in_array($rs['status'],array(8,9,1,-1))){
				echo ' ' , L($rs['content']);
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
        
        <script src="/assets/js/ajaxupload.3.5.js"></script>
        <script src="/assets/js/layer/layer.js"></script>
		<script src="/assets/js/fancybox/jquery.fancybox.js"></script>
        <link href="/assets/js/fancybox/jquery.fancybox.css" rel="stylesheet">
        
        <link href="/assets/js/sweetalert/sweetalert.css" rel="stylesheet"/>
        <script src="/assets/js/sweetalert/sweetalert.min.js"></script>

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
			
			
				$(".fancybox").fancybox({openEffect: "none", closeEffect: "none"});

			});
        </script>
        
<script>
   function delete_(this_) {
		var ID =  $(this_).attr('rel');
		swal({
			title: "<?php echo L('您确定要取消这条出金申请吗'); ?>",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "<?php echo L('确定'); ?>",
			closeOnConfirm: false,   
			showLoaderOnConfirm: true,
		}, function () {
			var url = "?clause=canceloutmoney";
			$.post(url, "id="+ID, function (data) {
				if (data.status) {
					swal("<?php echo L('出金取消'); ?>", data.info, "success");
					setTimeout(function () {
						document.location.reload()
					}, 1500);
				} else {
					swal("<?php echo L('出金取消'); ?>", data.info, "warning");
				}
			}, 'json');
		});
	}
</script>

    </body>
</html>
