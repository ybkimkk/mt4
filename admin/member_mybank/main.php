<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/include/ReportModel.class.php');
?>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('我的银行卡') , getCurrMt4ServerName();?></h4>
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
                                                <a href="?clause=addinfo" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加银行卡');?></a>
                                            </div>
                                        </div>
                                        <table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort"><?php echo L('银行卡号');?></th>
                                                    <th class="no-sort"><?php echo L('开户名');?></th>
                                                    <th class="no-sort"><?php echo L('开户行');?></th>
                                                    <th class="no-sort"><?php echo L('银行国际代码');?></th>
                                                    <th class="no-sort"><?php echo L('卡图片');?></th>
                                                    <th class="no-sort"><?php echo L('申请时间');?></th>
                                                    <th class="no-sort"><?php echo L('状态');?></th>
                                                    <th class="no-sort"><?php echo L('处理时间');?></th>
                                                    <th class="no-sort"><?php echo L('备注');?></th>
                                                    <th class="no-sort"><?php echo L('操作');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php
		$where = "where `server_id` = '{$DRAdmin['server_id']}' and id = '{$DRAdmin['id']}'";
        $bank = $DB->getDTable("select email,bankCard,bankName,bankBranch,swiftCode,accountName,accountNum,create_time,update_time from t_member {$where}");
        $bank_a = array();
        if ($bank && $bank[0]['accountNum']) {
            $bank_a[0]['email'] = $bank[0]['email'];
            $bank_a[0]['bankCard'] = $bank[0]['bankCard'];
            $bank_a[0]['bankName'] = $bank[0]['bankName'];
            $bank_a[0]['accountName'] = $bank[0]['accountName'];
            $bank_a[0]['accountNum'] = $bank[0]['accountNum'];
            $bank_a[0]['swiftCode'] = $bank[0]['swiftCode'];
            $bank_a[0]['status'] = 1;
            $bank_a[0]['creattime'] = $bank[0]['create_time'];
            $bank_a[0]['checktime'] = $bank[0]['update_time'];
            $bank_a[0]['remark'] = 1;  //主卡标识
        }

		$map = "where `server_id` = '{$DRAdmin['server_id']}' and member_id = '{$DRAdmin['id']}' and status <> 3";
        $bank_b = $DB->getDTable("select * from t_bankcode {$map}");
        $list = array_merge($bank_a ? $bank_a : array(), $bank_b ? $bank_b : array());
        $orderFile = array();
        foreach ($list as $vo) {
            $orderFile[] = $vo['creattime'];
        }
        array_multisort($orderFile, SORT_DESC, $list); //按创造时间倒序


		foreach ($list as $key => $rs) {
			echo '<tr class="gradeX" id="tr_' , $rs['id'] , '">';
			echo '<td>' , $rs['accountNum'] , '</td>';
			echo '<td>' , $rs['accountName'] , '</td>';
			echo '<td>' , $rs['bankName'] , '</td>';
			echo '<td>' , $rs['swiftCode'] , '</td>';
			echo '<td>';
			if(strlen($rs['bankCard']) && $rs['bankCard'] > 0){
				$attach = $DB->getDRow("select * from t_attach where id = '{$rs['bankCard']}'");
                $rs['imgpath'] = str_replace(".", "", $attach['savepath']) . $attach['savename'];
				
				echo '<a href="' , $rs['imgpath'] , '" class="fancybox"><img src="' , $rs['imgpath'] , '" style="width: 50px;height: 50px;"></a>';
			}else{
				echo '-';
			}
			echo '</td>';
			echo '<td class="center">' , date('Y-m-d H:i:s',$vo['creattime']) , '</td>';
			echo '<td>';
			if($rs['status'] == '1'){
				echo '<span class="badge badge-success">' , L('已审核') , '</span>';
			}else if($rs['status'] == '0'){
				echo '<span class="badge badge-default">' , L('未审核') , '</span>';
			}else{
				echo '<span class="badge badge-danger">' , L('拒绝') , '</span>';
			}
			echo '</td>';
			echo '<td>';
			if(strlen($rs['checktime'])){
				echo date('Y-m-d H:i:s',$rs['checktime']);
			}
			echo '</td>';
			echo '<td>';
			if($rs['remark'] == 1){
				echo '<span class="badge badge-danger">' , L('主卡') , '</span>';
			}else{
				echo $rs['remark'];
			}
			echo '</td>';
			echo '<td> ';
			if($rs['remark'] != 1){
				echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=addinfo&id=' , $rs['id'] , '">' , L('修改') , '</a> ';
				echo '<a class="btn btn-danger btn-red-cz btn-xs delete" href="#nolink" url="?clause=delinfo&id=' , $rs['id'] , '" type="button">' , L('删除') , '</a>';
			}
			echo '</td>';
			echo '</tr>';
		}
?>
                                            </tbody>
                                        </table>


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
        
		<script src="/assets/js/fancybox/jquery.fancybox.js"></script>
        <link href="/assets/js/fancybox/jquery.fancybox.css" rel="stylesheet">

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
  $(".fancybox").fancybox({openEffect: "none", closeEffect: "none"});
  
$(document).on("click",".delete",function(){
//$('.delete').click(function () {
	var _this = $(this);
	layer.confirm('<?php echo L('您确定要删除这条信息吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
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
