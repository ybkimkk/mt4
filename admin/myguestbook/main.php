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
                                    <h4 class="page-title"><?php echo L('留言列表') , getCurrMt4ServerName();?></h4>
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
                                                <a href="?clause=addinfo" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('提交留言');?></a>
                                            </div>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('类型');?></th>
                                                    <th class="no-sort"><?php echo L('标题');?></th>
                                                    <th class="no-sort"><?php echo L('时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
	$where = "where m_id = '{$DRAdmin['id']}'";
	//$where .= " and `status` = 1";
	$recordCount = intval($DB->getField("select count(*) from `t_message_board` {$where}"));
	
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
	$query = $DB->query("select * from `t_message_board` {$where} order by addtime desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			echo '<tr>';
			echo '<td>' , L($GuestbookTypeArr[$rs['type']]) , '</td>';
			echo '<td>';
			echo '<a href="?clause=showinfo&id=' , $rs['id'] , '">';
			echo $rs['f_title'];
			echo '</a>';
			echo '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['addtime']) , '</td>';
			echo '<td>' , $rs['status'] == 1 ? '<font color="green">' . L('已回复') . '<font>' : '<font color="red">' . L('未回复') . '<font>' , '</td>';
			echo '<td>';
			echo '<a class="btn btn-light btn-sm" type="button" href="?clause=showinfo&id=' , $rs['id'] , '">' , L('查看') , '</a> ';
			echo '<a class="btn btn-danger btn-red-cz btn-sm deleteinfo" type="button" href="#nolink" url="?clause=delinfo&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
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
			
			
			
			$(document).on("click",".deleteinfo",function(){
			//$(".deleteinfo").click(function () {
				var _this = $(this);
				layer.confirm('<?php echo L('您确定要删除吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
					_this.attr('disabled', 'disabled');
					$.post(_this.attr('url'), function (data) {
						if (data.status == 1) {
							layer.msg('<?php echo L('删除成功');?>');
							setTimeout(function () {
								document.location.reload();
							}, 700);
						} else if (data.status == 0) {
							layer.msg('<?php echo L('删除失败');?>');
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
