<?php
$LoadCSSArr = array(
	'/assets/css/vendor/dataTables.bootstrap4.css',
	'/assets/css/vendor/responsive.bootstrap4.css',
	'/assets/css/vendor/buttons.bootstrap4.css',
	'/assets/css/vendor/select.bootstrap4.css',
);
require_once('header.php');
?>

<style>
.radio-inline{ margin-right:15px;}
</style>

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title"><?php echo L('群发任务列表') , getCurrMt4ServerName();?></h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 


                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="nav nav-tabs mb-3"><!-- nav-tabs-cz-->
                                            <?php
                                            	$Type = FGetInt('type');
												
                                                echo '<li class="nav-item"><a href="?" aria-expanded="';
                                                if($Type <= 0){
                                                    echo 'true';
                                                }else{
                                                    echo 'false';
                                                }
                                                echo '" class="nav-link';
                                                if($Type <= 0){
                                                    echo ' active';
                                                }
                                                echo '"> ' , L('邮件') , '</a></li>';//<span class="czico-arrow"></span>
												
												echo '<li class="nav-item"><a href="?type=1" aria-expanded="';
                                                if($Type > 0){
                                                    echo 'true';
                                                }else{
                                                    echo 'false';
                                                }
                                                echo '" class="nav-link';
                                                if($Type > 0){
                                                    echo ' active';
                                                }
                                                echo '"> ' , L('短信') , '</a></li>';//<span class="czico-arrow"></span>
                                            ?>
                                        </ul>
                                    
                                    	<?php
										if($Type == 1){
											?>
											<div class="row mb-2">
												<div class="col-sm-4">
													<a href="?clause=sendGroupMessage" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加群发任务');?></a>
												</div>
											</div>
											<?php
										}else{
											?>
											<div class="row mb-2">
												<div class="col-sm-4">
													<a href="?clause=sendgroupemail" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle mr-2"></i> <?php echo L('添加群发任务');?></a>
												</div>
											</div>
											<?php
										}

if($Type == 1){
	echo '<table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
			<thead>
				<tr>
					<th class="no-sort">' , L('编号') , '</th>
					<th class="no-sort">' , L('发送内容') , '</th>
					<th class="no-sort">' , L('时间') , '</th>
					<th class="no-sort">' , L('处理状态') , '</th>
					<th class="no-sort">' , L('操作') , '</th>
				</tr>
			</thead>
			<tbody>';

	$where = "where server_id = '{$DRAdmin['server_id']}' and status = 0";
	
	$recordCount = intval($DB->getField("select count(*) from `t_message_group` {$where}"));
	
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
	$query = $DB->query("select * from `t_message_group` {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			$counter = $DB->getDRow("select count(*) as count1,sum(if(status<>0,1,0)) as sum1 from t_mail_group_user where groupid = '{$rs['id']}' and `type` = 1");
            $count = intval($counter['count1']);
            $used = intval($counter['sum1']);
            $rs['number'] = $used . '/' . $count;
			
			echo '<tr>';
			echo '<td>' , $rs['id'] , '</td>';
			echo '<td>' , $rs['content'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
			echo '<td>' , $rs['number'] , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=sendGroupMessage&id=' , $rs['id'] , '">' , L('修改') , '</a> ';
			echo '<a class="btn btn-danger btn-red-cz btn-xs deletegroupmail" type="button" href="#nolink" url="?clause=deleteGroupMessage&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
			//echo '<a class="btn btn-info btn-xs" type="button" href="?clause=sendallmessage&id=' , $rs['id'] , '">' , L('发送') , '</a> ';
			//echo '<a class="btn btn-light btn-xs" type="button" href="?clause=groupmaillog&id=' , $rs['id'] , '">' , L('发送记录') , '</a> ';
			echo '</td>';
			echo '</tr>';
		}
	}
	
	echo '</tbody></table>';
}else{
	echo '<table id="basic-datatable" class="table dt-responsive nowrap table-hover" width="100%">
			<thead>
				<tr>
					<th class="no-sort">' , L('编号') , '</th>
					<th class="no-sort">' , L('邮件标题') , '</th>
					<th class="no-sort">' , L('发送邮箱') , '</th>
					<th class="no-sort">' , L('发送昵称') , '</th>
					<th class="no-sort">' , L('时间') , '</th>
					<th class="no-sort">' , L('处理状态') , '</th>
					<th class="no-sort">' , L('操作') , '</th>
				</tr>
			</thead>
			<tbody>';

	$where = "where server_id = '{$DRAdmin['server_id']}' and status = 0";
	
	$recordCount = intval($DB->getField("select count(*) from `t_mail_group` {$where}"));
	
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
	$query = $DB->query("select * from `t_mail_group` {$where} order by id desc LIMIT {$sqlRecordStartIndex},{$pagersize}");
	if($DB->numRows($query) <= 0){
		echo '<tr><td colspan="99" class="dataTables_empty" valign="top">' , L('无可用数据') , '</td></tr>';
	}else{
		while($rs = $DB->fetchArray($query)){
			$counter = $DB->getDRow("select count(*) as count1,sum(if(status<>0,1,0)) as sum1 from t_mail_group_user where groupid = '{$rs['id']}' and `type` = 0");
            $count = intval($counter['count1']);
            $used = intval($counter['sum1']);
            $rs['number'] = $used . '/' . $count;
			
			echo '<tr>';
			echo '<td>' , $rs['id'] , '</td>';
			echo '<td>' , $rs['title'] , '</td>';
			echo '<td>' , $rs['send_email'] , '</td>';
			echo '<td>' , $rs['email_nickname'] , '</td>';
			echo '<td>' , date('Y-m-d H:i:s',$rs['create_time']) , '</td>';
			echo '<td>' , $rs['number'] , '</td>';
			echo '<td>';
			echo '<a class="btn btn-primary btn-xs" type="button" href="?clause=sendgroupemail&id=' , $rs['id'] , '">' , L('修改') , '</a> ';
			echo '<a class="btn btn-danger btn-red-cz btn-xs deletegroupmail" type="button" href="#nolink" url="?clause=deletegroupmail&id=' , $rs['id'] , '">' , L('删除') , '</a> ';
			//echo '<a class="btn btn-info btn-xs" type="button" href="?clause=sendallmail&id=' , $rs['id'] , '">' , L('发送') , '</a> ';
			//echo '<a class="btn btn-light btn-xs" type="button" href="?clause=groupmaillog&id=' , $rs['id'] , '">' , L('发送记录') , '</a> ';
			echo '</td>';
			echo '</tr>';
		}
	}
	
	echo '</tbody></table>';
}
?>


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
        </script>

        <script>
		$(document).on("click",".deletegroupmail",function(){
        //$('.deletegroupmail').click(function () {
			var _this = $(this);
			layer.confirm('<?php echo L('您确定要删除这条信息吗');?>?', {btn: ['<?php echo L('确定');?>', '<?php echo L('取消');?>'],icon: 3, title:'<?php echo L('提示');?>'}, function(index, layero){
				window.location.href = _this.attr('url');
			}, function(index){
				layer.close(index);
			});
		});
        </script>

    </body>
</html>
